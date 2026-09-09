<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\Communication\CommunicationDispatcherService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(CommunicationDispatcherService $dispatcher): void
    {
        $campaign = $this->campaign;

        if ($campaign->status === 'Cancelled') {
            return;
        }

        $campaign->update([
            'status' => 'Running',
            'started_at' => now(),
        ]);

        $companyId = $campaign->company_id;
        $channel = $campaign->channel;
        $filters = $campaign->audience_filter_json ?? [];

        // Build target audience list from Leads and Customers
        $recipients = [];

        // Fetch Leads
        $leadQuery = Lead::where('company_id', $companyId);
        if (!empty($campaign->project_id)) {
            $leadQuery->where('project_id', $campaign->project_id);
        }
        if (!empty($filters['lead_status'])) {
            $leadQuery->where('status', $filters['lead_status']);
        }
        $leads = $leadQuery->get();

        foreach ($leads as $lead) {
            $contact = ($channel === 'email') ? $lead->email : $lead->mobile;
            if ($contact) {
                $recipients[] = [
                    'recipient' => $contact,
                    'lead_id' => $lead->id,
                    'customer_id' => null,
                    'context' => [
                        'customer_name' => trim("{$lead->first_name} {$lead->last_name}"),
                        'sales_executive' => $lead->assignedUser?->name ?? 'Sales Team',
                    ],
                ];
            }
        }

        // Fetch Customers if target allows
        if (empty($filters['target_type']) || $filters['target_type'] === 'customers') {
            $custQuery = Customer::where('company_id', $companyId);
            if (!empty($filters['customer_status'])) {
                $custQuery->where('status', $filters['customer_status']);
            }
            $customers = $custQuery->get();

            foreach ($customers as $cust) {
                $contact = ($channel === 'email') ? $cust->email : $cust->mobile;
                if ($contact) {
                    $recipients[] = [
                        'recipient' => $contact,
                        'lead_id' => null,
                        'customer_id' => $cust->id,
                        'context' => [
                            'customer_name' => trim("{$cust->first_name} {$cust->last_name}"),
                        ],
                    ];
                }
            }
        }

        $campaign->update(['total_recipients' => count($recipients)]);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $target) {
            try {
                $log = $dispatcher->dispatchMessage([
                    'company_id' => $companyId,
                    'campaign_id' => $campaign->id,
                    'communication_template_id' => $campaign->communication_template_id,
                    'channel' => $channel,
                    'recipient' => $target['recipient'],
                    'context' => $target['context'],
                    'lead_id' => $target['lead_id'],
                    'customer_id' => $target['customer_id'],
                    'is_transactional' => false,
                ]);

                if ($log->status === 'Failed') {
                    $failed++;
                } else {
                    $sent++;
                }
            } catch (Exception $e) {
                $failed++;
            }
        }

        $campaign->update([
            'status' => 'Completed',
            'sent_count' => $sent,
            'delivered_count' => $sent,
            'failed_count' => $failed,
            'completed_at' => now(),
        ]);
    }
}

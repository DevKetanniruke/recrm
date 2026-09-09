<?php

namespace App\Services\Communication;

use App\Models\CommunicationLog;
use App\Models\CommunicationTemplate;
use App\Models\CustomerCommunicationPreference;
use App\Services\Communication\CommunicationChannelManager;
use App\Services\Communication\TemplateHydrationService;
use Exception;
use Illuminate\Support\Facades\DB;

class CommunicationDispatcherService
{
    public function __construct(
        protected CommunicationChannelManager $channelManager,
        protected TemplateHydrationService $hydrationService
    ) {}

    /**
     * Dispatch a message via template or raw text, respecting opt-out rules and creating audit log
     */
    public function dispatchMessage(array $payload): CommunicationLog
    {
        $companyId = $payload['company_id'];
        $channel = strtolower($payload['channel']);
        $recipient = $payload['recipient'];
        $context = $payload['context'] ?? [];
        $customerId = $payload['customer_id'] ?? null;
        $leadId = $payload['lead_id'] ?? null;
        $campaignId = $payload['campaign_id'] ?? null;

        $template = null;
        if (!empty($payload['communication_template_id'])) {
            $template = CommunicationTemplate::find($payload['communication_template_id']);
        }

        $isTransactional = $payload['is_transactional'] ?? ($template?->is_transactional ?? false);
        $subjectTemplate = $payload['subject'] ?? ($template?->subject ?? '');
        $bodyTemplate = $payload['body'] ?? ($template?->body ?? '');

        // 1. Opt-Out Enforcement for Non-Transactional (Marketing) messages
        if (!$isTransactional) {
            $isOptedOut = CustomerCommunicationPreference::where('company_id', $companyId)
                ->where('recipient', $recipient)
                ->whereIn('channel', [$channel, 'all'])
                ->where('opt_in_marketing', false)
                ->exists();

            if ($isOptedOut) {
                return CommunicationLog::create([
                    'company_id' => $companyId,
                    'customer_id' => $customerId,
                    'lead_id' => $leadId,
                    'campaign_id' => $campaignId,
                    'communication_template_id' => $template?->id,
                    'channel' => $channel,
                    'recipient' => $recipient,
                    'subject' => $this->hydrationService->hydrate($subjectTemplate, $context),
                    'message_body' => $this->hydrationService->hydrate($bodyTemplate, $context),
                    'status' => 'OptedOut',
                    'is_transactional' => false,
                    'failure_reason' => 'Customer has opted out of marketing communications.',
                ]);
            }
        }

        // 2. Hydrate content
        $subject = $this->hydrationService->hydrate($subjectTemplate, $context);
        $messageBody = $this->hydrationService->hydrate($bodyTemplate, $context);

        // 3. Create initial log
        $log = CommunicationLog::create([
            'company_id' => $companyId,
            'customer_id' => $customerId,
            'lead_id' => $leadId,
            'campaign_id' => $campaignId,
            'communication_template_id' => $template?->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'message_body' => $messageBody,
            'status' => 'Queued',
            'is_transactional' => $isTransactional,
        ]);

        // 4. Resolve Provider & Send
        try {
            $provider = $this->channelManager->getProvider($channel);
            $result = $provider->send($recipient, $messageBody, $subject, $context);

            if ($result->success) {
                $log->update([
                    'status' => 'Sent',
                    'provider_name' => $result->providerName,
                    'provider_reference' => $result->providerReference,
                    'sent_at' => now(),
                    'delivered_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => 'Failed',
                    'provider_name' => $result->providerName,
                    'failure_reason' => $result->errorMessage ?? 'Unknown provider error',
                ]);
            }
        } catch (Exception $e) {
            $log->update([
                'status' => 'Failed',
                'failure_reason' => $e->getMessage(),
            ]);
        }

        return $log;
    }
}

<?php

namespace App\Services\Automation;

use App\Models\AutomationExecution;
use App\Models\AutomationRule;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\PaymentSchedule;
use App\Models\SiteVisit;
use App\Services\Communication\CommunicationDispatcherService;
use Exception;

class AutomationEngineService
{
    public function __construct(
        protected CommunicationDispatcherService $dispatcher
    ) {}

    /**
     * Trigger automation rules for a specific event type and entity
     */
    public function trigger(string $event, object $entity): int
    {
        $companyId = $entity->company_id ?? null;
        if (!$companyId) {
            return 0;
        }

        $rules = AutomationRule::where('company_id', $companyId)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->with('template')
            ->get();

        $executedCount = 0;
        foreach ($rules as $rule) {
            try {
                $recipient = $this->resolveRecipient($entity);
                if (!$recipient) {
                    continue;
                }

                $context = $this->buildContext($entity);

                // Dispatch message
                $log = $this->dispatcher->dispatchMessage([
                    'company_id' => $companyId,
                    'channel' => $rule->template->channel,
                    'recipient' => $recipient,
                    'communication_template_id' => $rule->communication_template_id,
                    'context' => $context,
                    'customer_id' => $context['customer_id'] ?? null,
                    'lead_id' => $context['lead_id'] ?? null,
                ]);

                // Record execution
                AutomationExecution::create([
                    'company_id' => $companyId,
                    'automation_rule_id' => $rule->id,
                    'entity_type' => get_class($entity),
                    'entity_id' => $entity->id,
                    'status' => $log->status === 'Failed' ? 'Failed' : 'Success',
                    'executed_at' => now(),
                    'error_message' => $log->failure_reason,
                ]);

                $executedCount++;
            } catch (Exception $e) {
                AutomationExecution::create([
                    'company_id' => $companyId,
                    'automation_rule_id' => $rule->id,
                    'entity_type' => get_class($entity),
                    'entity_id' => $entity->id,
                    'status' => 'Failed',
                    'executed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $executedCount;
    }

    /**
     * Extract primary recipient contact for entity
     */
    protected function resolveRecipient(object $entity): ?string
    {
        if ($entity instanceof Lead) {
            return $entity->email ?: $entity->mobile;
        }

        if ($entity instanceof Customer) {
            return $entity->email ?: $entity->mobile;
        }

        if ($entity instanceof Booking) {
            return $entity->customer?->email ?: $entity->customer?->mobile;
        }

        if ($entity instanceof SiteVisit) {
            return $entity->lead?->email ?: $entity->lead?->mobile;
        }

        if ($entity instanceof PaymentSchedule) {
            return $entity->booking?->customer?->email ?: $entity->booking?->customer?->mobile;
        }

        if ($entity instanceof LeadFollowup) {
            return $entity->lead?->email ?: $entity->lead?->mobile;
        }

        return null;
    }

    /**
     * Extract template context variables from entity
     */
    protected function buildContext(object $entity): array
    {
        $context = [];

        if ($entity instanceof Lead) {
            $context['lead'] = $entity;
            $context['lead_id'] = $entity->id;
            $context['customer_name'] = trim("{$entity->first_name} {$entity->last_name}");
        } elseif ($entity instanceof Customer) {
            $context['customer'] = $entity;
            $context['customer_id'] = $entity->id;
            $context['customer_name'] = trim("{$entity->first_name} {$entity->last_name}");
        } elseif ($entity instanceof Booking) {
            $context['booking'] = $entity;
            $context['customer_id'] = $entity->customer_id;
        } elseif ($entity instanceof SiteVisit) {
            $context['lead'] = $entity->lead;
            $context['lead_id'] = $entity->lead_id;
            $context['due_date'] = $entity->visit_date ? $entity->visit_date->format('Y-m-d H:i') : date('Y-m-d H:i');
        } elseif ($entity instanceof PaymentSchedule) {
            $context['booking'] = $entity->booking;
            $context['payment_schedule'] = $entity;
            $context['customer_id'] = $entity->booking?->customer_id;
        } elseif ($entity instanceof LeadFollowup) {
            $context['lead'] = $entity->lead;
            $context['lead_id'] = $entity->lead_id;
            $context['due_date'] = $entity->scheduled_at ? $entity->scheduled_at->format('Y-m-d H:i') : date('Y-m-d H:i');
        }

        return $context;
    }
}

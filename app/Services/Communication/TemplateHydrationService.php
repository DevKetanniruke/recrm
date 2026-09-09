<?php

namespace App\Services\Communication;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PaymentSchedule;

class TemplateHydrationService
{
    /**
     * Hydrate placeholders in text template with actual entity parameters
     */
    public function hydrate(string $content, array $context = []): string
    {
        $placeholders = $this->buildReplacements($context);

        foreach ($placeholders as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * Extract available replacement values from context entities
     */
    protected function buildReplacements(array $context): array
    {
        $replacements = [
            'customer_name' => $context['customer_name'] ?? 'Valued Customer',
            'project_name' => $context['project_name'] ?? 'Real Estate Project',
            'unit_number' => $context['unit_number'] ?? 'N/A',
            'booking_number' => $context['booking_number'] ?? 'N/A',
            'payment_amount' => isset($context['payment_amount']) ? number_format((float)$context['payment_amount'], 2) : '0.00',
            'due_date' => $context['due_date'] ?? date('Y-m-d'),
            'sales_executive' => $context['sales_executive'] ?? 'Sales Team',
            'company_name' => $context['company_name'] ?? 'Apex Realty',
        ];

        // Hydrate from Lead model if present
        if (isset($context['lead']) && $context['lead'] instanceof Lead) {
            $lead = $context['lead'];
            $replacements['customer_name'] = trim("{$lead->first_name} {$lead->last_name}");
            if ($lead->assignedUser) {
                $replacements['sales_executive'] = $lead->assignedUser->name;
            }
        }

        // Hydrate from Customer model if present
        if (isset($context['customer']) && $context['customer'] instanceof Customer) {
            $cust = $context['customer'];
            $replacements['customer_name'] = trim("{$cust->first_name} {$cust->last_name}");
        }

        // Hydrate from Booking model if present
        if (isset($context['booking']) && $context['booking'] instanceof Booking) {
            $booking = $context['booking'];
            $replacements['booking_number'] = $booking->booking_number;
            if ($booking->unit) {
                $replacements['unit_number'] = $booking->unit->unit_number;
                if ($booking->unit->building && $booking->unit->building->project) {
                    $replacements['project_name'] = $booking->unit->building->project->name;
                }
            }
            if ($booking->customer) {
                $replacements['customer_name'] = trim("{$booking->customer->first_name} {$booking->customer->last_name}");
            }
            if ($booking->salesAgent) {
                $replacements['sales_executive'] = $booking->salesAgent->name;
            }
        }

        // Hydrate from PaymentSchedule model
        if (isset($context['payment_schedule']) && $context['payment_schedule'] instanceof PaymentSchedule) {
            $ps = $context['payment_schedule'];
            $replacements['payment_amount'] = number_format((float)$ps->amount_due, 2);
            $replacements['due_date'] = $ps->due_date ? $ps->due_date->format('Y-m-d') : date('Y-m-d');
        }

        // Hydrate from Payment model
        if (isset($context['payment']) && $context['payment'] instanceof Payment) {
            $pay = $context['payment'];
            $replacements['payment_amount'] = number_format((float)$pay->amount_paid, 2);
        }

        return array_merge($replacements, array_diff_key($context, array_flip(['lead', 'customer', 'booking', 'payment_schedule', 'payment'])));
    }
}

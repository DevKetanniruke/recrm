<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PaymentPlanTemplate;
use App\Models\PaymentSchedule;
use Illuminate\Support\Facades\DB;

class PaymentPlanService
{
    /**
     * Create payment schedules for a booking based on a template or milestone array
     */
    public function generateSchedules(Booking $booking, array|PaymentPlanTemplate $plan): array
    {
        return DB::transaction(function () use ($booking, $plan) {
            $companyId = $booking->company_id;
            $agreedPrice = (float) $booking->agreed_price;

            $milestones = [];
            if ($plan instanceof PaymentPlanTemplate) {
                $milestones = $plan->milestones_json ?? [];
            } elseif (is_array($plan)) {
                $milestones = $plan;
            }

            $createdSchedules = [];
            foreach ($milestones as $ms) {
                $milestoneName = $ms['milestone_name'] ?? ($ms['name'] ?? 'Milestone');
                $milestoneCode = $ms['milestone_code'] ?? ($ms['code'] ?? 'OTHER');
                $percentage = (float) ($ms['percentage'] ?? 0.00);
                $triggerDays = (int) ($ms['trigger_days'] ?? 0);
                $dueDate = $ms['due_date'] ?? now()->addDays($triggerDays)->toDateString();

                $amountDue = (float) ($ms['amount_due'] ?? round($agreedPrice * ($percentage / 100), 2));

                $schedule = PaymentSchedule::create([
                    'company_id' => $companyId,
                    'booking_id' => $booking->id,
                    'milestone_name' => $milestoneName,
                    'milestone_code' => $milestoneCode,
                    'description' => $ms['description'] ?? "Milestone {$milestoneName} ({$percentage}%)",
                    'due_date' => $dueDate,
                    'percentage' => $percentage,
                    'amount_due' => $amountDue,
                    'amount_paid' => 0.00,
                    'outstanding_amount' => $amountDue,
                    'status' => 'Pending',
                ]);

                $createdSchedules[] = $schedule;
            }

            return $createdSchedules;
        });
    }
}

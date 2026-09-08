<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record financial payment receipt against booking & milestones.
     */
    public function recordPayment(array $data, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $booking = Booking::where('id', $data['booking_id'])->lockForUpdate()->firstOrFail();

            $receiptNumber = 'REC-' . date('Ymd') . '-' . rand(1000, 9999);
            $amountPaid = (float) $data['amount_paid'];

            $payment = Payment::create([
                'company_id' => $booking->company_id,
                'booking_id' => $booking->id,
                'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
                'receipt_number' => $receiptNumber,
                'amount_paid' => $amountPaid,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => 'Verified',
                'received_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update linked payment schedule if applicable
            if (! empty($data['payment_schedule_id'])) {
                $schedule = PaymentSchedule::where('id', $data['payment_schedule_id'])->lockForUpdate()->first();
                if ($schedule) {
                    $newSchedulePaid = (float) $schedule->amount_paid + $amountPaid;
                    $scheduleStatus = $newSchedulePaid >= (float) $schedule->amount_due ? 'Paid' : 'Partially Paid';
                    $schedule->update([
                        'amount_paid' => $newSchedulePaid,
                        'status' => $scheduleStatus,
                    ]);
                }
            } else {
                // Auto-allocate payment to earliest pending schedule
                $unpaidSchedules = PaymentSchedule::where('booking_id', $booking->id)
                    ->whereIn('status', ['Pending', 'Partially Paid', 'Overdue'])
                    ->orderBy('due_date', 'asc')
                    ->get();

                $remainingPayment = $amountPaid;
                foreach ($unpaidSchedules as $sch) {
                    if ($remainingPayment <= 0) {
                        break;
                    }

                    $dueLeft = (float) $sch->amount_due - (float) $sch->amount_paid;
                    if ($remainingPayment >= $dueLeft) {
                        $sch->update([
                            'amount_paid' => $sch->amount_due,
                            'status' => 'Paid',
                        ]);
                        $remainingPayment -= $dueLeft;
                    } else {
                        $sch->update([
                            'amount_paid' => (float) $sch->amount_paid + $remainingPayment,
                            'status' => 'Partially Paid',
                        ]);
                        $remainingPayment = 0;
                    }
                }
            }

            // Update Booking Total Paid Amount
            $booking->increment('booking_amount_paid', $amountPaid);

            // Audit log
            AuditLog::create([
                'company_id' => $booking->company_id,
                'user_id' => $userId,
                'event' => 'created',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'new_values' => $payment->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }
}

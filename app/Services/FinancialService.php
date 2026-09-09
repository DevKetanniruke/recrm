<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentDemandNotice;
use App\Models\PaymentRefund;
use App\Models\PaymentSchedule;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Record a payment receipt transactionally with automatic FIFO or explicit schedule allocations
     */
    public function recordPayment(array $data, User|int|string $user): Payment
    {
        if (!$user instanceof User) {
            $user = User::findOrFail($user);
        }

        return DB::transaction(function () use ($data, $user) {
            $companyId = $user->company_id;
            $booking = Booking::where('company_id', $companyId)->where('id', $data['booking_id'])->lockForUpdate()->firstOrFail();

            $amountPaid = (float) $data['amount_paid'];
            $paymentMode = $data['payment_mode'] ?? ($data['payment_method'] ?? 'Bank Transfer');

            $payment = Payment::create([
                'company_id' => $companyId,
                'booking_id' => $booking->id,
                'customer_id' => $data['customer_id'] ?? $booking->customer_id,
                'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
                'amount_paid' => $amountPaid,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $paymentMode,
                'payment_mode' => $paymentMode,
                'transaction_reference' => $data['transaction_reference'] ?? ($data['reference_number'] ?? null),
                'bank_cheque_number' => $data['bank_cheque_number'] ?? null,
                'status' => 'Verified',
                'is_reversed' => false,
                'received_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            // Allocation Engine
            $unallocatedAmount = $amountPaid;

            // Explicit allocation array if provided
            if (!empty($data['allocations']) && is_array($data['allocations'])) {
                foreach ($data['allocations'] as $alloc) {
                    if ($unallocatedAmount <= 0) break;
                    $schedId = $alloc['payment_schedule_id'];
                    $allocAmt = min($unallocatedAmount, (float) $alloc['amount']);

                    $schedule = PaymentSchedule::where('company_id', $companyId)->where('id', $schedId)->lockForUpdate()->first();
                    if ($schedule && $allocAmt > 0) {
                        PaymentAllocation::create([
                            'company_id' => $companyId,
                            'payment_id' => $payment->id,
                            'payment_schedule_id' => $schedule->id,
                            'allocated_amount' => $allocAmt,
                            'allocated_at' => now(),
                        ]);

                        $schedule->amount_paid += $allocAmt;
                        $schedule->recalculateOutstanding();
                        $unallocatedAmount -= $allocAmt;
                    }
                }
            } elseif (!empty($data['payment_schedule_id'])) {
                // Direct allocation to specified schedule
                $schedule = PaymentSchedule::where('company_id', $companyId)->where('id', $data['payment_schedule_id'])->lockForUpdate()->first();
                if ($schedule) {
                    PaymentAllocation::create([
                        'company_id' => $companyId,
                        'payment_id' => $payment->id,
                        'payment_schedule_id' => $schedule->id,
                        'allocated_amount' => $amountPaid,
                        'allocated_at' => now(),
                    ]);

                    $schedule->amount_paid += $amountPaid;
                    $schedule->recalculateOutstanding();
                }
            } else {
                // Automatic FIFO Allocation against oldest pending or partially paid schedules
                $pendingSchedules = PaymentSchedule::where('company_id', $companyId)
                    ->where('booking_id', $booking->id)
                    ->whereIn('status', ['Pending', 'Partially Paid', 'Overdue'])
                    ->orderBy('due_date', 'asc')
                    ->get();

                foreach ($pendingSchedules as $schedule) {
                    if ($unallocatedAmount <= 0) break;

                    $scheduleRemaining = max(0, $schedule->amount_due - $schedule->amount_paid);
                    if ($scheduleRemaining <= 0) continue;

                    $allocateAmt = min($unallocatedAmount, $scheduleRemaining);

                    PaymentAllocation::create([
                        'company_id' => $companyId,
                        'payment_id' => $payment->id,
                        'payment_schedule_id' => $schedule->id,
                        'allocated_amount' => $allocateAmt,
                        'allocated_at' => now(),
                    ]);

                    $schedule->amount_paid += $allocateAmt;
                    $schedule->recalculateOutstanding();
                    $unallocatedAmount -= $allocateAmt;
                }
            }

            // Update Booking total paid
            $booking->increment('booking_amount_paid', $amountPaid);

            // Audit log
            AuditLog::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'event' => 'created',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'old_values' => json_encode([]),
                'new_values' => json_encode([
                    'payment_number' => $payment->payment_number,
                    'receipt_number' => $payment->receipt_number,
                    'amount_paid' => $amountPaid,
                    'payment_mode' => $paymentMode,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $payment;
        });
    }

    /**
     * Audit-compliant Payment Reversal or Refund (Zero Deletion)
     */
    public function reverseOrRefundPayment(Payment $payment, User|int|string $user, string $reason, ?float $refundAmount = null): PaymentRefund
    {
        if (!$user instanceof User) {
            $user = User::findOrFail($user);
        }

        return DB::transaction(function () use ($payment, $user, $reason, $refundAmount) {
            if ($payment->is_reversed) {
                throw new Exception("Payment #{$payment->payment_number} has already been reversed.");
            }

            $companyId = $user->company_id;
            $refundAmt = $refundAmount ?? $payment->amount_paid;

            // 1. Mark payment as reversed
            $payment->update([
                'is_reversed' => true,
                'status' => 'Refunded',
                'reversal_reason' => $reason,
                'reversed_at' => now(),
                'reversed_by_user_id' => $user->id,
            ]);

            // 2. Revert allocations on payment schedules
            foreach ($payment->allocations as $alloc) {
                $schedule = $alloc->paymentSchedule;
                if ($schedule) {
                    $schedule->amount_paid = max(0, $schedule->amount_paid - $alloc->allocated_amount);
                    $schedule->recalculateOutstanding();
                }
            }

            // 3. Create explicit PaymentRefund record
            $refund = PaymentRefund::create([
                'company_id' => $companyId,
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'refund_amount' => $refundAmt,
                'refund_date' => now()->toDateString(),
                'reason' => $reason,
                'processed_by_user_id' => $user->id,
                'status' => 'Processed',
            ]);

            // 4. Update Booking total paid
            $booking = $payment->booking;
            if ($booking) {
                $booking->decrement('booking_amount_paid', min($booking->booking_amount_paid, $payment->amount_paid));
            }

            // 5. Audit log
            AuditLog::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'event' => 'reversed',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'old_values' => json_encode(['is_reversed' => false]),
                'new_values' => json_encode(['is_reversed' => true, 'refund_number' => $refund->refund_number, 'reason' => $reason]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $refund;
        });
    }

    /**
     * Calculate comprehensive financial summary for a booking
     */
    public function calculateSummary(Booking $booking): array
    {
        $agreedPrice = (float) $booking->agreed_price;
        $totalPackageValue = (float) $booking->total_amount;
        $totalReceived = (float) Payment::where('booking_id', $booking->id)->where('is_reversed', false)->sum('amount_paid');
        $totalBilled = (float) PaymentSchedule::where('booking_id', $booking->id)->sum('amount_due');

        $totalOutstanding = max(0, $totalPackageValue - $totalReceived);

        $overdueSchedules = PaymentSchedule::where('booking_id', $booking->id)
            ->whereIn('status', ['Overdue', 'Partially Paid'])
            ->where('due_date', '<', now())
            ->get();

        $overdueAmount = 0.00;
        foreach ($overdueSchedules as $sch) {
            $overdueAmount += max(0, $sch->amount_due - $sch->amount_paid);
        }

        $upcomingSchedules = PaymentSchedule::where('booking_id', $booking->id)
            ->whereIn('status', ['Pending', 'Partially Paid'])
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->get();

        return [
            'total_agreement_value' => $agreedPrice,
            'total_package_value' => $totalPackageValue,
            'total_billed_amount' => $totalBilled,
            'total_received_amount' => $totalReceived,
            'total_outstanding_amount' => $totalOutstanding,
            'overdue_amount' => $overdueAmount,
            'upcoming_schedules' => $upcomingSchedules,
        ];
    }

    /**
     * Automated overdue calculation worker scanning past due dates
     */
    public function checkAndMarkOverdueSchedules(): int
    {
        $overdueSchedules = PaymentSchedule::whereIn('status', ['Pending', 'Partially Paid'])
            ->where('due_date', '<', now())
            ->get();

        $updatedCount = 0;
        foreach ($overdueSchedules as $schedule) {
            $schedule->recalculateOutstanding();
            $updatedCount++;

            // Update associated demand notices
            PaymentDemandNotice::where('payment_schedule_id', $schedule->id)
                ->whereIn('status', ['Sent', 'Partially Paid'])
                ->update(['status' => 'Overdue']);
        }

        return $updatedCount;
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\PaymentSchedule;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new unit booking with customer assignment, status reservation, and payment milestones.
     */
    public function createBooking(array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            $unit = Unit::where('id', $data['unit_id'])->lockForUpdate()->firstOrFail();

            if ($unit->status !== 'Available') {
                throw new Exception("Unit #{$unit->unit_number} is not available for booking (Current status: {$unit->status}).");
            }

            // 1. Find or Create Customer
            $customer = Customer::where('company_id', $unit->company_id)
                ->where('phone', $data['customer_phone'])
                ->first();

            if (! $customer) {
                $customer = Customer::create([
                    'company_id' => $unit->company_id,
                    'lead_id' => $data['lead_id'] ?? null,
                    'first_name' => $data['customer_first_name'],
                    'last_name' => $data['customer_last_name'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                    'phone' => $data['customer_phone'],
                    'pan_number' => $data['customer_pan_number'] ?? null,
                    'address' => $data['customer_address'] ?? null,
                    'kyc_status' => 'Pending',
                ]);
            }

            // 2. Lock Unit status to Booked
            $unit->update(['status' => 'Booked']);

            // 3. Update Lead status to Won if linked
            if (! empty($data['lead_id'])) {
                Lead::where('id', $data['lead_id'])->update(['status' => 'Won']);
            }

            // 4. Generate unique Booking Reference Number
            $bookingNumber = 'BK-' . strtoupper(dechex(time())) . '-' . rand(100, 999);

            $agreedPrice = (float) $data['agreed_price'];
            $discount = (float) ($data['discount_amount'] ?? 0);
            $tax = (float) ($data['tax_amount'] ?? 0);
            $totalAmount = $agreedPrice - $discount + $tax;
            $bookingPaid = (float) ($data['booking_amount_paid'] ?? 0);

            // 5. Create Booking Record
            $booking = Booking::create([
                'company_id' => $unit->company_id,
                'booking_number' => $bookingNumber,
                'unit_id' => $unit->id,
                'customer_id' => $customer->id,
                'sales_agent_id' => $userId,
                'booking_date' => $data['booking_date'] ?? now()->toDateString(),
                'agreed_price' => $agreedPrice,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $totalAmount,
                'booking_amount_paid' => $bookingPaid,
                'status' => 'Confirmed',
                'terms_conditions' => $data['terms_conditions'] ?? null,
            ]);

            // 6. Generate Payment Milestone Schedules
            if (! empty($data['milestones']) && is_array($data['milestones'])) {
                foreach ($data['milestones'] as $milestone) {
                    PaymentSchedule::create([
                        'company_id' => $unit->company_id,
                        'booking_id' => $booking->id,
                        'milestone_name' => $milestone['milestone_name'],
                        'due_date' => $milestone['due_date'],
                        'amount_due' => $milestone['amount_due'],
                        'amount_paid' => 0,
                        'status' => 'Pending',
                    ]);
                }
            } else {
                // Default 4-Stage Payment Milestone Template
                $stage1 = round($totalAmount * 0.10, 2); // 10% Booking Deposit
                $stage2 = round($totalAmount * 0.30, 2); // 30% Foundation
                $stage3 = round($totalAmount * 0.40, 2); // 40% Superstructure Slab
                $stage4 = round($totalAmount - ($stage1 + $stage2 + $stage3), 2); // Balance on Possession

                $bookingDate = now();
                PaymentSchedule::create([
                    'company_id' => $unit->company_id,
                    'booking_id' => $booking->id,
                    'milestone_name' => 'Token & Booking Advance (10%)',
                    'due_date' => $bookingDate->copy()->addDays(7)->toDateString(),
                    'amount_due' => $stage1,
                    'amount_paid' => min($bookingPaid, $stage1),
                    'status' => $bookingPaid >= $stage1 ? 'Paid' : ($bookingPaid > 0 ? 'Partially Paid' : 'Pending'),
                ]);

                PaymentSchedule::create([
                    'company_id' => $unit->company_id,
                    'booking_id' => $booking->id,
                    'milestone_name' => 'Foundation & Basement Completion (30%)',
                    'due_date' => $bookingDate->copy()->addMonths(2)->toDateString(),
                    'amount_due' => $stage2,
                    'amount_paid' => 0,
                    'status' => 'Pending',
                ]);

                PaymentSchedule::create([
                    'company_id' => $unit->company_id,
                    'booking_id' => $booking->id,
                    'milestone_name' => 'Slab & Structure Casting (40%)',
                    'due_date' => $bookingDate->copy()->addMonths(5)->toDateString(),
                    'amount_due' => $stage3,
                    'amount_paid' => 0,
                    'status' => 'Pending',
                ]);

                PaymentSchedule::create([
                    'company_id' => $unit->company_id,
                    'booking_id' => $booking->id,
                    'milestone_name' => 'Handover & Possession Balance (20%)',
                    'due_date' => $bookingDate->copy()->addMonths(9)->toDateString(),
                    'amount_due' => $stage4,
                    'amount_paid' => 0,
                    'status' => 'Pending',
                ]);
            }

            // 7. Audit Log
            AuditLog::create([
                'company_id' => $unit->company_id,
                'user_id' => $userId,
                'event' => 'created',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'new_values' => $booking->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $booking;
        });
    }

    /**
     * Cancel an existing booking and release unit status back to Available.
     */
    public function cancelBooking(Booking $booking, string $reason, float $refundAmount, int $userId): void
    {
        DB::transaction(function () use ($booking, $reason, $refundAmount, $userId) {
            $booking->update([
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
                'cancellation_refund_amount' => $refundAmount,
            ]);

            // Release unit status
            $booking->unit->update(['status' => 'Available']);

            // Audit log
            AuditLog::create([
                'company_id' => $booking->company_id,
                'user_id' => $userId,
                'event' => 'status_changed',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'old_values' => ['status' => 'Confirmed'],
                'new_values' => ['status' => 'Cancelled', 'reason' => $reason],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}

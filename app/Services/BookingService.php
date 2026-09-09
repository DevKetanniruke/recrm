<?php

namespace App\Services;

use App\Exceptions\UnitAlreadyBookedException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\CoApplicant;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Transaction-safe booking creation with pessimistic row locking (lockForUpdate)
     */
    public function createBooking(array $data, User|int|string $creator): Booking
    {
        if (!$creator instanceof User) {
            $creator = User::findOrFail($creator);
        }

        return DB::transaction(function () use ($data, $creator) {
            $companyId = $creator->company_id;
            $unitIds = (array) ($data['unit_ids'] ?? [$data['unit_id']]);

            // 1. Pessimistic row locking on target unit(s)
            $lockedUnits = Unit::where('company_id', $companyId)
                ->whereIn('id', $unitIds)
                ->lockForUpdate()
                ->get();

            if ($lockedUnits->count() !== count($unitIds)) {
                throw new UnitAlreadyBookedException("Specified unit(s) do not exist or belong to another company.");
            }

            foreach ($lockedUnits as $unit) {
                // Unit status must be Available or Hold
                if (!in_array($unit->status, ['Available', 'Hold'])) {
                    throw new UnitAlreadyBookedException("Unit #{$unit->unit_number} is currently status '{$unit->status}' and cannot be booked.");
                }
            }

            // 2. Resolve or Create Customer
            $customer = null;
            if (!empty($data['customer_id'])) {
                $customer = Customer::where('company_id', $companyId)->findOrFail($data['customer_id']);
            } else {
                $firstName = $data['first_name'] ?? $data['customer_first_name'] ?? 'Customer';
                $lastName = $data['last_name'] ?? $data['customer_last_name'] ?? 'Record';
                $mobile = $data['mobile'] ?? $data['customer_phone'] ?? '0000000000';
                $email = $data['email'] ?? ($data['customer_email'] ?? 'customer_' . time() . '@example.com');

                $customer = Customer::create([
                    'company_id' => $companyId,
                    'lead_id' => $data['lead_id'] ?? null,
                    'first_name' => $firstName,
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $lastName,
                    'mobile' => $mobile,
                    'phone' => $mobile,
                    'alternate_mobile' => $data['alternate_mobile'] ?? null,
                    'email' => $email,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'occupation' => $data['occupation'] ?? null,
                    'company_or_employer' => $data['company_or_employer'] ?? null,
                    'nationality' => $data['nationality'] ?? 'Indian',
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'pincode' => $data['pincode'] ?? null,
                    'country' => $data['country'] ?? 'India',
                    'PAN' => $data['PAN'] ?? ($data['pan_number'] ?? null),
                    'reference' => $data['reference'] ?? null,
                    'communication_preference' => $data['communication_preference'] ?? 'Email',
                    'kyc_status' => 'Pending',
                ]);
            }

            // 3. Handle Co-Applicants
            if (!empty($data['co_applicants']) && is_array($data['co_applicants'])) {
                foreach ($data['co_applicants'] as $coData) {
                    if (!empty($coData['customer_name'])) {
                        CoApplicant::create([
                            'company_id' => $companyId,
                            'customer_id' => $customer->id,
                            'customer_name' => $coData['customer_name'],
                            'relationship' => $coData['relationship'] ?? 'Co-Applicant',
                            'mobile' => $coData['mobile'] ?? null,
                            'email' => $coData['email'] ?? null,
                            'ownership_percentage' => $coData['ownership_percentage'] ?? 0.00,
                            'applicant_type' => $coData['applicant_type'] ?? 'Co-Applicant',
                            'pan_number' => $coData['pan_number'] ?? null,
                            'aadhaar_number' => $coData['aadhaar_number'] ?? null,
                        ]);
                    }
                }
            }

            // 4. Calculate final values & create Booking record
            $primaryUnit = $lockedUnits->first();
            $quotedPrice = (float) ($data['quoted_price'] ?? $primaryUnit->total_price ?? $primaryUnit->pricing?->calculated_total_price ?? 0);
            $agreedPrice = (float) ($data['agreed_price'] ?? $quotedPrice);
            $discountAmount = (float) ($data['discount_amount'] ?? max(0, $quotedPrice - $agreedPrice));
            $taxAmount = (float) ($data['tax_amount'] ?? round($agreedPrice * 0.05, 2));
            $totalAmount = (float) ($data['total_amount'] ?? ($agreedPrice + $taxAmount));
            $bookingAmountPaid = (float) ($data['booking_amount_paid'] ?? $data['token_amount'] ?? 0);

            $booking = Booking::create([
                'company_id' => $companyId,
                'unit_id' => $primaryUnit->id,
                'customer_id' => $customer->id,
                'project_id' => $data['project_id'] ?? $primaryUnit->project_id,
                'lead_id' => $data['lead_id'] ?? null,
                'unit_ids' => $unitIds,
                'sales_agent_id' => $data['sales_agent_id'] ?? $creator->id,
                'booking_date' => $data['booking_date'] ?? now()->toDateString(),
                'quoted_price' => $quotedPrice,
                'agreed_price' => $agreedPrice,
                'discount_amount' => $discountAmount,
                'charges' => $data['charges'] ?? null,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'booking_amount_paid' => $bookingAmountPaid,
                'payment_mode' => $data['payment_mode'] ?? 'Cheque',
                'payment_reference' => $data['payment_reference'] ?? null,
                'status' => 'Confirmed',
                'confirmed_at' => now(),
                'terms_conditions' => $data['terms_conditions'] ?? 'Standard Booking Agreement Terms Apply.',
            ]);

            // Auto Generate Default 4 Milestone Payment Schedules
            $milestones = [
                ['name' => 'Token & Booking Advance (10%)', 'percentage' => 0.10, 'due_date' => now()->toDateString()],
                ['name' => 'Foundation & Basement Completion (30%)', 'percentage' => 0.30, 'due_date' => now()->addDays(60)->toDateString()],
                ['name' => 'Superstructure & Plastering Completion (40%)', 'percentage' => 0.40, 'due_date' => now()->addDays(180)->toDateString()],
                ['name' => 'Final Possession & Handover (20%)', 'percentage' => 0.20, 'due_date' => now()->addDays(365)->toDateString()],
            ];

            foreach ($milestones as $milestone) {
                \App\Models\PaymentSchedule::create([
                    'company_id' => $companyId,
                    'booking_id' => $booking->id,
                    'milestone_name' => $milestone['name'],
                    'due_date' => $milestone['due_date'],
                    'amount_due' => round($agreedPrice * $milestone['percentage'], 2),
                    'amount_paid' => 0.00,
                    'status' => 'Pending',
                ]);
            }

            // 5. Flip Unit Status to Booked
            foreach ($lockedUnits as $unit) {
                $oldStatus = $unit->status;
                $unit->update([
                    'status' => 'Booked',
                    'inventory_status' => 'Booked',
                    'unit_lock_expires_at' => null,
                ]);

                UnitStatusHistory::create([
                    'unit_id' => $unit->id,
                    'previous_status' => $oldStatus,
                    'new_status' => 'Booked',
                    'changed_by' => $creator->id,
                    'reason' => "Unit booked under Booking #{$booking->booking_number}",
                ]);
            }

            // 6. Update Lead Stage if lead exists
            if (!empty($data['lead_id'])) {
                Lead::where('company_id', $companyId)->where('id', $data['lead_id'])->update([
                    'stage' => 'Booking Completed',
                    'status' => 'Won',
                ]);
            }

            // 7. Audit Log
            AuditLog::create([
                'company_id' => $companyId,
                'user_id' => $creator->id,
                'event' => 'created',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'old_values' => json_encode([]),
                'new_values' => json_encode(['booking_number' => $booking->booking_number, 'agreed_price' => $agreedPrice, 'status' => 'Confirmed']),
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $booking;
        });
    }

    /**
     * Transaction-safe booking cancellation & inventory release
     */
    public function cancelBooking(Booking $booking, User|int|string $user, string $reason, ?float $refundAmount = 0.00): Booking
    {
        if (!$user instanceof User) {
            $user = User::findOrFail($user);
        }

        return DB::transaction(function () use ($booking, $user, $reason, $refundAmount) {
            $oldStatus = $booking->status;

            $booking->update([
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
                'cancellation_refund_amount' => $refundAmount ?? 0.00,
                'cancelled_at' => now(),
                'cancelled_by_user_id' => $user->id,
            ]);

            // Release locked unit(s)
            $unitIds = (array) ($booking->unit_ids ?? [$booking->unit_id]);
            $units = Unit::whereIn('id', array_filter($unitIds))->lockForUpdate()->get();

            foreach ($units as $unit) {
                $prevUnitStatus = $unit->status;
                $unit->update([
                    'status' => 'Available',
                    'inventory_status' => 'Available',
                    'unit_lock_expires_at' => null,
                ]);

                UnitStatusHistory::create([
                    'unit_id' => $unit->id,
                    'previous_status' => $prevUnitStatus,
                    'new_status' => 'Available',
                    'changed_by' => $user->id,
                    'reason' => "Booking #{$booking->booking_number} cancelled: {$reason}",
                ]);
            }

            AuditLog::create([
                'company_id' => $booking->company_id,
                'user_id' => $user->id,
                'event' => 'cancelled',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'old_values' => json_encode(['status' => $oldStatus]),
                'new_values' => json_encode(['status' => 'Cancelled', 'reason' => $reason]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $booking;
        });
    }
}

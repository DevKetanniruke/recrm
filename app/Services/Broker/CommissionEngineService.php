<?php

namespace App\Services\Broker;

use App\Models\Booking;
use App\Models\ChannelPartner;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\CommissionStructure;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CommissionEngineService
{
    /**
     * Calculate and generate commission record for a confirmed booking
     */
    public function calculateCommissionForBooking(Booking $booking): ?Commission
    {
        return DB::transaction(function () use ($booking) {
            $companyId = $booking->company_id;

            // 1. Resolve channel partner from Booking or Customer's Lead
            $channelPartnerId = $booking->channel_partner_id
                ?? $booking->customer?->lead?->channel_partner_id;

            if (!$channelPartnerId) {
                return null; // Direct sales booking without broker attribution
            }

            $partner = ChannelPartner::where('company_id', $companyId)->find($channelPartnerId);
            if (!$partner || in_array($partner->status, ['Inactive', 'Suspended', 'Blacklisted'])) {
                return null; // Disqualified partner status
            }

            $agreedValue = (float) $booking->agreed_price;
            $unit = $booking->unit;
            $projectId = $unit?->project_id ?? $unit?->building?->project_id;
            $unitTypeId = $unit?->unit_type_id;

            // 2. Find matching CommissionStructure (Specific Project/UnitType first, then General)
            $structure = CommissionStructure::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(function ($q) use ($projectId, $unitTypeId) {
                    $q->where(function ($sub) use ($projectId, $unitTypeId) {
                        $sub->where('project_id', $projectId)->where('unit_type_id', $unitTypeId);
                    })
                    ->orWhere(function ($sub) use ($projectId) {
                        $sub->where('project_id', $projectId)->whereNull('unit_type_id');
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNull('project_id')->whereNull('unit_type_id');
                    });
                })
                ->orderByRaw('project_id IS NOT NULL DESC, unit_type_id IS NOT NULL DESC')
                ->first();

            $commissionRate = 2.00; // Default 2% base commission
            $calculatedAmount = round($agreedValue * 0.02, 2);
            $calculationType = 'percentage';

            if ($structure) {
                $calculationType = $structure->calculation_type;
                if ($calculationType === 'percentage') {
                    $commissionRate = (float) $structure->rate;
                    $calculatedAmount = round($agreedValue * ($commissionRate / 100), 2);
                } elseif ($calculationType === 'fixed_amount') {
                    $calculatedAmount = (float) $structure->fixed_amount;
                    $commissionRate = ($agreedValue > 0) ? round(($calculatedAmount / $agreedValue) * 100, 2) : 0.00;
                } elseif ($calculationType === 'slab_based' && !empty($structure->slabs_json)) {
                    $totalPartnerBookings = Booking::where('company_id', $companyId)
                        ->where(function ($bq) use ($partner) {
                            $bq->where('channel_partner_id', $partner->id)
                              ->orWhereHas('customer.lead', function ($lq) use ($partner) {
                                  $lq->where('channel_partner_id', $partner->id);
                              });
                        })
                        ->where('status', 'Confirmed')
                        ->count();

                    $appliedRate = (float) $structure->rate;
                    foreach ($structure->slabs_json as $slab) {
                        $min = (int) ($slab['min_bookings'] ?? 0);
                        $max = (int) ($slab['max_bookings'] ?? 999999);
                        if ($totalPartnerBookings >= $min && $totalPartnerBookings <= $max) {
                            $appliedRate = (float) ($slab['percentage_rate'] ?? $appliedRate);
                            break;
                        }
                    }
                    $commissionRate = $appliedRate;
                    $calculatedAmount = round($agreedValue * ($commissionRate / 100), 2);
                }
            }

            // 3. Create or update Commission record
            $commission = Commission::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'booking_id' => $booking->id,
                ],
                [
                    'channel_partner_id' => $partner->id,
                    'commission_structure_id' => $structure?->id,
                    'agreement_value' => $agreedValue,
                    'commission_percentage' => $commissionRate,
                    'calculated_commission_amount' => $calculatedAmount,
                    'approved_commission_amount' => $calculatedAmount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $calculatedAmount,
                    'status' => 'Pending',
                ]
            );

            return $commission;
        });
    }

    /**
     * Manager/Admin approval workflow for calculated commission
     */
    public function approveCommission(Commission $commission, float $approvedAmount, User $approver, ?string $notes = null): Commission
    {
        return DB::transaction(function () use ($commission, $approvedAmount, $approver, $notes) {
            $balance = max(0, $approvedAmount - $commission->paid_amount);

            $commission->update([
                'approved_commission_amount' => $approvedAmount,
                'balance_amount' => $balance,
                'status' => 'Approved',
                'approved_by_user_id' => $approver->id,
                'approved_at' => now(),
                'notes' => $notes ?? $commission->notes,
            ]);

            return $commission;
        });
    }

    /**
     * Record payment payout to Channel Partner
     */
    public function recordPayout(Commission $commission, array $payoutData, User $processor): CommissionPayout
    {
        return DB::transaction(function () use ($commission, $payoutData, $processor) {
            $companyId = $processor->company_id;
            $payoutAmount = (float) $payoutData['amount'];

            if ($payoutAmount <= 0) {
                throw new Exception("Payout amount must be greater than 0.");
            }

            $payout = CommissionPayout::create([
                'company_id' => $companyId,
                'commission_id' => $commission->id,
                'amount' => $payoutAmount,
                'payment_date' => $payoutData['payment_date'] ?? now()->toDateString(),
                'payment_mode' => $payoutData['payment_mode'] ?? 'NEFT',
                'payment_reference' => $payoutData['payment_reference'] ?? null,
                'bank_details' => $payoutData['bank_details'] ?? null,
                'notes' => $payoutData['notes'] ?? null,
                'processed_by_user_id' => $processor->id,
            ]);

            // Update Commission totals and state
            $newPaid = $commission->paid_amount + $payoutAmount;
            $newBalance = max(0, $commission->approved_commission_amount - $newPaid);
            $newStatus = ($newBalance <= 0) ? 'Paid' : 'Payable';

            $commission->update([
                'paid_amount' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => $newStatus,
            ]);

            return $payout;
        });
    }
}

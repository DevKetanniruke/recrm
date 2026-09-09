<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\OfferNegotiationRound;
use App\Models\PaymentSchedule;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OfferService
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Create formal price offer & trigger discount approval engine.
     */
    public function createOffer(
        Lead $lead,
        Unit $unit,
        float $offeredPrice,
        float $tokenAmount,
        string $paymentPlanType,
        int $validityDays,
        User $creator,
        ?string $terms = null
    ): Offer {
        return DB::transaction(function () use ($lead, $unit, $offeredPrice, $tokenAmount, $paymentPlanType, $validityDays, $creator, $terms) {
            $originalPrice = (float) ($unit->total_price ?? $unit->pricing?->calculated_total_price ?? 0);
            $discountAmount = max(0, $originalPrice - $offeredPrice);
            $discountPercent = $originalPrice > 0 ? round(($discountAmount / $originalPrice) * 100, 2) : 0;

            // Approval Threshold Matrix
            if ($discountPercent <= 5.00) {
                $status = 'Approved';
                $approvedBy = $creator->id;
            } elseif ($discountPercent <= 12.00) {
                $status = 'Pending Manager Approval';
                $approvedBy = null;
            } else {
                $status = 'Pending Admin Approval';
                $approvedBy = null;
            }

            $lockHours = 72; // Default 72 hours lock
            $lockExpiresAt = now()->addHours($lockHours);
            $validUntil = now()->addDays($validityDays);

            $offer = Offer::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'unit_id' => $unit->id,
                'created_by' => $creator->id,
                'approved_by' => $approvedBy,
                'original_unit_price' => $originalPrice,
                'offered_price' => $offeredPrice,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercent,
                'token_amount_offered' => $tokenAmount,
                'payment_plan_type' => $paymentPlanType,
                'valid_until' => $validUntil,
                'unit_lock_expires_at' => $lockExpiresAt,
                'status' => $status,
                'terms_conditions' => $terms ?? 'Standard booking terms apply. Offer valid for specified duration.',
            ]);

            // Create Round 1
            OfferNegotiationRound::create([
                'company_id' => $lead->company_id,
                'offer_id' => $offer->id,
                'round_number' => 1,
                'offered_by' => 'Buyer',
                'proposed_by_user_id' => $creator->id,
                'proposed_price' => $offeredPrice,
                'discount_amount' => $discountAmount,
                'requested_token_amount' => $tokenAmount,
                'payment_terms' => $paymentPlanType,
                'comments' => 'Initial buyer formal offer submission.',
            ]);

            // Temporary Unit Lock -> Hold
            $unit->updateInventoryStatus('Hold', "Unit locked for Offer #{$offer->offer_number} (Expires: {$lockExpiresAt->format('Y-m-d H:i')})", $creator->id);

            // Log activity on Lead
            $this->leadService->logActivity(
                lead: $lead,
                type: 'Note',
                subject: "Formal Offer Created (#{$offer->offer_number})",
                summary: "Offered ₹" . number_format($offeredPrice) . " (Discount: {$discountPercent}%) for Unit #{$unit->unit_number}. Status: {$status}.",
                user: $creator
            );

            return $offer;
        });
    }

    /**
     * Submit counter offer round.
     */
    public function submitCounterOffer(
        Offer $offer,
        float $counterPrice,
        float $tokenAmount,
        string $offeredBy,
        User $user,
        ?string $comments = null
    ): Offer {
        return DB::transaction(function () use ($offer, $counterPrice, $tokenAmount, $offeredBy, $user, $comments) {
            $nextRound = $offer->negotiationRounds()->max('round_number') + 1;
            $originalPrice = (float) $offer->original_unit_price;
            $discountAmount = max(0, $originalPrice - $counterPrice);
            $discountPercent = $originalPrice > 0 ? round(($discountAmount / $originalPrice) * 100, 2) : 0;

            OfferNegotiationRound::create([
                'company_id' => $offer->company_id,
                'offer_id' => $offer->id,
                'round_number' => $nextRound,
                'offered_by' => $offeredBy,
                'proposed_by_user_id' => $user->id,
                'proposed_price' => $counterPrice,
                'discount_amount' => $discountAmount,
                'requested_token_amount' => $tokenAmount,
                'comments' => $comments,
            ]);

            $offer->update([
                'offered_price' => $counterPrice,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercent,
                'token_amount_offered' => $tokenAmount,
                'status' => 'Countered',
            ]);

            $this->leadService->logActivity(
                lead: $offer->lead,
                type: 'Note',
                subject: "Counter Offer Submitted (Round {$nextRound})",
                summary: "{$offeredBy} proposed ₹" . number_format($counterPrice) . ($comments ? " - {$comments}" : ""),
                user: $user
            );

            return $offer;
        });
    }

    /**
     * Approve offer.
     */
    public function approveOffer(Offer $offer, User $approver, ?string $notes = null): Offer
    {
        return DB::transaction(function () use ($offer, $approver, $notes) {
            $offer->update([
                'status' => 'Approved',
                'approved_by' => $approver->id,
                'approval_notes' => $notes,
            ]);

            $this->leadService->logActivity(
                lead: $offer->lead,
                type: 'Note',
                subject: "Offer #{$offer->offer_number} Approved",
                summary: "Approved by {$approver->name}." . ($notes ? " Notes: {$notes}" : ""),
                user: $approver
            );

            return $offer;
        });
    }

    /**
     * Reject offer & release unit lock.
     */
    public function rejectOffer(Offer $offer, User $user, string $reason): Offer
    {
        return DB::transaction(function () use ($offer, $user, $reason) {
            $offer->update([
                'status' => 'Rejected',
                'rejection_reason' => $reason,
            ]);

            // Release unit status back to Available
            $offer->unit->updateInventoryStatus('Available', "Offer #{$offer->offer_number} rejected: {$reason}", $user->id);

            $this->leadService->logActivity(
                lead: $offer->lead,
                type: 'Note',
                subject: "Offer #{$offer->offer_number} Rejected",
                summary: "Rejected by {$user->name}. Reason: {$reason}",
                user: $user
            );

            return $offer;
        });
    }

    /**
     * One-click convert approved offer into Booking contract.
     */
    public function convertToBooking(Offer $offer, User $actor): Booking
    {
        return DB::transaction(function () use ($offer, $actor) {
            $lead = $offer->lead;
            $unit = $offer->unit;

            // 1. Create or fetch Customer from Lead
            $customer = Customer::where('company_id', $offer->company_id)
                ->where('email', $lead->email)
                ->orWhere('phone', $lead->mobile)
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'company_id' => $offer->company_id,
                    'lead_id' => $lead->id,
                    'first_name' => $lead->first_name,
                    'last_name' => $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->mobile,
                    'address' => $lead->location ?? $lead->city,
                    'kyc_status' => 'Pending',
                ]);
            }

            // 2. Generate Booking
            $maxBk = Booking::where('company_id', $offer->company_id)->max('id') + 1;
            $bookingNumber = 'BK-' . date('Y') . '-' . str_pad((string) $maxBk, 4, '0', STR_PAD_LEFT);

            $booking = Booking::create([
                'company_id' => $offer->company_id,
                'booking_number' => $bookingNumber,
                'unit_id' => $unit->id,
                'customer_id' => $customer->id,
                'sales_agent_id' => $lead->assigned_to ?? $actor->id,
                'booking_date' => now()->toDateString(),
                'agreed_price' => $offer->offered_price,
                'discount_amount' => $offer->discount_amount,
                'total_amount' => $offer->offered_price,
                'booking_amount_paid' => $offer->token_amount_offered,
                'status' => 'Confirmed',
            ]);

            // 3. Update Unit Status -> Booked
            $unit->updateInventoryStatus('Booked', "Converted from Offer #{$offer->offer_number} (Booking #{$bookingNumber})", $actor->id);

            // 4. Update Lead Status -> Won
            $lead->update(['status' => 'Won']);

            // 5. Update Offer Status -> Converted to Booking
            $offer->update(['status' => 'Converted to Booking']);

            // 6. Generate Initial Milestone Schedule
            PaymentSchedule::create([
                'company_id' => $offer->company_id,
                'booking_id' => $booking->id,
                'milestone_name' => 'Token Advance (Paid)',
                'due_date' => now()->toDateString(),
                'amount_due' => $offer->token_amount_offered,
                'amount_paid' => $offer->token_amount_offered,
                'status' => 'Paid',
            ]);

            $remainingAmount = max(0, $offer->offered_price - $offer->token_amount_offered);
            PaymentSchedule::create([
                'company_id' => $offer->company_id,
                'booking_id' => $booking->id,
                'milestone_name' => 'Agreement Execution (15%)',
                'due_date' => now()->addDays(30)->toDateString(),
                'amount_due' => round($remainingAmount * 0.15, 2),
                'amount_paid' => 0,
                'status' => 'Pending',
            ]);

            $this->leadService->logActivity(
                lead: $lead,
                type: 'Status Change',
                subject: "Offer Converted to Confirmed Booking (#{$bookingNumber})",
                summary: "Unit #{$unit->unit_number} booked for ₹" . number_format($offer->offered_price),
                user: $actor
            );

            return $booking;
        });
    }

    /**
     * Release expired offer unit locks.
     */
    public function releaseExpiredLock(Offer $offer): void
    {
        if ($offer->status !== 'Converted to Booking' && $offer->status !== 'Rejected' && $offer->status !== 'Expired') {
            DB::transaction(function () use ($offer) {
                $offer->update(['status' => 'Expired']);
                $offer->unit->updateInventoryStatus('Available', "Unit lock expired for Offer #{$offer->offer_number}", null);
            });
        }
    }
}

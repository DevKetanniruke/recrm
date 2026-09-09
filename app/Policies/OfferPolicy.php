<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Offer $offer): bool
    {
        if ($user->company_id !== $offer->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $allowedUserIds = $user->getTeamMemberIds();
        return in_array($offer->created_by, $allowedUserIds) ||
               in_array($offer->lead->assigned_to, $allowedUserIds);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Offer $offer): bool
    {
        if ($user->company_id !== $offer->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin() || $user->id === $offer->created_by || $user->isManager();
    }

    public function approve(User $user, Offer $offer): bool
    {
        if ($user->company_id !== $offer->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $discountPercent = (float) $offer->discount_percentage;

        if ($discountPercent <= 5.00) {
            return true;
        }

        if ($discountPercent <= 12.00) {
            return $user->isManager();
        }

        return $user->isAdmin();
    }

    public function reject(User $user, Offer $offer): bool
    {
        if ($user->company_id !== $offer->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin() || $user->isManager() || $user->id === $offer->created_by;
    }

    public function convertBooking(User $user, Offer $offer): bool
    {
        if ($user->company_id !== $offer->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        return $offer->status === 'Approved' && ($user->isAdmin() || $user->isManager() || $user->id === $offer->created_by);
    }
}

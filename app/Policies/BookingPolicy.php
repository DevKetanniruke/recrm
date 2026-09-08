<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->company_id === $booking->company_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSalesAgent() || $user->isProjectManager();
    }

    public function update(User $user, Booking $booking): bool
    {
        return ($user->company_id === $booking->company_id && ($user->isSalesAgent() || $user->isProjectManager())) || $user->isSuperAdmin();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return ($user->company_id === $booking->company_id && $user->isAdmin()) || $user->isSuperAdmin();
    }
}

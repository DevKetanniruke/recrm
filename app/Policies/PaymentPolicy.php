<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAccountant() || $user->isAdmin();
    }

    public function update(User $user, Payment $payment): bool
    {
        return ($user->company_id === $payment->company_id && $user->isAccountant()) || $user->isSuperAdmin();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return ($user->company_id === $payment->company_id && $user->isAdmin()) || $user->isSuperAdmin();
    }
}

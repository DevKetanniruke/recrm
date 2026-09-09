<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payments.view') || $user->isAdmin() || $user->isAccountant();
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && ($user->hasPermissionTo('payments.view') || $user->isAdmin() || $user->isAccountant());
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.create') || $user->isAdmin() || $user->isAccountant();
    }

    public function reverse(User $user, Payment $payment): bool
    {
        return $user->company_id === $payment->company_id && ($user->isAdmin() || $user->hasPermissionTo('payments.reverse'));
    }
}

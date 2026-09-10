<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('commissions.view') || $user->isAdmin() || $user->isAccountant() || $user->isSalesManager();
    }

    public function approve(User $user, Commission $commission): bool
    {
        return $user->company_id === $commission->company_id && ($user->isAdmin() || $user->isSalesManager() || $user->hasPermissionTo('commissions.approve'));
    }

    public function payout(User $user, Commission $commission): bool
    {
        return $user->company_id === $commission->company_id && ($user->isAdmin() || $user->isAccountant() || $user->hasPermissionTo('commissions.payout'));
    }
}

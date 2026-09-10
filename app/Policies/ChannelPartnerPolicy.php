<?php

namespace App\Policies;

use App\Models\ChannelPartner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChannelPartnerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('brokers.view') || $user->isAdmin() || $user->isSalesManager();
    }

    public function view(User $user, ChannelPartner $partner): bool
    {
        return $user->company_id === $partner->company_id && ($user->hasPermissionTo('brokers.view') || $user->isAdmin() || $user->isSalesManager());
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('brokers.manage') || $user->isAdmin() || $user->isSalesManager();
    }

    public function update(User $user, ChannelPartner $partner): bool
    {
        return $user->company_id === $partner->company_id && ($user->hasPermissionTo('brokers.manage') || $user->isAdmin() || $user->isSalesManager());
    }
}

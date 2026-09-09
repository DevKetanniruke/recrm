<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommunicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('marketing.view') || $user->isAdmin() || $user->isSalesManager();
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('marketing.manage') || $user->isAdmin() || $user->isSalesManager();
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('marketing.manage') || $user->isAdmin() || $user->isSalesManager();
    }
}

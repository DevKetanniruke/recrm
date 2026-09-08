<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('leads.view') || $user->isAdmin();
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->company_id !== $lead->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $allowedUserIds = $user->getTeamMemberIds();
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        return in_array($lead->assigned_to, $allowedUserIds) ||
            ($lead->assigned_team_id && in_array($lead->assigned_team_id, $userTeamIds));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('leads.create') || $user->isAdmin();
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->company_id !== $lead->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $allowedUserIds = $user->getTeamMemberIds();
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();

        return in_array($lead->assigned_to, $allowedUserIds) ||
            ($lead->assigned_team_id && in_array($lead->assigned_team_id, $userTeamIds));
    }

    public function delete(User $user, Lead $lead): bool
    {
        if ($user->company_id !== $lead->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin() || $user->hasPermissionTo('leads.delete');
    }

    public function assign(User $user, Lead $lead): bool
    {
        if ($user->company_id !== $lead->company_id && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin() || $user->isManager() || $user->hasPermissionTo('leads.assign');
    }
}

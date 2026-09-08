<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isProjectManager();
    }

    public function update(User $user, Project $project): bool
    {
        return ($user->company_id === $project->company_id && $user->isProjectManager()) || $user->isSuperAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return ($user->company_id === $project->company_id && $user->isAdmin()) || $user->isSuperAdmin();
    }
}

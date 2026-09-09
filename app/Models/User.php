<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, Auditable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'status',
        'profile_photo',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_has_roles');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')->withTimestamps();
    }

    public function managedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'manager_id');
    }

    public function assignRole(Role|string $role): void
    {
        $roleObj = is_string($role)
            ? Role::where('slug', $role)->firstOrFail()
            : $role;

        $this->roles()->syncWithoutDetaching([$roleObj->id]);
        $this->update(['role' => $roleObj->slug]);
    }

    public function hasPermissionTo(string $permissionSlug): bool
    {
        // Super Admin & Admin have all permissions by default
        if ($this->role === 'super_admin' || $this->role === 'admin') {
            return true;
        }

        // All active users in the CRM system have access to dashboard
        if ($permissionSlug === 'dashboard.view') {
            return true;
        }

        // Role-based fallbacks for standard CRM roles
        $rolePermissionsMap = [
            'sales_manager' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'leads.assign', 'projects.view', 'inventory.view', 'bookings.view'],
            'crm_manager' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'leads.assign', 'projects.view', 'inventory.view', 'bookings.view'],
            'manager' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'leads.assign', 'projects.view', 'inventory.view', 'bookings.view'],
            'sales_agent' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'projects.view', 'inventory.view', 'bookings.view'],
            'sales_executive' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'projects.view', 'inventory.view', 'bookings.view'],
            'telecaller' => ['dashboard.view', 'leads.view', 'leads.create', 'leads.edit'],
            'accountant' => ['dashboard.view', 'payments.view', 'payments.create', 'bookings.view'],
        ];

        if (isset($rolePermissionsMap[$this->role]) && in_array($permissionSlug, $rolePermissionsMap[$this->role])) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['sales_manager', 'crm_manager', 'manager']) || $this->managedTeams()->exists();
    }

    public function isSalesExecutive(): bool
    {
        return in_array($this->role, ['sales_executive', 'sales_agent', 'telecaller']);
    }

    /**
     * Get user IDs belonging to teams managed by or associated with this user.
     */
    public function getTeamMemberIds(): array
    {
        $teamIds = $this->managedTeams()->pluck('id')
            ->merge($this->teams()->pluck('teams.id'))
            ->unique();

        if ($teamIds->isEmpty()) {
            return [$this->id];
        }

        $memberIds = \DB::table('team_members')
            ->whereIn('team_id', $teamIds)
            ->pluck('user_id')
            ->toArray();

        return array_unique(array_merge([$this->id], $memberIds));
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}

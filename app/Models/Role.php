<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, BelongsToCompany, Auditable;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_roles');
    }

    public function givePermissionTo(Permission|string $permission): void
    {
        $permObj = is_string($permission)
            ? Permission::where('slug', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->syncWithoutDetaching([$permObj->id]);
    }
}

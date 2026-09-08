<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V01RoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_middleware_blocks_unauthorized_user(): void
    {
        $company = Company::create(['name' => 'PropFlow Realty', 'slug' => 'propflow']);

        $role = Role::create(['company_id' => $company->id, 'name' => 'Telecaller', 'slug' => 'telecaller']);
        $permission = Permission::create(['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users']);

        // User without users.view permission
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Telecaller User',
            'email' => 'telecaller@test.com',
            'password' => bcrypt('password'),
            'role' => 'telecaller',
            'status' => 'Active',
        ]);
        $user->roles()->attach($role->id);

        $this->actingAs($user);

        // Attempt accessing users.index protected by permission:users.view
        $response = $this->get(route('users.index'));
        $response->assertStatus(403);

        // Grant permission to role
        $role->permissions()->attach($permission->id);

        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
    }
}

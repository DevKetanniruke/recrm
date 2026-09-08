<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V01AuthAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_last_login_at_is_updated(): void
    {
        $company = Company::create(['name' => 'PropFlow Realty', 'slug' => 'propflow']);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'John Developer',
            'email' => 'john@propflow.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $response = $this->post('/login', [
            'email' => 'john@propflow.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $company = Company::create(['name' => 'PropFlow Realty', 'slug' => 'propflow']);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Inactive User',
            'email' => 'inactive@propflow.com',
            'password' => bcrypt('secret123'),
            'role' => 'sales_agent',
            'status' => 'Inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@propflow.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
    }

    public function test_user_crud_and_status_toggle(): void
    {
        $company = Company::create(['name' => 'PropFlow Realty', 'slug' => 'propflow']);
        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Admin',
            'email' => 'admin@propflow.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
            'status' => 'Active',
        ]);

        $this->actingAs($admin);

        // Store User
        $response = $this->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'staff@propflow.com',
            'mobile' => '+15550190088',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'sales_executive',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'staff@propflow.com', 'mobile' => '+15550190088']);

        $newUser = User::where('email', 'staff@propflow.com')->first();

        // Update status to Suspended
        $response = $this->post(route('users.update-status', $newUser->id), [
            'status' => 'Suspended',
        ]);

        $newUser->refresh();
        $this->assertEquals('Suspended', $newUser->status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_access_their_company_projects(): void
    {
        // 1. Create Company A and User A
        $companyA = Company::create(['name' => 'Builder A', 'slug' => 'builder-a']);
        $userA = User::create([
            'company_id' => $companyA->id,
            'name' => 'Agent A',
            'email' => 'agentA@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create Project for Company A
        $projectA = Project::create([
            'company_id' => $companyA->id,
            'name' => 'Alpha Towers',
            'status' => 'Ongoing',
        ]);

        // 2. Create Company B and User B
        $companyB = Company::create(['name' => 'Builder B', 'slug' => 'builder-b']);
        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'Agent B',
            'email' => 'agentB@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create Project for Company B
        $projectB = Project::create([
            'company_id' => $companyB->id,
            'name' => 'Beta Residency',
            'status' => 'Ongoing',
        ]);

        // 3. Authenticate as User A and query projects
        $this->actingAs($userA);

        $projectsForA = Project::all();

        $this->assertTrue($projectsForA->contains($projectA));
        $this->assertFalse($projectsForA->contains($projectB));
    }

    public function test_user_cannot_view_other_company_lead(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'slug' => 'comp-a']);
        $userA = User::create(['company_id' => $companyA->id, 'name' => 'User A', 'email' => 'a@test.com', 'password' => bcrypt('password'), 'role' => 'sales_agent']);
        $leadA = Lead::create(['company_id' => $companyA->id, 'first_name' => 'John', 'phone' => '1234567890']);

        $companyB = Company::create(['name' => 'Company B', 'slug' => 'comp-b']);
        $userB = User::create(['company_id' => $companyB->id, 'name' => 'User B', 'email' => 'b@test.com', 'password' => bcrypt('password'), 'role' => 'sales_agent']);

        // Authenticate as User B
        $this->actingAs($userB);

        // BelongsToCompany global scope automatically scopes queries to company_id, returning 404 for cross-tenant access attempts.
        $response = $this->get(route('leads.show', $leadA->id));
        $response->assertStatus(404);
    }
}

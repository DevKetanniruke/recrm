<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V03LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $admin;
    protected User $manager;
    protected User $salesExec;
    protected Team $team;
    protected LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->leadService = app(LeadService::class);

        // 1. Create test company
        $this->company = Company::create([
            'name' => 'Acme Real Estate Developers',
            'legal_name' => 'Acme Realty Corp',
            'slug' => 'acme-realty',
            'email' => 'contact@acmerealty.com',
        ]);

        // 2. Create users with roles
        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'mobile' => '9900000001',
            'status' => 'Active',
        ]);

        $this->manager = User::create([
            'company_id' => $this->company->id,
            'name' => 'Sales Manager',
            'email' => 'manager@acme.com',
            'password' => bcrypt('password'),
            'role' => 'sales_manager',
            'mobile' => '9900000002',
            'status' => 'Active',
        ]);

        $this->salesExec = User::create([
            'company_id' => $this->company->id,
            'name' => 'Sales Executive',
            'email' => 'exec@acme.com',
            'password' => bcrypt('password'),
            'role' => 'sales_executive',
            'mobile' => '9900000003',
            'status' => 'Active',
        ]);

        // 3. Create Team
        $this->team = Team::create([
            'company_id' => $this->company->id,
            'name' => 'Alpha Sales Team',
            'manager_id' => $this->manager->id,
        ]);
        $this->team->members()->attach([$this->salesExec->id, $this->manager->id]);

        // 4. Create default sources & statuses
        LeadSource::create(['company_id' => $this->company->id, 'name' => 'Website', 'is_active' => true]);
        LeadSource::create(['company_id' => $this->company->id, 'name' => 'Facebook Ads', 'is_active' => true]);

        LeadStatus::create(['company_id' => $this->company->id, 'name' => 'New', 'color_code' => '#3b82f6', 'sort_order' => 10]);
        LeadStatus::create(['company_id' => $this->company->id, 'name' => 'Won', 'color_code' => '#10b981', 'sort_order' => 80, 'is_won' => true]);
    }

    #[Test]
    public function lead_creation_populates_number_and_records_initial_activity()
    {
        $response = $this->actingAs($this->admin)->post(route('leads.store'), [
            'first_name' => 'Robert',
            'last_name' => 'Baratheon',
            'mobile' => '9888877771',
            'email' => 'robert@stormsend.com',
            'source' => 'Website',
            'status' => 'New',
            'priority' => 'Hot',
            'minimum_budget' => 5000000,
            'maximum_budget' => 8000000,
            'assigned_to' => $this->salesExec->id,
            'assigned_team_id' => $this->team->id,
            'notes' => 'Looking for luxury villa',
        ]);

        $response->assertRedirect();
        
        $lead = Lead::where('email', 'robert@stormsend.com')->first();
        $this->assertNotNull($lead);
        $this->assertStringStartsWith('LD-', $lead->lead_number);
        $this->assertEquals('Robert Baratheon', $lead->full_name);
        $this->assertEquals($this->salesExec->id, $lead->assigned_to);

        // Check initial activity created
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'Note',
        ]);

        // Check assignment history recorded
        $this->assertDatabaseHas('lead_assignment_histories', [
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $this->salesExec->id,
        ]);
    }

    #[Test]
    public function configurable_sources_and_statuses_can_be_managed_by_admin()
    {
        $this->actingAs($this->admin)->post(route('lead-config.sources.store'), [
            'name' => 'Radio Campaign',
        ])->assertRedirect();

        $this->assertDatabaseHas('lead_sources', [
            'company_id' => $this->company->id,
            'name' => 'Radio Campaign',
        ]);

        $this->actingAs($this->admin)->post(route('lead-config.statuses.store'), [
            'name' => 'Negotiation Phase',
            'color_code' => '#8b5cf6',
            'sort_order' => 60,
        ])->assertRedirect();

        $this->assertDatabaseHas('lead_statuses', [
            'company_id' => $this->company->id,
            'name' => 'Negotiation Phase',
        ]);
    }

    #[Test]
    public function duplicate_check_detects_matching_mobile_or_email()
    {
        Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Existing',
            'last_name' => 'Lead',
            'mobile' => '9988776655',
            'email' => 'existing@test.com',
            'source' => 'Website',
            'status' => 'New',
        ]);

        $duplicates = $this->leadService->checkDuplicates($this->company->id, '9988776655', 'existing@test.com');
        $this->assertCount(1, $duplicates);
    }

    #[Test]
    public function secondary_leads_can_be_merged_into_primary_lead()
    {
        $primary = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Primary',
            'last_name' => 'Lead',
            'mobile' => '9911223344',
            'email' => 'primary@test.com',
            'source' => 'Website',
            'status' => 'New',
        ]);

        $secondary = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Secondary',
            'last_name' => 'Lead',
            'mobile' => '9911223344',
            'email' => 'primary@test.com',
            'source' => 'Facebook Ads',
            'status' => 'Contacted',
            'notes' => 'Enquired on FB',
        ]);

        $this->leadService->mergeLeads($primary, [$secondary->id], $this->admin);

        $primary->refresh();
        $this->assertStringContainsString('Enquired on FB', $primary->notes);
        $this->assertSoftDeleted('leads', ['id' => $secondary->id]);
    }

    #[Test]
    public function followups_can_be_scheduled_and_filtered()
    {
        $lead = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Followup',
            'last_name' => 'Target',
            'mobile' => '9111122222',
            'source' => 'Website',
            'status' => 'New',
        ]);

        $this->actingAs($this->admin)->post(route('followups.store', $lead->id), [
            'type' => 'Call',
            'followup_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Follow up call today',
        ])->assertRedirect();

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'type' => 'Call',
            'status' => 'Pending',
        ]);
    }

    #[Test]
    public function role_based_policy_scopes_leads_correctly()
    {
        $otherUser = User::create([
            'company_id' => $this->company->id,
            'name' => 'Other Exec',
            'email' => 'other@acme.com',
            'password' => bcrypt('password'),
            'role' => 'sales_executive',
            'mobile' => '9900000099',
            'status' => 'Active',
        ]);

        $assignedLead = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'My',
            'last_name' => 'Lead',
            'mobile' => '9555544444',
            'source' => 'Website',
            'status' => 'New',
            'assigned_to' => $this->salesExec->id,
        ]);

        $otherLead = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Other',
            'last_name' => 'Lead',
            'mobile' => '9555544445',
            'source' => 'Website',
            'status' => 'New',
            'assigned_to' => $otherUser->id,
        ]);

        // Sales executive sees assigned lead
        $this->assertTrue($this->salesExec->can('view', $assignedLead));
        // Sales executive cannot see unassigned lead outside their team
        $this->assertFalse($this->salesExec->can('view', $otherLead));

        // Admin can view all
        $this->assertTrue($this->admin->can('view', $assignedLead));
        $this->assertTrue($this->admin->can('view', $otherLead));
    }
}

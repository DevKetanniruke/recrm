<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Building;
use App\Models\ChannelPartner;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class V10ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // Company A Setup
        $this->companyA = Company::create(['name' => 'Company A Realty', 'slug' => 'company-a-realty', 'status' => 'Active']);
        $this->userA = User::create([
            'company_id' => $this->companyA->id,
            'name' => 'User A Admin',
            'email' => 'admin@companya.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        // Company B Setup
        $this->companyB = Company::create(['name' => 'Company B Realty', 'slug' => 'company-b-realty', 'status' => 'Active']);
        $this->userB = User::create([
            'company_id' => $this->companyB->id,
            'name' => 'User B Admin',
            'email' => 'admin@companyb.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);
    }

    public function test_enforces_strict_company_data_isolation_across_all_entities()
    {
        // Create Company B Entities
        $projectB = Project::create(['company_id' => $this->companyB->id, 'name' => 'Project B', 'code' => 'PRJB', 'status' => 'Active']);
        $unitTypeB = UnitType::create(['company_id' => $this->companyB->id, 'name' => '2BHK']);
        $buildingB = Building::create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id, 'name' => 'Block B']);
        $wingB = Wing::create(['company_id' => $this->companyB->id, 'building_id' => $buildingB->id, 'name' => 'Wing B']);
        $floorB = Floor::create(['company_id' => $this->companyB->id, 'wing_id' => $wingB->id, 'floor_number' => 1]);

        $unitB = Unit::create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id, 'building_id' => $buildingB->id, 'wing_id' => $wingB->id, 'floor_id' => $floorB->id, 'unit_type_id' => $unitTypeB->id, 'unit_number' => 'B-101', 'status' => 'Available', 'base_price' => 4000000, 'total_price' => 4500000]);
        $leadB = Lead::create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id, 'assigned_to' => $this->userB->id, 'lead_number' => 'LD-B1', 'first_name' => 'John', 'last_name' => 'Doe', 'mobile' => '9999911111', 'email' => 'john@companyb.com', 'status' => 'New']);
        $customerB = Customer::create(['company_id' => $this->companyB->id, 'customer_code' => 'CUST-B1', 'first_name' => 'John', 'last_name' => 'Doe', 'mobile' => '9999911111', 'email' => 'john@companyb.com', 'status' => 'Active']);
        $bookingB = Booking::create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id, 'unit_id' => $unitB->id, 'customer_id' => $customerB->id, 'sales_agent_id' => $this->userB->id, 'booking_date' => now()->toDateString(), 'booking_code' => 'BKG-B1', 'status' => 'Confirmed', 'agreed_price' => 4500000, 'total_amount' => 4500000]);
        
        Storage::fake('local');
        $docPath = "customer_documents/{$this->companyB->id}/{$customerB->id}/doc.pdf";
        Storage::disk('local')->put($docPath, 'sample content');
        $documentB = CustomerDocument::create([
            'company_id' => $this->companyB->id,
            'customer_id' => $customerB->id,
            'booking_id' => $bookingB->id,
            'document_type' => 'Aadhaar Card',
            'file_name' => 'doc.pdf',
            'file_path' => $docPath,
            'file_size' => 100,
            'mime_type' => 'application/pdf',
            'uploaded_by_user_id' => $this->userB->id,
            'verification_status' => 'Verified',
        ]);

        // Act as User A from Company A
        $this->actingAs($this->userA);

        // Attempting to access Company B records should return 403 Forbidden or 404 Not Found (due to BelongsToCompany global scoping)
        $this->assertContains($this->get(route('leads.show', $leadB->id))->status(), [403, 404]);
        $this->assertContains($this->get(route('projects.show', $projectB->id))->status(), [403, 404]);
        $this->assertContains($this->get(route('bookings.show', $bookingB->id))->status(), [403, 404]);
        $this->assertContains($this->get(route('customers.show', $customerB->id))->status(), [403, 404]);
        $this->assertContains($this->get(route('customer-documents.download', $documentB->id))->status(), [403, 404]);

        // Attempting to access Company B via API v1 should return 403 or 404
        $this->assertContains($this->get("/api/v1/leads/{$leadB->id}")->status(), [403, 404]);
        $this->assertContains($this->get("/api/v1/projects/{$projectB->id}")->status(), [403, 404]);
        $this->assertContains($this->get("/api/v1/bookings/{$bookingB->id}")->status(), [403, 404]);
        $this->assertContains($this->get("/api/v1/customers/{$customerB->id}")->status(), [403, 404]);
    }

    public function test_executes_global_crm_search_with_company_scoping_and_permissions()
    {
        // Company A Lead
        $projectA = Project::create(['company_id' => $this->companyA->id, 'name' => 'Horizon Alpha', 'code' => 'HZA', 'status' => 'Active']);
        Lead::create(['company_id' => $this->companyA->id, 'project_id' => $projectA->id, 'assigned_to' => $this->userA->id, 'lead_number' => 'LD-A100', 'first_name' => 'UniqueName', 'last_name' => 'Alpha', 'mobile' => '9888877777', 'email' => 'unique@alpha.com', 'status' => 'New']);

        // Company B Lead with same search term
        $projectB = Project::create(['company_id' => $this->companyB->id, 'name' => 'Horizon Beta', 'code' => 'HZB', 'status' => 'Active']);
        Lead::create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id, 'assigned_to' => $this->userB->id, 'lead_number' => 'LD-B100', 'first_name' => 'UniqueName', 'last_name' => 'Beta', 'mobile' => '9888877778', 'email' => 'unique@beta.com', 'status' => 'New']);

        $this->actingAs($this->userA);

        $response = $this->get(route('global.search', ['q' => 'UniqueName']));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(1, $data['count']);
        $this->assertStringContainsString('UniqueName Alpha', $data['results'][0]['title']);
    }

    public function test_returns_sanitized_json_resources_via_api_v1()
    {
        $projectA = Project::create(['company_id' => $this->companyA->id, 'name' => 'API Tower', 'code' => 'API1', 'status' => 'Active']);
        $leadA = Lead::create(['company_id' => $this->companyA->id, 'project_id' => $projectA->id, 'assigned_to' => $this->userA->id, 'lead_number' => 'LD-API', 'first_name' => 'API', 'last_name' => 'Test', 'mobile' => '9555544444', 'email' => 'api@test.com', 'status' => 'New']);

        $this->actingAs($this->userA);

        $responseLeads = $this->get('/api/v1/leads');
        $responseLeads->assertStatus(200);
        $responseLeads->assertJsonStructure(['data' => [['id', 'lead_number', 'first_name', 'last_name', 'full_name', 'mobile', 'email', 'status']]]);

        $responseProjects = $this->get('/api/v1/projects');
        $responseProjects->assertStatus(200);
        $responseProjects->assertJsonStructure(['data' => [['id', 'name', 'code', 'status']]]);
    }

    public function test_renders_custom_production_error_pages()
    {
        $this->actingAs($this->userA);

        // 404 Route
        $response404 = $this->get('/non-existent-route-9999');
        $response404->assertStatus(404);

        // 403 Permission Denial
        $nonAdminUser = User::create([
            'company_id' => $this->companyA->id,
            'name' => 'Restricted User',
            'email' => 'restricted@companya.com',
            'password' => bcrypt('password123'),
            'role' => 'sales_agent',
            'status' => 'Active',
        ]);
        $this->actingAs($nonAdminUser);

        $response403 = $this->get(route('users.index'));
        $response403->assertStatus(403);
    }
}

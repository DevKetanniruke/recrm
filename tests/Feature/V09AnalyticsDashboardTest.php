<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Building;
use App\Models\ChannelPartner;
use App\Models\Commission;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V09AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected Project $project;
    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Apex Realty Ltd', 'slug' => 'apex-realty-ltd', 'status' => 'Active']);
        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Executive User',
            'email' => 'executive@apex.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Apex Horizon Towers',
            'code' => 'AHT',
            'status' => 'Active',
        ]);

        $this->analyticsService = app(AnalyticsService::class);
    }

    public function test_executive_dashboard_kpis_and_funnel_percentages()
    {
        $unitType = UnitType::create(['company_id' => $this->company->id, 'name' => '3BHK Luxury']);
        $building = Building::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'name' => 'Tower A']);
        $wing = Wing::create(['company_id' => $this->company->id, 'building_id' => $building->id, 'name' => 'East Wing']);
        $floor = Floor::create(['company_id' => $this->company->id, 'wing_id' => $wing->id, 'floor_number' => 5]);

        // Units
        $u1 = Unit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'building_id' => $building->id, 'wing_id' => $wing->id, 'floor_id' => $floor->id, 'unit_type_id' => $unitType->id, 'unit_number' => '501', 'status' => 'Available', 'base_price' => 5000000, 'total_price' => 5500000]);
        $u2 = Unit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'building_id' => $building->id, 'wing_id' => $wing->id, 'floor_id' => $floor->id, 'unit_type_id' => $unitType->id, 'unit_number' => '502', 'status' => 'Booked', 'base_price' => 6000000, 'total_price' => 6500000]);
        $u3 = Unit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'building_id' => $building->id, 'wing_id' => $wing->id, 'floor_id' => $floor->id, 'unit_type_id' => $unitType->id, 'unit_number' => '503', 'status' => 'Sold', 'base_price' => 7000000, 'total_price' => 7500000]);

        // Leads & Funnel stages
        $l1 = Lead::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'assigned_to' => $this->user->id, 'lead_number' => 'LD-101', 'first_name' => 'Rajesh', 'last_name' => 'Sharma', 'mobile' => '9876543210', 'email' => 'rajesh@gmail.com', 'status' => 'New']);
        $l2 = Lead::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'assigned_to' => $this->user->id, 'lead_number' => 'LD-102', 'first_name' => 'Priya', 'last_name' => 'Patel', 'mobile' => '9876543211', 'email' => 'priya@gmail.com', 'status' => 'Qualified']);
        $l3 = Lead::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'assigned_to' => $this->user->id, 'lead_number' => 'LD-103', 'first_name' => 'Amit', 'last_name' => 'Kumar', 'mobile' => '9876543212', 'email' => 'amit@gmail.com', 'status' => 'Won']);

        // Site Visits
        SiteVisit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'lead_id' => $l2->id, 'assigned_to' => $this->user->id, 'visit_date' => now(), 'status' => 'Completed']);

        // Customer & Booking
        $customer = Customer::create(['company_id' => $this->company->id, 'customer_code' => 'CUST-001', 'first_name' => 'Amit', 'last_name' => 'Kumar', 'mobile' => '9876543212', 'email' => 'amit@gmail.com', 'status' => 'Active']);
        $booking = Booking::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'unit_id' => $u2->id, 'customer_id' => $customer->id, 'sales_agent_id' => $this->user->id, 'booking_date' => now()->toDateString(), 'booking_code' => 'BKG-502', 'status' => 'Confirmed', 'agreed_price' => 6500000, 'total_amount' => 6500000, 'agreement_value' => 6500000]);

        // Payment Schedules & Payments
        PaymentSchedule::create(['company_id' => $this->company->id, 'booking_id' => $booking->id, 'milestone_name' => 'Booking Token', 'amount' => 500000, 'amount_due' => 500000, 'due_date' => now()->subDays(10)->toDateString(), 'status' => 'Unpaid']);
        Payment::create(['company_id' => $this->company->id, 'booking_id' => $booking->id, 'received_by' => $this->user->id, 'receipt_number' => 'RCT-1001', 'payment_mode' => 'NEFT/RTGS', 'amount_paid' => 500000, 'payment_date' => now()->toDateString(), 'status' => 'Verified']);

        $metrics = $this->analyticsService->getExecutiveMetrics($this->company->id);

        $this->assertEquals(1, $metrics['totalProjects']);
        $this->assertEquals(3, $metrics['totalUnits']);
        $this->assertEquals(1, $metrics['availableUnits']);
        $this->assertEquals(1, $metrics['bookedUnits']);
        $this->assertEquals(1, $metrics['soldUnits']);
        $this->assertEquals(3, $metrics['totalLeads']);
        $this->assertEquals(2, $metrics['qualifiedLeads']);
        $this->assertEquals(1, $metrics['siteVisits']);
        $this->assertEquals(1, $metrics['bookings']);
        $this->assertEquals(6500000.0, $metrics['bookingValue']);
        $this->assertEquals(500000.0, $metrics['currentPeriodCollection']);
        $this->assertEquals(500000.0, $metrics['outstandingPayments']);

        // Check 8-stage funnel structure
        $this->assertArrayHasKey('Leads', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Contacted', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Qualified', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Site Visit', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Negotiation', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Offer', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Booking', $metrics['salesFunnel']);
        $this->assertArrayHasKey('Won', $metrics['salesFunnel']);
    }

    public function test_filtering_for_all_7_report_categories()
    {
        $unitType = UnitType::create(['company_id' => $this->company->id, 'name' => '2BHK Executive']);
        $building = Building::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'name' => 'Block B']);
        $wing = Wing::create(['company_id' => $this->company->id, 'building_id' => $building->id, 'name' => 'West Wing']);
        $floor = Floor::create(['company_id' => $this->company->id, 'wing_id' => $wing->id, 'floor_number' => 2]);

        // 1. Lead Report Data
        $lead = Lead::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'assigned_to' => $this->user->id, 'lead_number' => 'LD-901', 'first_name' => 'Report', 'last_name' => 'Lead', 'mobile' => '9111111111', 'email' => 'lead901@test.com', 'source' => 'Website', 'status' => 'New']);
        $leadData = $this->analyticsService->getLeadReportData($this->company->id, ['source' => 'Website']);
        $this->assertEquals(1, $leadData['totalCount']);

        // 2. Site Visit Report Data
        $sv = SiteVisit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'lead_id' => $lead->id, 'assigned_to' => $this->user->id, 'visit_date' => now(), 'status' => 'Scheduled']);
        $svData = $this->analyticsService->getSiteVisitReportData($this->company->id, ['status' => 'Scheduled']);
        $this->assertEquals(1, $svData['scheduledVisits']);

        // 3. Sales Report Data
        $uSales = Unit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'building_id' => $building->id, 'wing_id' => $wing->id, 'floor_id' => $floor->id, 'unit_type_id' => $unitType->id, 'unit_number' => '901', 'status' => 'Booked', 'base_price' => 4000000, 'total_price' => 4500000]);
        $customer = Customer::create(['company_id' => $this->company->id, 'customer_code' => 'CUST-002', 'first_name' => 'Sales', 'last_name' => 'Cust', 'mobile' => '9222222222', 'email' => 'salescust@test.com', 'status' => 'Active']);
        Booking::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'unit_id' => $uSales->id, 'customer_id' => $customer->id, 'sales_agent_id' => $this->user->id, 'booking_date' => now()->toDateString(), 'booking_code' => 'BKG-901', 'status' => 'Confirmed', 'agreed_price' => 4500000, 'total_amount' => 4500000, 'agreement_value' => 4500000]);
        $salesData = $this->analyticsService->getSalesReportData($this->company->id, ['project_id' => $this->project->id]);
        $this->assertEquals(1, $salesData['totalBookings']);
        $this->assertEquals(4500000.0, $salesData['totalSalesValue']);

        // 4. Inventory Report Data
        $u = Unit::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'building_id' => $building->id, 'wing_id' => $wing->id, 'floor_id' => $floor->id, 'unit_type_id' => $unitType->id, 'unit_number' => '101', 'status' => 'Available', 'base_price' => 3000000, 'total_price' => 3500000]);
        $invData = $this->analyticsService->getInventoryReportData($this->company->id, ['status' => 'Available']);
        $this->assertEquals(1, $invData['available']);

        // 5. Finance Report Data
        $finData = $this->analyticsService->getFinanceReportData($this->company->id, []);
        $this->assertArrayHasKey('totalCollected', $finData);

        // 6. Channel Partner Report Data
        $cp = ChannelPartner::create(['company_id' => $this->company->id, 'partner_code' => 'CP-101', 'company_name' => 'Prime Realty Brokers', 'contact_person' => 'Vikram', 'mobile' => '9333333333', 'email' => 'vikram@prime.com', 'status' => 'Active', 'onboarding_date' => now()->toDateString()]);
        $cpData = $this->analyticsService->getChannelPartnerReportData($this->company->id, ['partner_id' => $cp->id]);
        $this->assertCount(1, $cpData['partners']);

        // 7. Executive Performance Data
        $execData = $this->analyticsService->getExecutivePerformanceData($this->company->id, ['assigned_to' => $this->user->id]);
        $this->assertCount(1, $execData['executives']);
    }

    public function test_serves_dashboard_and_reports_routes()
    {
        $this->actingAs($this->user);

        $responseDashboard = $this->get(route('dashboard'));
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertSee('Executive Management Dashboard');

        $tabs = ['leads', 'site_visits', 'sales', 'inventory', 'finance', 'channel_partners', 'executives'];
        foreach ($tabs as $tab) {
            $res = $this->get(route('reports.index', ['tab' => $tab]));
            $res->assertStatus(200);
            $res->assertSee('Multi-Dimensional CRM Reports Portal');
        }
    }

    public function test_generates_csv_streaming_exports()
    {
        $this->actingAs($this->user);

        $exportTypes = ['leads', 'site_visits', 'sales', 'inventory', 'finance', 'channel_partners', 'executives'];
        foreach ($exportTypes as $type) {
            $res = $this->get(route('reports.export', ['type' => $type]));
            $res->assertStatus(200);
            $res->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        }
    }

    public function test_enforces_multi_tenant_isolation_on_analytics_and_reports()
    {
        $companyB = Company::create(['name' => 'Company B', 'slug' => 'company-b', 'status' => 'Active']);
        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'Company B User',
            'email' => 'userb@companyb.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        Lead::create(['company_id' => $this->company->id, 'project_id' => $this->project->id, 'lead_number' => 'LD-A', 'first_name' => 'Lead', 'last_name' => 'A', 'mobile' => '9000000001', 'email' => 'a@test.com', 'status' => 'New']);
        Lead::create(['company_id' => $companyB->id, 'lead_number' => 'LD-B', 'first_name' => 'Lead', 'last_name' => 'B', 'mobile' => '9000000002', 'email' => 'b@test.com', 'status' => 'New']);

        $metricsA = $this->analyticsService->getExecutiveMetrics($this->company->id);
        $metricsB = $this->analyticsService->getExecutiveMetrics($companyB->id);

        $this->assertEquals(1, $metricsA['totalLeads']);
        $this->assertEquals(1, $metricsB['totalLeads']);

        $reportA = $this->analyticsService->getLeadReportData($this->company->id, []);
        $this->assertEquals(1, $reportA['totalCount']);
        $this->assertEquals('Lead A', $reportA['leads']->first()->name);
    }
}

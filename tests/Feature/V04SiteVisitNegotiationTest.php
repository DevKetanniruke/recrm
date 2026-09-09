<?php

namespace Tests\Feature;

use App\Jobs\CheckExpiredOffersJob;
use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\OfferNegotiationRound;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V04SiteVisitNegotiationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $admin;
    protected User $manager;
    protected User $salesExec;
    protected Project $project;
    protected Unit $unit;
    protected Lead $lead;
    protected OfferService $offerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->offerService = app(OfferService::class);

        // 1. Create company
        $this->company = Company::create([
            'name' => 'Acme Developers',
            'legal_name' => 'Acme Developers Ltd',
            'slug' => 'acme-devs',
            'email' => 'contact@acmedevs.com',
        ]);

        // 2. Create users
        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Admin User',
            'email' => 'admin@acmedevs.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'mobile' => '9900000001',
            'status' => 'Active',
        ]);

        $this->manager = User::create([
            'company_id' => $this->company->id,
            'name' => 'Manager User',
            'email' => 'manager@acmedevs.com',
            'password' => bcrypt('password'),
            'role' => 'sales_manager',
            'mobile' => '9900000002',
            'status' => 'Active',
        ]);

        $this->salesExec = User::create([
            'company_id' => $this->company->id,
            'name' => 'Exec User',
            'email' => 'exec@acmedevs.com',
            'password' => bcrypt('password'),
            'role' => 'sales_executive',
            'mobile' => '9900000003',
            'status' => 'Active',
        ]);

        // 3. Create Project & Unit Structure
        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Acme Towers',
            'code' => 'AT01',
            'location' => 'Downtown',
            'city' => 'Metropolis',
            'status' => 'Active',
        ]);

        $building = Building::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'name' => 'Tower A',
            'code' => 'TA',
        ]);

        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'East Wing',
        ]);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => 1,
            'label' => '1st Floor',
        ]);

        $unitType = UnitType::create([
            'company_id' => $this->company->id,
            'name' => '2BHK Luxury',
            'code' => '2BHK-L',
            'super_builtup_area' => 1200,
            'carpet_area' => 950,
        ]);

        $this->unit = Unit::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'building_id' => $building->id,
            'wing_id' => $wing->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '101',
            'floor_number' => 1,
            'status' => 'Available',
            'inventory_status' => 'Available',
        ]);

        UnitPricing::create([
            'unit_id' => $this->unit->id,
            'base_price' => 1000000.00,
            'parking_charges' => 50000.00,
            'clubhouse_charges' => 20000.00,
            'other_charges' => 10000.00,
            'calculated_total_price' => 1080000.00,
        ]);

        // 4. Create Lead
        $this->lead = Lead::create([
            'company_id' => $this->company->id,
            'lead_number' => 'LD-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'mobile' => '9876543210',
            'email' => 'john.doe@example.com',
            'assigned_user_id' => $this->salesExec->id,
            'project_id' => $this->project->id,
            'status' => 'Hot',
            'stage' => 'Site Visit Scheduled',
        ]);
    }

    #[Test]
    public function it_can_manage_site_visit_logistics_and_gps_checkin()
    {
        $visit = SiteVisit::create([
            'company_id' => $this->company->id,
            'visit_number' => 'SV-1001',
            'lead_id' => $this->lead->id,
            'project_id' => $this->project->id,
            'conducted_by_user_id' => $this->salesExec->id,
            'scheduled_at' => now()->addHours(2),
            'visit_date' => now()->addHours(2),
            'status' => 'Scheduled',
            'pickup_location' => 'Airport Terminal 1',
            'cab_required' => true,
        ]);

        // Dispatch cab
        $response = $this->actingAs($this->salesExec)->post(route('site-visits.dispatch', $visit->id), [
            'driver_name' => 'Michael Knight',
            'driver_phone' => '9112233445',
            'cab_number' => 'KITT-2000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_visits', [
            'id' => $visit->id,
            'driver_name' => 'Michael Knight',
            'driver_phone' => '9112233445',
            'transportation_type' => 'Company Cab',
        ]);

        // GPS Check-in
        $checkinResponse = $this->actingAs($this->salesExec)->post(route('site-visits.check-in', $visit->id), [
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);

        $checkinResponse->assertRedirect();
        $this->assertDatabaseHas('site_visits', [
            'id' => $visit->id,
            'status' => 'Checked In',
            'check_in_lat' => 19.0760,
            'check_in_lng' => 72.8777,
        ]);
    }

    #[Test]
    public function it_fetches_unified_calendar_events()
    {
        SiteVisit::create([
            'company_id' => $this->company->id,
            'visit_number' => 'SV-1002',
            'lead_id' => $this->lead->id,
            'project_id' => $this->project->id,
            'conducted_by_user_id' => $this->salesExec->id,
            'scheduled_at' => now()->addDay(),
            'visit_date' => now()->addDay(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($this->salesExec)->get(route('calendar.events'));

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['id', 'title', 'start', 'type', 'url', 'color']
        ]);
    }

    #[Test]
    public function it_creates_offer_and_routes_approval_based_on_discount()
    {
        // 1. Discount <= 5% -> Auto Approved (Original: 1,080,000, Offered: 1,040,000 -> ~3.7% discount)
        $offer1 = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            1040000.00,
            50000.00,
            'Standard Construction Linked',
            3,
            $this->salesExec,
            'Free modular kitchen'
        );

        $this->assertEquals('Approved', $offer1->status);
        $this->assertEquals('Hold', $this->unit->fresh()->status);

        // Reset unit status
        $this->unit->update(['status' => 'Available']);

        // 2. Discount 8% -> Needs Manager Approval (Original: 1,080,000, Offered: 990,000 -> ~8.3% discount)
        $offer2 = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            990000.00,
            50000.00,
            'Flexi Plan',
            3,
            $this->salesExec
        );

        $this->assertEquals('Pending Manager Approval', $offer2->status);

        // Manager approves offer2
        $this->actingAs($this->manager)->post(route('offers.approve', $offer2->id));
        $this->assertEquals('Approved', $offer2->fresh()->status);

        // Reset unit status
        $this->unit->update(['status' => 'Available']);

        // 3. Discount 15% -> Needs Admin Approval (Original: 1,080,000, Offered: 900,000 -> ~16.6% discount)
        $offer3 = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            900000.00,
            50000.00,
            'Flexi Plan',
            3,
            $this->salesExec
        );

        $this->assertEquals('Pending Admin Approval', $offer3->status);
    }

    #[Test]
    public function it_handles_counter_offer_negotiation_rounds()
    {
        $offer = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            1040000.00,
            50000.00,
            'Standard',
            3,
            $this->salesExec
        );

        // Counter offer round by lead
        $response = $this->actingAs($this->salesExec)->post(route('offers.counter', $offer->id), [
            'offered_by' => 'Buyer',
            'counter_price' => 1000000.00,
            'token_amount' => 50000.00,
            'comments' => 'Customer requested round figure of 10 Lakhs',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('offer_negotiation_rounds', [
            'offer_id' => $offer->id,
            'offered_by' => 'Buyer',
            'proposed_price' => 1000000.00,
        ]);

        $this->assertEquals(1000000.00, $offer->fresh()->offered_price);
    }

    #[Test]
    public function it_releases_expired_unit_locks_via_background_job()
    {
        $offer = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            1040000.00,
            50000.00,
            'Standard',
            3,
            $this->salesExec
        );

        // Manually set lock_expires_at to past
        $offer->update(['unit_lock_expires_at' => now()->subHour()]);
        $this->unit->update(['unit_lock_expires_at' => now()->subHour()]);

        $this->assertEquals('Hold', $this->unit->fresh()->status);

        // Run background job with OfferService dependency
        (new CheckExpiredOffersJob())->handle($this->offerService);

        $this->assertEquals('Expired', $offer->fresh()->status);
        $this->assertEquals('Available', $this->unit->fresh()->status);
    }

    #[Test]
    public function it_converts_approved_offer_to_booking()
    {
        $offer = $this->offerService->createOffer(
            $this->lead,
            $this->unit,
            1040000.00,
            50000.00,
            'Standard',
            3,
            $this->salesExec
        );

        $response = $this->actingAs($this->salesExec)->post(route('offers.convert-booking', $offer->id));

        $response->assertRedirect();
        $this->assertEquals('Converted to Booking', $offer->fresh()->status);
        $this->assertEquals('Booked', $this->unit->fresh()->status);
        $this->assertDatabaseHas('bookings', [
            'unit_id' => $this->unit->id,
            'agreed_price' => 1040000.00,
        ]);
    }
}

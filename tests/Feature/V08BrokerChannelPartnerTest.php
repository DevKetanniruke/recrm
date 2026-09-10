<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Building;
use App\Models\ChannelPartner;
use App\Models\ChannelPartnerContact;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\CommissionStructure;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\LeadAttribution;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\Broker\CommissionEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V08BrokerChannelPartnerTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company1;
    protected Company $company2;
    protected User $admin1;
    protected User $agent1;
    protected User $agent2;
    protected Project $project;
    protected Unit $unit;
    protected Customer $customer;
    protected Booking $booking;
    protected CommissionEngineService $commissionEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commissionEngine = app(CommissionEngineService::class);

        // Setup Company 1
        $this->company1 = Company::create([
            'name' => 'Brokerage Apex Realty',
            'legal_name' => 'Brokerage Apex Realty LLC',
            'slug' => 'brokerage-apex-realty',
        ]);

        $this->admin1 = User::create([
            'company_id' => $this->company1->id,
            'name' => 'Brokerage Admin',
            'email' => 'admin@brokerageapex.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'mobile' => '9666655551',
            'status' => 'Active',
        ]);

        $this->agent1 = User::create([
            'company_id' => $this->company1->id,
            'name' => 'Sales Agent One',
            'email' => 'agent1@brokerageapex.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9666655552',
            'status' => 'Active',
        ]);

        // Setup Company 2 (Tenancy Isolation)
        $this->company2 = Company::create([
            'name' => 'Rival Brokerage',
            'legal_name' => 'Rival Brokerage Inc',
            'slug' => 'rival-brokerage',
        ]);

        $this->agent2 = User::create([
            'company_id' => $this->company2->id,
            'name' => 'Rival Agent',
            'email' => 'agent@rivalbrokerage.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9666655553',
            'status' => 'Active',
        ]);

        // Project and Inventory
        $this->project = Project::create([
            'company_id' => $this->company1->id,
            'name' => 'Grand Horizon Towers',
            'code' => 'GHT01',
            'city' => 'Metropolis',
        ]);

        $building = Building::create([
            'company_id' => $this->company1->id,
            'project_id' => $this->project->id,
            'name' => 'Tower A',
        ]);

        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'West Wing',
        ]);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => 8,
        ]);

        $unitType = UnitType::create([
            'company_id' => $this->company1->id,
            'name' => '3BHK Premium',
            'carpet_area' => 1800,
        ]);

        $this->unit = Unit::create([
            'company_id' => $this->company1->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '802',
            'status' => 'Booked',
        ]);

        UnitPricing::create([
            'unit_id' => $this->unit->id,
            'base_price' => 15000000.00,
            'final_price' => 15000000.00,
        ]);

        // Customer & Booking
        $lead = Lead::create([
            'company_id' => $this->company1->id,
            'first_name' => 'David',
            'last_name' => 'Warner',
            'email' => 'david.warner@example.com',
            'mobile' => '9123443210',
            'assigned_to' => $this->agent1->id,
            'status' => 'Converted',
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company1->id,
            'lead_id' => $lead->id,
            'customer_number' => 'CUST-2026-00888',
            'first_name' => 'David',
            'last_name' => 'Warner',
            'email' => 'david.warner@example.com',
            'mobile' => '9123443210',
            'status' => 'Active',
        ]);

        $this->booking = Booking::create([
            'company_id' => $this->company1->id,
            'customer_id' => $this->customer->id,
            'unit_id' => $this->unit->id,
            'sales_agent_id' => $this->agent1->id,
            'booking_number' => 'BKG-2026-00888',
            'booking_date' => now()->toDateString(),
            'agreed_price' => 15000000.00,
            'total_amount' => 15000000.00,
            'status' => 'Confirmed',
        ]);
    }

    #[Test]
    public function it_onboards_channel_partner_and_links_contacts(): void
    {
        $response = $this->actingAs($this->admin1)
            ->post(route('brokers.partners.store'), [
                'company_name' => 'Premier Property Advisors',
                'contact_person' => 'Michael Scott',
                'mobile' => '9888811112',
                'email' => 'michael@premieradvisors.com',
                'rera_registration_number' => 'A518000778899',
                'gst_number' => '27AAACP9988Z1',
                'pan_number' => 'ABCDE9999F',
                'city' => 'Metropolis',
            ]);

        $response->assertRedirect(route('brokers.partners.index'));

        $partner = ChannelPartner::where('company_name', 'Premier Property Advisors')->first();
        $this->assertNotNull($partner);
        $this->assertStringStartsWith('CP-', $partner->partner_code);
        $this->assertEquals('Active', $partner->status);

        // Check primary contact linkage
        $this->assertDatabaseHas('channel_partner_contacts', [
            'channel_partner_id' => $partner->id,
            'name' => 'Michael Scott',
            'is_primary' => true,
        ]);
    }

    #[Test]
    public function it_tracks_lead_attribution_history(): void
    {
        $partner1 = ChannelPartner::create([
            'company_id' => $this->company1->id,
            'partner_code' => 'CP-2026-00001',
            'company_name' => 'First Realty',
            'contact_person' => 'John Doe',
            'mobile' => '9999911111',
        ]);

        $partner2 = ChannelPartner::create([
            'company_id' => $this->company1->id,
            'partner_code' => 'CP-2026-00002',
            'company_name' => 'Second Realty',
            'contact_person' => 'Jane Doe',
            'mobile' => '9999922222',
        ]);

        $lead = Lead::create([
            'company_id' => $this->company1->id,
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'mobile' => '9555544444',
            'channel_partner_id' => $partner1->id,
        ]);

        // Re-attribute lead to partner 2
        $lead->update(['channel_partner_id' => $partner2->id]);
        LeadAttribution::create([
            'company_id' => $this->company1->id,
            'lead_id' => $lead->id,
            'previous_channel_partner_id' => $partner1->id,
            'new_channel_partner_id' => $partner2->id,
            'changed_by_user_id' => $this->admin1->id,
            'reason' => 'Client transferred to partner 2',
            'attributed_at' => now(),
        ]);

        $this->assertDatabaseHas('lead_attributions', [
            'lead_id' => $lead->id,
            'previous_channel_partner_id' => $partner1->id,
            'new_channel_partner_id' => $partner2->id,
        ]);
    }

    #[Test]
    public function it_calculates_commission_approves_and_records_payout(): void
    {
        $partner = ChannelPartner::create([
            'company_id' => $this->company1->id,
            'partner_code' => 'CP-2026-00099',
            'company_name' => 'Metropolis Estates',
            'contact_person' => 'Bruce Wayne',
            'mobile' => '9888800000',
            'status' => 'Active',
        ]);

        $structure = CommissionStructure::create([
            'company_id' => $this->company1->id,
            'name' => 'Standard 3% Brokerage',
            'calculation_type' => 'percentage',
            'rate' => 3.00,
            'is_active' => true,
        ]);

        $this->booking->update(['channel_partner_id' => $partner->id]);
        $this->booking->refresh();

        // Calculate commission -> 3% of ₹15,000,000 = ₹450,000
        $commission = $this->commissionEngine->calculateCommissionForBooking($this->booking);

        $this->assertNotNull($commission);
        $this->assertEquals(450000.00, $commission->calculated_commission_amount);
        $this->assertEquals('Pending', $commission->status);

        // Approve commission
        $this->commissionEngine->approveCommission($commission, 450000.00, $this->admin1);
        $commission->refresh();
        $this->assertEquals('Approved', $commission->status);
        $this->assertEquals(450000.00, $commission->balance_amount);

        // Record payout of ₹200,000
        $payout = $this->commissionEngine->recordPayout($commission, [
            'amount' => 200000.00,
            'payment_date' => now()->toDateString(),
            'payment_mode' => 'NEFT',
            'payment_reference' => 'UTR10203040',
        ], $this->admin1);

        $commission->refresh();
        $this->assertNotNull($payout->payout_number);
        $this->assertStringStartsWith('PAYOUT-', $payout->payout_number);
        $this->assertEquals(200000.00, $commission->paid_amount);
        $this->assertEquals(250000.00, $commission->balance_amount);
        $this->assertEquals('Payable', $commission->status);
    }

    #[Test]
    public function it_generates_partner_performance_reports(): void
    {
        $partner = ChannelPartner::create([
            'company_id' => $this->company1->id,
            'partner_code' => 'CP-2026-00055',
            'company_name' => 'Urban Living Brokers',
            'contact_person' => 'Clark Kent',
            'mobile' => '9777755555',
            'status' => 'Active',
        ]);

        Lead::create([
            'company_id' => $this->company1->id,
            'first_name' => 'Oliver',
            'last_name' => 'Queen',
            'mobile' => '9444433333',
            'channel_partner_id' => $partner->id,
        ]);

        $response = $this->actingAs($this->admin1)
            ->get(route('brokers.reports.index', ['channel_partner_id' => $partner->id]));

        $response->assertStatus(200)
            ->assertSee('Urban Living Brokers')
            ->assertSee('CP-2026-00055');
    }

    #[Test]
    public function it_enforces_multi_tenant_isolation_on_channel_partners(): void
    {
        $partner1 = ChannelPartner::create([
            'company_id' => $this->company1->id,
            'partner_code' => 'CP-2026-00010',
            'company_name' => 'Company One Brokers',
            'contact_person' => 'Agent One',
            'mobile' => '9111111111',
        ]);

        // User from Company 2 should receive 404 when attempting to access Company 1 partner
        $response = $this->actingAs($this->agent2)
            ->get(route('brokers.partners.show', $partner1));

        $response->assertStatus(404);
    }
}

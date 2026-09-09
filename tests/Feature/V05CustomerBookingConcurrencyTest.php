<?php

namespace Tests\Feature;

use App\Exceptions\UnitAlreadyBookedException;
use App\Models\Building;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V05CustomerBookingConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company1;
    protected Company $company2;
    protected User $agent1;
    protected User $agent2;
    protected Project $project;
    protected Unit $unit1;
    protected Unit $unit2;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);
        Storage::fake('local');

        // Company 1
        $this->company1 = Company::create([
            'name' => 'Apex Real Estate',
            'legal_name' => 'Apex Real Estate LLC',
            'slug' => 'apex-realty',
        ]);

        $this->agent1 = User::create([
            'company_id' => $this->company1->id,
            'name' => 'Agent One',
            'email' => 'agent1@apex.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9900112233',
            'status' => 'Active',
        ]);

        // Company 2
        $this->company2 = Company::create([
            'name' => 'Beacon Properties',
            'legal_name' => 'Beacon Properties LLC',
            'slug' => 'beacon-props',
        ]);

        $this->agent2 = User::create([
            'company_id' => $this->company2->id,
            'name' => 'Agent Two',
            'email' => 'agent2@beacon.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9900445566',
            'status' => 'Active',
        ]);

        // Project structure for Company 1
        $this->project = Project::create([
            'company_id' => $this->company1->id,
            'name' => 'Apex Horizon',
            'code' => 'AH01',
            'city' => 'Metropolis',
        ]);

        $building = Building::create([
            'company_id' => $this->company1->id,
            'project_id' => $this->project->id,
            'name' => 'Tower 1',
        ]);

        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'Main Wing',
        ]);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => 1,
            'label' => 'Floor 1',
        ]);

        $unitType = UnitType::create([
            'company_id' => $this->company1->id,
            'name' => '3 BHK Deluxe',
            'code' => '3BHK',
        ]);

        $this->unit1 = Unit::create([
            'company_id' => $this->company1->id,
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
            'unit_id' => $this->unit1->id,
            'base_price' => 2000000.00,
            'calculated_total_price' => 2100000.00,
        ]);
        $this->unit1->update(['total_price' => 2100000.00]);

        $this->unit2 = Unit::create([
            'company_id' => $this->company1->id,
            'project_id' => $this->project->id,
            'building_id' => $building->id,
            'wing_id' => $wing->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '102',
            'floor_number' => 1,
            'status' => 'Available',
            'inventory_status' => 'Available',
        ]);

        UnitPricing::create([
            'unit_id' => $this->unit2->id,
            'base_price' => 2000000.00,
            'calculated_total_price' => 2100000.00,
        ]);
        $this->unit2->update(['total_price' => 2100000.00]);
    }

    #[Test]
    public function it_creates_customer_profile_with_co_applicants_and_secure_documents()
    {
        $response = $this->actingAs($this->agent1)->post(route('customers.store'), [
            'first_name' => 'Arthur',
            'last_name' => 'Dent',
            'mobile' => '9876543210',
            'email' => 'arthur.dent@example.com',
            'PAN' => 'ADENT1234F',
            'occupation' => 'Architect',
            'co_applicants' => [
                [
                    'customer_name' => 'Ford Prefect',
                    'relationship' => 'Business Partner',
                    'mobile' => '9876543211',
                    'email' => 'ford@example.com',
                    'ownership_percentage' => 50.00,
                    'pan_number' => 'FPREF5678G',
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'first_name' => 'Arthur',
            'email' => 'arthur.dent@example.com',
        ]);

        $customer = Customer::where('email', 'arthur.dent@example.com')->first();
        $this->assertNotNull($customer->customer_number);

        $this->assertDatabaseHas('co_applicants', [
            'customer_id' => $customer->id,
            'customer_name' => 'Ford Prefect',
            'ownership_percentage' => 50.00,
        ]);

        // Test Uploading Document to Secure Vault
        $file = UploadedFile::fake()->create('passport.pdf', 500, 'application/pdf');

        $docResponse = $this->actingAs($this->agent1)->post(route('customer-documents.store', $customer->id), [
            'document_type' => 'Identity Proof',
            'document_file' => $file,
            'verification_notes' => 'Valid Passport document',
        ]);

        $docResponse->assertRedirect();
        $this->assertDatabaseHas('customer_documents', [
            'customer_id' => $customer->id,
            'document_type' => 'Identity Proof',
            'file_name' => 'passport.pdf',
        ]);

        $doc = CustomerDocument::where('customer_id', $customer->id)->first();
        Storage::disk('local')->assertExists($doc->file_path);
    }

    #[Test]
    public function it_creates_booking_transactionally_and_locks_unit()
    {
        $booking = $this->bookingService->createBooking([
            'unit_id' => $this->unit1->id,
            'first_name' => 'Zaphod',
            'last_name' => 'Beeblebrox',
            'mobile' => '9112233445',
            'email' => 'zaphod@galaxy.com',
            'quoted_price' => 2100000.00,
            'agreed_price' => 2000000.00,
            'discount_amount' => 100000.00,
            'tax_amount' => 100000.00,
            'total_amount' => 2100000.00,
            'booking_amount_paid' => 100000.00,
            'payment_mode' => 'Cheque',
            'payment_reference' => 'CHQ-998822',
        ], $this->agent1);

        $this->assertEquals('Confirmed', $booking->status);
        $this->assertNotNull($booking->booking_number);
        $this->assertEquals('Booked', $this->unit1->fresh()->status);

        $this->assertDatabaseHas('unit_status_histories', [
            'unit_id' => $this->unit1->id,
            'previous_status' => 'Available',
            'new_status' => 'Booked',
        ]);
    }

    #[Test]
    public function it_prevents_simultaneous_double_booking_of_same_unit()
    {
        // First booking succeeds
        $this->bookingService->createBooking([
            'unit_id' => $this->unit1->id,
            'first_name' => 'Buyer',
            'last_name' => 'One',
            'mobile' => '9111111111',
            'email' => 'buyer1@example.com',
            'agreed_price' => 2100000.00,
            'total_amount' => 2100000.00,
            'booking_amount_paid' => 50000.00,
        ], $this->agent1);

        $this->assertEquals('Booked', $this->unit1->fresh()->status);

        // Second booking attempt on same unit must throw UnitAlreadyBookedException
        $this->expectException(UnitAlreadyBookedException::class);

        $this->bookingService->createBooking([
            'unit_id' => $this->unit1->id,
            'first_name' => 'Buyer',
            'last_name' => 'Two',
            'mobile' => '9222222222',
            'email' => 'buyer2@example.com',
            'agreed_price' => 2100000.00,
            'total_amount' => 2100000.00,
            'booking_amount_paid' => 50000.00,
        ], $this->agent1);
    }

    #[Test]
    public function it_cancels_booking_and_releases_unit_back_to_available()
    {
        $booking = $this->bookingService->createBooking([
            'unit_id' => $this->unit1->id,
            'first_name' => 'Trillian',
            'last_name' => 'Astra',
            'mobile' => '9333333333',
            'email' => 'trillian@example.com',
            'agreed_price' => 2100000.00,
            'total_amount' => 2100000.00,
            'booking_amount_paid' => 100000.00,
        ], $this->agent1);

        $this->assertEquals('Booked', $this->unit1->fresh()->status);

        // Cancel booking
        $cancelledBooking = $this->bookingService->cancelBooking(
            $booking,
            $this->agent1,
            'Customer requested cancellation due to relocation',
            50000.00
        );

        $this->assertEquals('Cancelled', $cancelledBooking->status);
        $this->assertEquals('Customer requested cancellation due to relocation', $cancelledBooking->cancellation_reason);
        $this->assertEquals('Available', $this->unit1->fresh()->status);

        $this->assertDatabaseHas('unit_status_histories', [
            'unit_id' => $this->unit1->id,
            'previous_status' => 'Booked',
            'new_status' => 'Available',
        ]);
    }

    #[Test]
    public function it_enforces_tenancy_and_policy_isolation_for_customers()
    {
        $customerComp1 = Customer::create([
            'company_id' => $this->company1->id,
            'first_name' => 'Company1',
            'last_name' => 'Customer',
            'email' => 'c1@example.com',
        ]);

        // Agent 2 (Company 2) cannot view Customer from Company 1 (scoped via BelongsToCompany)
        $response = $this->actingAs($this->agent2)->get(route('customers.show', $customerComp1->id));
        $response->assertNotFound();
    }
}

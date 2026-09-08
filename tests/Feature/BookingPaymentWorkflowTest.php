<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Models\Wing;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_reserves_unit_status_and_generates_schedules(): void
    {
        $company = Company::create(['name' => 'Apex Builders', 'slug' => 'apex-builders']);
        $user = User::create(['company_id' => $company->id, 'name' => 'Agent', 'email' => 'agent@apex.com', 'password' => bcrypt('password'), 'role' => 'sales_agent']);

        $project = Project::create(['company_id' => $company->id, 'name' => 'Apex Residency']);
        $building = Building::create(['company_id' => $company->id, 'project_id' => $project->id, 'name' => 'Tower A', 'total_floors' => 5]);
        $wing = Wing::create(['company_id' => $company->id, 'building_id' => $building->id, 'name' => 'East Wing']);
        $floor = Floor::create(['company_id' => $company->id, 'wing_id' => $wing->id, 'floor_number' => 1]);

        $unit = Unit::create([
            'company_id' => $company->id,
            'floor_id' => $floor->id,
            'unit_number' => '101',
            'unit_type' => '2BHK',
            'facing' => 'East',
            'carpet_area_sqft' => 1000,
            'super_builtup_area_sqft' => 1200,
            'base_rate_per_sqft' => 100,
            'total_price' => 120000,
            'status' => 'Available',
        ]);

        $bookingService = app(BookingService::class);

        $booking = $bookingService->createBooking([
            'unit_id' => $unit->id,
            'customer_first_name' => 'Robert',
            'customer_last_name' => 'Downey',
            'customer_phone' => '+1555998877',
            'agreed_price' => 120000,
            'booking_amount_paid' => 12000,
            'booking_date' => now()->toDateString(),
        ], $user->id);

        // Assert unit status updated to Booked
        $unit->refresh();
        $this->assertEquals('Booked', $unit->status);

        // Assert booking created with 4 milestone schedules
        $this->assertEquals(4, $booking->paymentSchedules()->count());
        $this->assertEquals('Confirmed', $booking->status);

        // Record a Payment
        $paymentService = app(PaymentService::class);
        $schedule = $booking->paymentSchedules()->first();

        $payment = $paymentService->recordPayment([
            'booking_id' => $booking->id,
            'payment_schedule_id' => $schedule->id,
            'amount_paid' => $schedule->amount_due,
            'payment_method' => 'Bank Transfer',
            'payment_date' => now()->toDateString(),
        ], $user->id);

        $schedule->refresh();
        $this->assertEquals('Paid', $schedule->status);
        $this->assertEquals('Verified', $payment->status);
    }
}

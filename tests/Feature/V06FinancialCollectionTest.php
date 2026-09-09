<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Building;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentDemandNotice;
use App\Models\PaymentPlanTemplate;
use App\Models\PaymentRefund;
use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\FinancialService;
use App\Services\PaymentPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V06FinancialCollectionTest extends TestCase
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
    protected PaymentPlanService $planService;
    protected FinancialService $financialService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planService = app(PaymentPlanService::class);
        $this->financialService = app(FinancialService::class);

        // Setup Company 1 & Users
        $this->company1 = Company::create([
            'name' => 'Apex Financial Realty',
            'legal_name' => 'Apex Financial Realty LLC',
            'slug' => 'apex-fin-realty',
        ]);

        $this->admin1 = User::create([
            'company_id' => $this->company1->id,
            'name' => 'Finance Admin',
            'email' => 'finance@apex.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'mobile' => '9888877771',
            'status' => 'Active',
        ]);

        $this->agent1 = User::create([
            'company_id' => $this->company1->id,
            'name' => 'Sales Agent One',
            'email' => 'agent1@apex.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9888877772',
            'status' => 'Active',
        ]);

        // Setup Company 2 & User (Tenancy Isolation test)
        $this->company2 = Company::create([
            'name' => 'Rival Realty',
            'legal_name' => 'Rival Realty Inc',
            'slug' => 'rival-realty',
        ]);

        $this->agent2 = User::create([
            'company_id' => $this->company2->id,
            'name' => 'Rival Agent',
            'email' => 'agent@rival.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9888877773',
            'status' => 'Active',
        ]);

        // Inventory setup for Company 1
        $this->project = Project::create([
            'company_id' => $this->company1->id,
            'name' => 'Grand Residency',
            'code' => 'GR01',
            'city' => 'Metropolis',
        ]);

        $building = Building::create([
            'company_id' => $this->company1->id,
            'project_id' => $this->project->id,
            'name' => 'Block A',
        ]);

        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'East Wing',
        ]);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => 5,
        ]);

        $unitType = UnitType::create([
            'company_id' => $this->company1->id,
            'name' => '3 BHK Luxury',
            'carpet_area' => 1500,
        ]);

        $this->unit = Unit::create([
            'company_id' => $this->company1->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '501',
            'status' => 'Booked',
        ]);

        UnitPricing::create([
            'unit_id' => $this->unit->id,
            'base_price' => 10000000.00,
            'final_price' => 10000000.00,
        ]);

        // Customer & Booking
        $lead = Lead::create([
            'company_id' => $this->company1->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '9123456789',
            'assigned_to' => $this->agent1->id,
            'status' => 'Converted',
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company1->id,
            'lead_id' => $lead->id,
            'customer_number' => 'CUST-2026-00001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'mobile' => '9123456789',
            'pan_card' => 'ABCDE1234F',
            'status' => 'Active',
        ]);

        $this->booking = Booking::create([
            'company_id' => $this->company1->id,
            'customer_id' => $this->customer->id,
            'unit_id' => $this->unit->id,
            'sales_agent_id' => $this->agent1->id,
            'booking_number' => 'BKG-2026-00001',
            'booking_date' => now()->toDateString(),
            'agreed_price' => 10000000.00,
            'total_amount' => 10000000.00,
            'status' => 'Confirmed',
        ]);
    }

    #[Test]
    public function it_creates_payment_plan_template_and_generates_schedules(): void
    {
        $template = PaymentPlanTemplate::create([
            'company_id' => $this->company1->id,
            'name' => 'Standard Construction Linked Plan',
            'description' => '20-30-50 Construction milestone plan',
            'is_active' => true,
            'milestones_json' => [
                ['milestone_name' => 'Booking Amount', 'milestone_code' => 'BOOKING', 'percentage' => 20, 'due_days_from_booking' => 0],
                ['milestone_name' => 'Plinth Level', 'milestone_code' => 'PLINTH', 'percentage' => 30, 'due_days_from_booking' => 60],
                ['milestone_name' => 'Possession', 'milestone_code' => 'POSSESSION', 'percentage' => 50, 'due_days_from_booking' => 180],
            ],
        ]);

        $schedules = $this->planService->generateSchedules($this->booking, $template);

        $this->assertCount(3, $schedules);
        $this->assertEquals(2000000.00, $schedules[0]->amount_due);
        $this->assertEquals(3000000.00, $schedules[1]->amount_due);
        $this->assertEquals(5000000.00, $schedules[2]->amount_due);
        $this->assertEquals('BOOKING', $schedules[0]->milestone_code);
        $this->assertEquals('Pending', $schedules[0]->status);
    }

    #[Test]
    public function it_records_payment_and_automatically_allocates_via_fifo(): void
    {
        // Create 2 schedules: Schedule 1 = $200k, Schedule 2 = $300k
        $s1 = PaymentSchedule::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'milestone_name' => 'Booking Amount',
            'milestone_code' => 'BOOKING',
            'percentage' => 20,
            'amount_due' => 200000.00,
            'amount_paid' => 0,
            'outstanding_amount' => 200000.00,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'Pending',
        ]);

        $s2 = PaymentSchedule::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'milestone_name' => 'Plinth Level',
            'milestone_code' => 'PLINTH',
            'percentage' => 30,
            'amount_due' => 300000.00,
            'amount_paid' => 0,
            'outstanding_amount' => 300000.00,
            'due_date' => now()->addDays(60)->toDateString(),
            'status' => 'Pending',
        ]);

        // Record $350k payment -> should cover S1 ($200k) completely, and S2 ($150k) partially
        $payment = $this->financialService->recordPayment([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->admin1->id,
            'amount_paid' => 350000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank Transfer',
            'payment_mode' => 'NEFT',
            'bank_cheque_number' => 'TXN99887766',
        ], $this->admin1);

        $this->assertNotNull($payment->payment_number);
        $this->assertStringStartsWith('PAY-', $payment->payment_number);

        // Check Schedule 1 state
        $s1->refresh();
        $this->assertEquals(200000.00, $s1->amount_paid);
        $this->assertEquals(0, $s1->outstanding_amount);
        $this->assertEquals('Paid', $s1->status);

        // Check Schedule 2 state
        $s2->refresh();
        $this->assertEquals(150000.00, $s2->amount_paid);
        $this->assertEquals(150000.00, $s2->outstanding_amount);
        $this->assertEquals('Partially Paid', $s2->status);

        // Allocations verify
        $this->assertCount(2, $payment->allocations);
    }

    #[Test]
    public function it_handles_payment_reversal_and_refund_without_row_deletion(): void
    {
        $s1 = PaymentSchedule::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'milestone_name' => 'Booking Amount',
            'milestone_code' => 'BOOKING',
            'amount_due' => 200000.00,
            'amount_paid' => 0,
            'outstanding_amount' => 200000.00,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'Pending',
        ]);

        $payment = $this->financialService->recordPayment([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->admin1->id,
            'amount_paid' => 200000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cheque',
            'payment_mode' => 'Cheque',
            'bank_cheque_number' => 'CHQ102030',
        ], $this->admin1);

        $s1->refresh();
        $this->assertEquals('Paid', $s1->status);

        // Reverse payment due to Cheque Bounced
        $refund = $this->financialService->reverseOrRefundPayment(
            $payment,
            $this->admin1,
            'Cheque bounced by bank'
        );

        $payment->refresh();
        $s1->refresh();

        // Zero deletion check
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'is_reversed' => true]);
        $this->assertEquals('Cheque bounced by bank', $payment->reversal_reason);

        // Refund record check
        $this->assertNotNull($refund->refund_number);
        $this->assertStringStartsWith('RFD-', $refund->refund_number);
        $this->assertEquals(200000.00, $refund->refund_amount);

        // Schedule rolled back
        $this->assertEquals(0, $s1->amount_paid);
        $this->assertEquals(200000.00, $s1->outstanding_amount);
        $this->assertEquals('Pending', $s1->status);
    }

    #[Test]
    public function it_identifies_overdue_payments_and_creates_demand_notices(): void
    {
        $overdueSchedule = PaymentSchedule::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'milestone_name' => 'Plinth Installment',
            'milestone_code' => 'PLINTH',
            'amount_due' => 500000.00,
            'amount_paid' => 0,
            'outstanding_amount' => 500000.00,
            'due_date' => now()->subDays(15)->toDateString(),
            'status' => 'Pending',
        ]);

        // Run overdue check Artisan command logic
        $this->artisan('crm:check-overdue')
            ->assertExitCode(0);

        $overdueSchedule->refresh();
        $this->assertEquals('Overdue', $overdueSchedule->status);
        $this->assertEquals(15, $overdueSchedule->overdue_days);

        // Issue Demand Notice
        $demand = PaymentDemandNotice::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'payment_schedule_id' => $overdueSchedule->id,
            'demand_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'demand_amount' => 500000.00,
            'penalty_amount' => 5000.00,
            'status' => 'Sent',
            'notes' => 'Urgent payment demand for overdue installment',
        ]);

        $this->assertDatabaseHas('payment_demand_notices', [
            'id' => $demand->id,
            'demand_number' => $demand->demand_number,
            'status' => 'Sent',
        ]);

        // Check PDF route returns HTML/PDF template
        $response = $this->actingAs($this->admin1)
            ->get(route('demands.pdf', $demand));

        $response->assertStatus(200)
            ->assertSee('DEMAND NOTICE')
            ->assertSee('500,000.00');
    }

    #[Test]
    public function it_enforces_multi_tenant_isolation_on_financial_records(): void
    {
        $payment = Payment::create([
            'company_id' => $this->company1->id,
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'payment_number' => 'PAY-2026-99999',
            'amount_paid' => 100000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'UPI',
        ]);

        // User from Company 2 should be forbidden from accessing Company 1 payment view via BelongsToCompany global scope
        $response = $this->actingAs($this->agent2)
            ->get(route('payments.show', $payment));

        $response->assertStatus(404);
    }
}

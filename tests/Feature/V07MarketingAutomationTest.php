<?php

namespace Tests\Feature;

use App\Contracts\CommunicationProviderInterface;
use App\Contracts\CommunicationResult;
use App\Models\AutomationExecution;
use App\Models\AutomationRule;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Campaign;
use App\Models\CommunicationLog;
use App\Models\CommunicationTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerCommunicationPreference;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\Automation\AutomationEngineService;
use App\Services\Communication\CommunicationChannelManager;
use App\Services\Communication\CommunicationDispatcherService;
use App\Services\Communication\TemplateHydrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class V07MarketingAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $admin;
    protected User $agent;
    protected Project $project;
    protected Unit $unit;
    protected Customer $customer;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Apex Automation Realty',
            'legal_name' => 'Apex Automation Realty Inc',
            'slug' => 'apex-auto-realty',
        ]);

        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Marketing Director',
            'email' => 'marketing@apex.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'mobile' => '9777766661',
            'status' => 'Active',
        ]);

        $this->agent = User::create([
            'company_id' => $this->company->id,
            'name' => 'Sales Exec John',
            'email' => 'exec@apex.com',
            'password' => bcrypt('password'),
            'role' => 'sales_agent',
            'mobile' => '9777766662',
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Skyline Heights',
            'code' => 'SH01',
            'city' => 'Metropolis',
        ]);

        $building = Building::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'name' => 'Tower A',
        ]);

        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'North Wing',
        ]);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => 10,
        ]);

        $unitType = UnitType::create([
            'company_id' => $this->company->id,
            'name' => 'Penthouse 4BHK',
            'carpet_area' => 2500,
        ]);

        $this->unit = Unit::create([
            'company_id' => $this->company->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '1001',
            'status' => 'Booked',
        ]);

        UnitPricing::create([
            'unit_id' => $this->unit->id,
            'base_price' => 25000000.00,
            'final_price' => 25000000.00,
        ]);

        $lead = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@example.com',
            'mobile' => '9876543210',
            'assigned_to' => $this->agent->id,
            'status' => 'Converted',
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'customer_number' => 'CUST-2026-00777',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@example.com',
            'mobile' => '9876543210',
            'status' => 'Active',
        ]);

        $this->booking = Booking::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'unit_id' => $this->unit->id,
            'sales_agent_id' => $this->agent->id,
            'booking_number' => 'BKG-2026-00777',
            'booking_date' => now()->toDateString(),
            'agreed_price' => 25000000.00,
            'total_amount' => 25000000.00,
            'status' => 'Confirmed',
        ]);
    }

    #[Test]
    public function it_hydrates_template_placeholders_with_context_entities(): void
    {
        $hydrationService = app(TemplateHydrationService::class);

        $template = "Dear {{customer_name}}, your booking {{booking_number}} for unit {{unit_number}} in {{project_name}} has been confirmed by {{sales_executive}}!";
        $hydrated = $hydrationService->hydrate($template, [
            'booking' => $this->booking,
        ]);

        $this->assertEquals(
            "Dear Alice Smith, your booking BKG-2026-00777 for unit 1001 in Skyline Heights has been confirmed by Sales Exec John!",
            $hydrated
        );
    }

    #[Test]
    public function it_resolves_providers_dynamically_and_dispatches_messages(): void
    {
        $manager = app(CommunicationChannelManager::class);
        $emailProvider = $manager->getProvider('email');
        $whatsappProvider = $manager->getProvider('whatsapp');

        $this->assertInstanceOf(CommunicationProviderInterface::class, $emailProvider);
        $this->assertEquals('email', $emailProvider->getSupportedChannel());
        $this->assertEquals('whatsapp', $whatsappProvider->getSupportedChannel());

        $dispatcher = app(CommunicationDispatcherService::class);
        $log = $dispatcher->dispatchMessage([
            'company_id' => $this->company->id,
            'channel' => 'email',
            'recipient' => 'alice.smith@example.com',
            'subject' => 'Welcome to Skyline Heights',
            'body' => 'Dear {{customer_name}}, thank you for your interest.',
            'context' => ['customer_name' => 'Alice Smith'],
            'is_transactional' => true,
        ]);

        $this->assertEquals('Sent', $log->status);
        $this->assertNotNull($log->provider_reference);
        $this->assertDatabaseHas('communication_logs', [
            'id' => $log->id,
            'recipient' => 'alice.smith@example.com',
            'status' => 'Sent',
        ]);
    }

    #[Test]
    public function it_enforces_marketing_opt_out_while_allowing_transactional_messages(): void
    {
        // Opt-out recipient from marketing
        CustomerCommunicationPreference::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'recipient' => 'alice.smith@example.com',
            'channel' => 'email',
            'opt_in_marketing' => false,
            'opt_out_reason' => 'User requested unsubscribed link',
            'opted_out_at' => now(),
        ]);

        $dispatcher = app(CommunicationDispatcherService::class);

        // 1. Dispatch Marketing Message -> Should be blocked & status OptedOut
        $marketingLog = $dispatcher->dispatchMessage([
            'company_id' => $this->company->id,
            'channel' => 'email',
            'recipient' => 'alice.smith@example.com',
            'subject' => 'Special Festival Discount Offer!',
            'body' => 'Get 5% off on penthouse units!',
            'is_transactional' => false, // Marketing
        ]);

        $this->assertEquals('OptedOut', $marketingLog->status);
        $this->assertStringContainsString('opted out', $marketingLog->failure_reason);

        // 2. Dispatch Transactional Message -> Should bypass opt-out & status Sent
        $transactionalLog = $dispatcher->dispatchMessage([
            'company_id' => $this->company->id,
            'channel' => 'email',
            'recipient' => 'alice.smith@example.com',
            'subject' => 'Official Booking Confirmation Letter',
            'body' => 'Your booking #BKG-2026-00777 is confirmed.',
            'is_transactional' => true, // Transactional
        ]);

        $this->assertEquals('Sent', $transactionalLog->status);
    }

    #[Test]
    public function it_executes_automation_rule_on_lead_creation_event(): void
    {
        $template = CommunicationTemplate::create([
            'company_id' => $this->company->id,
            'name' => 'Auto Welcome Email',
            'channel' => 'email',
            'subject' => 'Welcome {{customer_name}}!',
            'body' => 'Hi {{customer_name}}, thanks for visiting our project {{project_name}}.',
            'is_transactional' => true,
            'status' => 'Active',
        ]);

        $rule = AutomationRule::create([
            'company_id' => $this->company->id,
            'name' => 'Welcome Email Rule',
            'trigger_event' => 'lead.created',
            'communication_template_id' => $template->id,
            'is_active' => true,
        ]);

        $newLead = Lead::create([
            'company_id' => $this->company->id,
            'first_name' => 'Bob',
            'last_name' => 'Marley',
            'email' => 'bob.marley@example.com',
            'mobile' => '9111122223',
            'status' => 'New',
        ]);

        $engine = app(AutomationEngineService::class);
        $triggeredCount = $engine->trigger('lead.created', $newLead);

        $this->assertEquals(1, $triggeredCount);
        $this->assertDatabaseHas('automation_executions', [
            'automation_rule_id' => $rule->id,
            'entity_id' => $newLead->id,
            'status' => 'Success',
        ]);
        $this->assertDatabaseHas('communication_logs', [
            'recipient' => 'bob.marley@example.com',
            'status' => 'Sent',
        ]);
    }

    #[Test]
    public function it_runs_scheduled_campaign_and_automation_reminder_commands(): void
    {
        $template = CommunicationTemplate::create([
            'company_id' => $this->company->id,
            'name' => 'Promo Campaign Template',
            'channel' => 'sms',
            'subject' => 'Promo',
            'body' => 'Visit Skyline Heights today!',
            'is_transactional' => false,
            'status' => 'Active',
        ]);

        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'name' => 'Weekend Special Campaign',
            'channel' => 'sms',
            'communication_template_id' => $template->id,
            'scheduled_at' => now()->subMinutes(10),
            'status' => 'Scheduled',
        ]);

        // Run campaign worker command
        $this->artisan('crm:process-scheduled-campaigns')
            ->assertExitCode(0);

        $campaign->refresh();
        $this->assertEquals('Completed', $campaign->status);
        $this->assertGreaterThan(0, $campaign->sent_count);

        // Run automation reminder worker command
        $this->artisan('crm:process-automation-reminders')
            ->assertExitCode(0);
    }
}

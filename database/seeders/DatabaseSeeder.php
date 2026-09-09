<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Building;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Floor;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignmentHistory;
use App\Models\LeadFollowup;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Offer;
use App\Models\OfferNegotiationRound;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\SiteVisit;
use App\Models\Team;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use App\Services\PricingCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Company
        $company = Company::create([
            'name' => 'Skyline Developers & Builders',
            'legal_name' => 'Skyline Developers Corp LLC',
            'slug' => 'skyline-builders',
            'email' => 'contact@skylinebuilders.com',
            'phone' => '+1 (555) 019-2831',
            'address' => '100 Landmark Blvd, Tower Suite 400',
            'city' => 'Austin',
            'state' => 'Texas',
            'pincode' => '78701',
            'country' => 'USA',
            'tax_id_rera' => 'RERA-REG-2026-99182',
            'currency_code' => 'USD',
        ]);

        // Seed Permissions Catalog
        $permissionsList = [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'general'],
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users'],
            ['name' => 'View Projects', 'slug' => 'projects.view', 'module' => 'projects'],
            ['name' => 'Create Projects', 'slug' => 'projects.create', 'module' => 'projects'],
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory'],
            ['name' => 'View Leads', 'slug' => 'leads.view', 'module' => 'leads'],
            ['name' => 'Create Leads', 'slug' => 'leads.create', 'module' => 'leads'],
            ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'module' => 'leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'module' => 'leads'],
            ['name' => 'Assign Leads', 'slug' => 'leads.assign', 'module' => 'leads'],
            ['name' => 'View Site Visits', 'slug' => 'site_visits.view', 'module' => 'site_visits'],
            ['name' => 'View Sales Calendar', 'slug' => 'calendar.view', 'module' => 'calendar'],
            ['name' => 'View Offers', 'slug' => 'offers.view', 'module' => 'offers'],
            ['name' => 'Create Offers', 'slug' => 'offers.create', 'module' => 'offers'],
            ['name' => 'Approve Offers', 'slug' => 'offers.approve', 'module' => 'offers'],
            ['name' => 'View Bookings', 'slug' => 'bookings.view', 'module' => 'bookings'],
            ['name' => 'Create Bookings', 'slug' => 'bookings.create', 'module' => 'bookings'],
            ['name' => 'View Payments', 'slug' => 'payments.view', 'module' => 'payments'],
            ['name' => 'Record Payments', 'slug' => 'payments.create', 'module' => 'payments'],
        ];

        $permModels = [];
        foreach ($permissionsList as $pData) {
            $permModels[$pData['slug']] = Permission::firstOrCreate(['slug' => $pData['slug']], $pData);
        }

        // Seed Default Roles & Attach Permissions
        $adminRole = Role::create(['company_id' => $company->id, 'name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full administrative access']);
        $adminRole->permissions()->sync(array_column($permModels, 'id'));

        $managerRole = Role::create(['company_id' => $company->id, 'name' => 'Sales Manager', 'slug' => 'sales_manager', 'description' => 'Oversees sales team and team leads']);
        $managerRole->permissions()->sync([
            $permModels['dashboard.view']->id,
            $permModels['projects.view']->id,
            $permModels['inventory.view']->id,
            $permModels['leads.view']->id,
            $permModels['leads.create']->id,
            $permModels['leads.edit']->id,
            $permModels['leads.assign']->id,
            $permModels['site_visits.view']->id,
            $permModels['calendar.view']->id,
            $permModels['offers.view']->id,
            $permModels['offers.create']->id,
            $permModels['offers.approve']->id,
            $permModels['bookings.view']->id,
        ]);

        $agentRole = Role::create(['company_id' => $company->id, 'name' => 'Sales Executive', 'slug' => 'sales_agent', 'description' => 'Handles assigned buyer leads']);
        $agentRole->permissions()->sync([
            $permModels['dashboard.view']->id,
            $permModels['projects.view']->id,
            $permModels['inventory.view']->id,
            $permModels['leads.view']->id,
            $permModels['leads.create']->id,
            $permModels['leads.edit']->id,
            $permModels['site_visits.view']->id,
            $permModels['calendar.view']->id,
            $permModels['offers.view']->id,
            $permModels['offers.create']->id,
            $permModels['bookings.view']->id,
        ]);

        $accountantRole = Role::create(['company_id' => $company->id, 'name' => 'Accountant', 'slug' => 'accountant', 'description' => 'Handles payment receipts and accounts']);
        $accountantRole->permissions()->sync([
            $permModels['dashboard.view']->id,
            $permModels['bookings.view']->id,
            $permModels['payments.view']->id,
            $permModels['payments.create']->id,
        ]);

        // 2. Create Configurable Lead Sources
        $defaultSources = [
            'Website', 'Google', 'Google Ads', 'Facebook', 'Instagram',
            'WhatsApp', '99acres', 'MagicBricks', 'Housing', 'Referral',
            'Walk-in', 'Broker', 'Channel Partner', 'Call', 'Other'
        ];

        $sourceModels = [];
        foreach ($defaultSources as $srcName) {
            $sourceModels[$srcName] = LeadSource::create([
                'company_id' => $company->id,
                'name' => $srcName,
                'is_active' => true,
            ]);
        }

        // 3. Create Configurable Lead Statuses
        $defaultStatuses = [
            ['name' => 'New', 'color' => '#3b82f6', 'order' => 10, 'won' => false, 'lost' => false],
            ['name' => 'Contacted', 'color' => '#0ea5e9', 'order' => 20, 'won' => false, 'lost' => false],
            ['name' => 'Qualified', 'color' => '#06b6d4', 'order' => 30, 'won' => false, 'lost' => false],
            ['name' => 'Site Visit Planned', 'color' => '#f59e0b', 'order' => 40, 'won' => false, 'lost' => false],
            ['name' => 'Site Visit Completed', 'color' => '#14b8a6', 'order' => 50, 'won' => false, 'lost' => false],
            ['name' => 'Negotiation', 'color' => '#8b5cf6', 'order' => 60, 'won' => false, 'lost' => false],
            ['name' => 'Booking', 'color' => '#ec4899', 'order' => 70, 'won' => false, 'lost' => false],
            ['name' => 'Won', 'color' => '#10b981', 'order' => 80, 'won' => true, 'lost' => false],
            ['name' => 'Lost', 'color' => '#ef4444', 'order' => 90, 'won' => false, 'lost' => true],
        ];

        $statusModels = [];
        foreach ($defaultStatuses as $st) {
            $statusModels[$st['name']] = LeadStatus::create([
                'company_id' => $company->id,
                'name' => $st['name'],
                'color_code' => $st['color'],
                'sort_order' => $st['order'],
                'is_won' => $st['won'],
                'is_lost' => $st['lost'],
                'is_active' => true,
            ]);
        }

        // 4. Create Users
        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Alexander Vance',
            'email' => 'admin@recrm.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'mobile' => '+1 (555) 019-0001',
            'status' => 'Active',
        ]);
        $admin->roles()->attach($adminRole->id);

        $manager = User::create([
            'company_id' => $company->id,
            'name' => 'Rachel Green',
            'email' => 'manager@recrm.com',
            'password' => Hash::make('password'),
            'role' => 'sales_manager',
            'mobile' => '+1 (555) 019-0004',
            'status' => 'Active',
        ]);
        $manager->roles()->attach($managerRole->id);

        $agent1 = User::create([
            'company_id' => $company->id,
            'name' => 'Sophia Carter',
            'email' => 'agent@recrm.com',
            'password' => Hash::make('password'),
            'role' => 'sales_agent',
            'mobile' => '+1 (555) 019-0002',
            'status' => 'Active',
        ]);
        $agent1->roles()->attach($agentRole->id);

        $accountant = User::create([
            'company_id' => $company->id,
            'name' => 'Michael Chang',
            'email' => 'accountant@recrm.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'mobile' => '+1 (555) 019-0003',
            'status' => 'Active',
        ]);
        $accountant->roles()->attach($accountantRole->id);

        // 5. Create Team & Members
        $team = Team::create([
            'company_id' => $company->id,
            'name' => 'Residential Sales Alpha',
            'description' => 'Primary high-rise residential sales team',
            'manager_id' => $manager->id,
        ]);
        $team->members()->attach([$agent1->id, $manager->id]);

        // 6. Create Configurable Unit Types
        $ut1 = UnitType::create([
            'company_id' => $company->id,
            'name' => '2 BHK Deluxe',
            'code' => '2BHK-DLX',
            'category' => '2 BHK',
            'default_carpet_area' => 1100.00,
        ]);

        $ut2 = UnitType::create([
            'company_id' => $company->id,
            'name' => '3 BHK Luxury Penthouse',
            'code' => '3BHK-LUX',
            'category' => '3 BHK',
            'default_carpet_area' => 1450.00,
        ]);

        // 7. Create Projects
        $project1 = Project::create([
            'company_id' => $company->id,
            'project_name' => 'Skyline Grand Heights',
            'name' => 'Skyline Grand Heights',
            'project_code' => 'SGH-2026',
            'project_type' => 'Residential',
            'project_status' => 'Under Construction',
            'status' => 'Under Construction',
            'address' => 'Downtown Boulevard',
            'city' => 'Austin',
            'state' => 'Texas',
            'pincode' => '78701',
            'RERA_number' => 'RERA-TX-88771',
            'RERA_registration_date' => '2026-01-10',
            'start_date' => '2026-02-01',
            'expected_completion' => '2028-12-31',
            'total_land_area' => 250000.00,
            'project_manager_id' => $admin->id,
            'description' => 'Luxury 2BHK and 3BHK high-rise residential towers with infinity pool and clubhouse.',
        ]);

        $project2 = Project::create([
            'company_id' => $company->id,
            'project_name' => 'Apex Horizon Tech Park',
            'name' => 'Apex Horizon Tech Park',
            'project_code' => 'AHTP-2026',
            'project_type' => 'Commercial',
            'project_status' => 'Planning',
            'status' => 'Planning',
            'address' => 'Innovation Hub District',
            'city' => 'Austin',
            'state' => 'Texas',
            'pincode' => '78702',
            'RERA_number' => 'RERA-TX-88772',
            'total_land_area' => 180000.00,
            'description' => 'Modern grade-A commercial office suites and retail showrooms.',
        ]);

        // 8. Create Buildings, Wings, Floors, Units & Pricings
        $building1 = Building::create([
            'project_id' => $project1->id,
            'name' => 'Tower Alpha',
            'code' => 'TWR-A',
            'number_of_floors' => 4,
            'status' => 'Under Construction',
        ]);

        $wing1 = Wing::create([
            'building_id' => $building1->id,
            'name' => 'East Wing',
            'code' => 'W-EAST',
            'number_of_floors' => 4,
        ]);

        $pricingCalculator = new PricingCalculatorService();
        $unitsCreated = [];

        for ($f = 1; $f <= 4; $f++) {
            $floor = Floor::create([
                'wing_id' => $wing1->id,
                'floor_number' => $f,
                'label' => "Floor {$f}",
            ]);

            for ($u = 1; $u <= 4; $u++) {
                $unitNum = ($f * 100) + $u;
                $is3Bhk = ($u % 2 == 0);
                $unitTypeObj = $is3Bhk ? $ut2 : $ut1;
                $status = match(true) {
                    $unitNum == 101 => 'Booked',
                    $unitNum == 102 => 'Sold',
                    $unitNum == 201 => 'Hold',
                    default => 'Available',
                };

                $carpetArea = $is3Bhk ? 1450.00 : 1100.00;
                $builtUp = $carpetArea * 1.15;
                $superBuiltUp = $carpetArea * 1.35;

                $unit = Unit::create([
                    'company_id' => $company->id,
                    'project_id' => $project1->id,
                    'building_id' => $building1->id,
                    'wing_id' => $wing1->id,
                    'floor_id' => $floor->id,
                    'unit_type_id' => $unitTypeObj->id,
                    'unit_number' => (string) $unitNum,
                    'unit_code' => "SGH-TWR-A-{$unitNum}",
                    'carpet_area' => $carpetArea,
                    'built_up_area' => $builtUp,
                    'super_built_up_area' => $superBuiltUp,
                    'bedrooms' => $is3Bhk ? 3 : 2,
                    'bathrooms' => $is3Bhk ? 3 : 2,
                    'facing' => $is3Bhk ? 'North-East' : 'East',
                    'parking' => 'Covered',
                    'possession_status' => 'Under Construction',
                    'inventory_status' => $status,
                    'unit_type' => $unitTypeObj->name,
                    'carpet_area_sqft' => $carpetArea,
                    'super_builtup_area_sqft' => $superBuiltUp,
                    'base_rate_per_sqft' => 150.00,
                    'status' => $status,
                ]);

                $pricingData = [
                    'rate_per_sqft' => 150.00,
                    'base_price' => $carpetArea * 150.00,
                    'floor_rise_rate' => 500.00,
                    'facing_premium' => $is3Bhk ? 3000.00 : 0.00,
                    'plc_amount' => 5000.00,
                    'parking_charges' => 10000.00,
                    'gst_percent' => 5.00,
                ];

                $calculated = $pricingCalculator->calculate($pricingData, $carpetArea, $f);

                UnitPricing::create([
                    'unit_id' => $unit->id,
                    'rate_per_sqft' => 150.00,
                    'base_price' => $calculated['base_price'],
                    'floor_rise_rate' => 500.00,
                    'facing_premium' => $calculated['facing_premium'],
                    'plc_amount' => 5000.00,
                    'parking_charges' => 10000.00,
                    'gst_percent' => 5.00,
                    'calculated_total_price' => $calculated['calculated_total_price'],
                ]);

                $unit->update(['total_price' => $calculated['calculated_total_price']]);
                $unitsCreated[$unitNum] = $unit;
            }
        }

        // 9. Seed V0.3 Leads & Activities
        $lead1 = Lead::create([
            'company_id' => $company->id,
            'lead_number' => 'LD-2026-00001',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'mobile' => '9876543210',
            'alternate_mobile' => '9876543211',
            'email' => 'david.miller@example.com',
            'city' => 'Austin',
            'location' => 'Downtown',
            'source' => 'Website',
            'source_id' => $sourceModels['Website']->id,
            'campaign' => 'Google Search Ad 2026',
            'project_id' => $project1->id,
            'unit_type' => '3BHK Luxury Penthouse',
            'minimum_budget' => 200000.00,
            'maximum_budget' => 300000.00,
            'preferred_floor' => '2nd Floor',
            'preferred_facing' => 'East',
            'purchase_timeline' => 'Immediate',
            'priority' => 'Hot',
            'status' => 'Won',
            'status_id' => $statusModels['Won']->id,
            'assigned_to' => $agent1->id,
            'assigned_team_id' => $team->id,
            'notes' => 'Looking for 3BHK flat on 2nd floor with East facing.',
        ]);

        LeadActivity::create([
            'company_id' => $company->id,
            'lead_id' => $lead1->id,
            'user_id' => $agent1->id,
            'activity_type' => 'Call',
            'subject' => 'Initial Requirement Gathering Call',
            'summary' => 'Initial inquiry phone call. Discussed unit plans and payment schedule.',
            'completed_at' => now()->subDays(10),
            'status' => 'Completed',
        ]);

        LeadAssignmentHistory::create([
            'company_id' => $company->id,
            'lead_id' => $lead1->id,
            'assigned_by' => $manager->id,
            'assigned_to_user_id' => $agent1->id,
            'assigned_to_team_id' => $team->id,
            'notes' => 'Initial assignment from manager',
        ]);

        $lead2 = Lead::create([
            'company_id' => $company->id,
            'lead_number' => 'LD-2026-00002',
            'first_name' => 'Sarah',
            'last_name' => 'Jenkins',
            'mobile' => '9876543222',
            'email' => 'sarah.j@example.com',
            'city' => 'Austin',
            'location' => 'North Loop',
            'source' => 'Walk-in',
            'source_id' => $sourceModels['Walk-in']->id,
            'campaign' => 'Site Billboard',
            'project_id' => $project1->id,
            'unit_type' => '2 BHK Deluxe',
            'minimum_budget' => 180000.00,
            'maximum_budget' => 220000.00,
            'priority' => 'High',
            'status' => 'Site Visit Planned',
            'status_id' => $statusModels['Site Visit Planned']->id,
            'assigned_to' => $agent1->id,
            'assigned_team_id' => $team->id,
        ]);

        LeadFollowup::create([
            'company_id' => $company->id,
            'lead_id' => $lead2->id,
            'user_id' => $agent1->id,
            'type' => 'Site Visit',
            'followup_at' => now()->addDay()->setHour(11)->setMinute(0),
            'notes' => 'Guided site tour of Tower Alpha Floor 2.',
            'status' => 'Pending',
        ]);

        // 10. Seed V0.4 Site Visits with Cab Dispatch
        SiteVisit::create([
            'company_id' => $company->id,
            'visit_number' => 'SV-2026-00001',
            'lead_id' => $lead2->id,
            'project_id' => $project1->id,
            'assigned_to' => $agent1->id,
            'transportation_type' => 'Company Cab',
            'driver_name' => 'Carlos Driver',
            'driver_phone' => '+1 (555) 998-1122',
            'pickup_location' => '742 Evergreen Terrace, Austin TX',
            'pickup_time' => now()->addDay()->setHour(10)->setMinute(30),
            'visit_date' => now()->addDay()->setHour(11)->setMinute(0),
            'status' => 'Driver Assigned',
        ]);

        // 11. Seed V0.4 Formal Offers & Negotiations
        $targetUnit = $unitsCreated[201];
        $targetUnit->updateInventoryStatus('Hold', 'Locked for formal offer proposal');

        $offer1 = Offer::create([
            'company_id' => $company->id,
            'offer_number' => 'OFF-2026-00001',
            'lead_id' => $lead2->id,
            'unit_id' => $targetUnit->id,
            'created_by' => $agent1->id,
            'approved_by' => null,
            'original_unit_price' => $targetUnit->total_price,
            'offered_price' => $targetUnit->total_price * 0.92, // 8% discount
            'discount_amount' => $targetUnit->total_price * 0.08,
            'discount_percentage' => 8.00,
            'token_amount_offered' => 200000.00,
            'payment_plan_type' => 'Construction Linked',
            'valid_until' => now()->addDays(7),
            'unit_lock_expires_at' => now()->addHours(72),
            'status' => 'Pending Manager Approval',
            'terms_conditions' => 'Includes free covered parking slot and modular kitchen voucher.',
        ]);

        OfferNegotiationRound::create([
            'company_id' => $company->id,
            'offer_id' => $offer1->id,
            'round_number' => 1,
            'offered_by' => 'Buyer',
            'proposed_by_user_id' => $agent1->id,
            'proposed_price' => $targetUnit->total_price * 0.92,
            'discount_amount' => $targetUnit->total_price * 0.08,
            'requested_token_amount' => 200000.00,
            'payment_terms' => 'Construction Linked',
            'comments' => 'Buyer requested 8% discount for early booking.',
        ]);

        // 12. Create Customer & Booking
        $customer1 = Customer::create([
            'company_id' => $company->id,
            'lead_id' => $lead1->id,
            'first_name' => 'David',
            'last_name' => 'Miller',
            'email' => 'david.miller@example.com',
            'phone' => '+1 (555) 234-5678',
            'pan_number' => 'DVM887712K',
            'address' => '742 Evergreen Terrace, Austin TX',
            'kyc_status' => 'Verified',
        ]);

        $bookedUnit = $unitsCreated[101];

        $booking = Booking::create([
            'company_id' => $company->id,
            'booking_number' => 'BK-2026-101',
            'unit_id' => $bookedUnit->id,
            'customer_id' => $customer1->id,
            'sales_agent_id' => $agent1->id,
            'booking_date' => now()->subDays(5)->toDateString(),
            'agreed_price' => $bookedUnit->total_price,
            'discount_amount' => 2500.00,
            'tax_amount' => 5000.00,
            'total_amount' => $bookedUnit->total_price,
            'booking_amount_paid' => 25000.00,
            'status' => 'Confirmed',
        ]);

        $sch1 = PaymentSchedule::create([
            'company_id' => $company->id,
            'booking_id' => $booking->id,
            'milestone_name' => 'Token & Booking Advance (10%)',
            'due_date' => now()->subDays(5)->toDateString(),
            'amount_due' => 25000.00,
            'amount_paid' => 25000.00,
            'status' => 'Paid',
        ]);

        PaymentSchedule::create([
            'company_id' => $company->id,
            'booking_id' => $booking->id,
            'milestone_name' => 'Foundation & Basement Completion (30%)',
            'due_date' => now()->addMonths(2)->toDateString(),
            'amount_due' => 60000.00,
            'amount_paid' => 0.00,
            'status' => 'Pending',
        ]);

        Payment::create([
            'company_id' => $company->id,
            'booking_id' => $booking->id,
            'payment_schedule_id' => $sch1->id,
            'receipt_number' => 'REC-2026-0001',
            'amount_paid' => 25000.00,
            'payment_date' => now()->subDays(5)->toDateString(),
            'payment_method' => 'Bank Transfer',
            'transaction_reference' => 'TXN-BANK-998822',
            'status' => 'Verified',
            'received_by' => $accountant->id,
        ]);
    }
}

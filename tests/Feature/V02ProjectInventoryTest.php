<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V02ProjectInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_v02_project_crud_and_status_history_logging(): void
    {
        $company = Company::create(['name' => 'PropFlow Realty', 'slug' => 'propflow']);
        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'Active',
        ]);

        $this->actingAs($admin);

        // Store Project V0.2
        $response = $this->post(route('projects.store'), [
            'project_name' => 'Skyline Grand Towers',
            'project_code' => 'SGT-2026',
            'project_type' => 'Residential',
            'project_status' => 'Under Construction',
            'RERA_number' => 'RERA-TX-998822',
            'RERA_registration_date' => '2026-01-15',
            'total_land_area' => 300000,
            'city' => 'Austin',
            'state' => 'Texas',
        ]);

        $project = Project::where('project_name', 'Skyline Grand Towers')->first();
        $this->assertNotNull($project);
        $this->assertEquals('RERA-TX-998822', $project->RERA_number);

        // Create Structural Sub-units
        $building = Building::create(['project_id' => $project->id, 'name' => 'Tower 1', 'number_of_floors' => 5]);
        $wing = Wing::create(['building_id' => $building->id, 'name' => 'West Wing']);
        $floor = Floor::create(['wing_id' => $wing->id, 'floor_number' => 2, 'label' => '2nd Floor']);

        $unitType = UnitType::create(['company_id' => $company->id, 'name' => '2 BHK Luxury', 'category' => '2 BHK', 'default_carpet_area' => 1100]);

        $unit = Unit::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'wing_id' => $wing->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => '201',
            'carpet_area' => 1100,
            'built_up_area' => 1350,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'facing' => 'East',
            'inventory_status' => 'Available',
        ]);

        // Transition Unit Status & Assert Status History Log
        $unit->updateInventoryStatus('Hold', 'Customer requested 24h hold', $admin->id);

        $unit->refresh();
        $this->assertEquals('Hold', $unit->inventory_status);
        $this->assertCount(1, $unit->statusHistories);
        $this->assertEquals('Available', $unit->statusHistories->first()->previous_status);
        $this->assertEquals('Hold', $unit->statusHistories->first()->new_status);
    }
}

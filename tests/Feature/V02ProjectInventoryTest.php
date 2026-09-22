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

    public function test_building_floor_creation_and_crud_management(): void
    {
        $company = Company::create(['name' => 'Apex Infra', 'slug' => 'apex']);
        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'Active',
        ]);
        $this->actingAs($admin);

        $project = Project::create([
            'company_id' => $company->id,
            'project_name' => 'Green Residency',
            'project_type' => 'Mixed',
        ]);

        // 1. Create Building with 6 floors via controller POST
        $response = $this->post(route('buildings.store'), [
            'project_id' => $project->id,
            'name' => 'Tower 6 Floors',
            'total_floors' => 6,
            'status' => 'Under Construction',
        ]);

        $response->assertRedirect();

        $building = Building::where('name', 'Tower 6 Floors')->first();
        $this->assertNotNull($building);
        $this->assertEquals(6, $building->number_of_floors);

        $mainWing = $building->wings()->first();
        $this->assertNotNull($mainWing);
        $this->assertEquals(6, $mainWing->floors()->count());

        // 2. Add 7th floor using FloorController
        $floorResp = $this->post(route('floors.store'), [
            'wing_id' => $mainWing->id,
            'floor_number' => 7,
            'label' => '7th Floor Penthouse',
        ]);

        $floorResp->assertRedirect();
        $this->assertEquals(7, $mainWing->floors()->count());

        $floor7 = Floor::where('wing_id', $mainWing->id)->where('floor_number', 7)->first();
        $this->assertNotNull($floor7);
        $this->assertEquals('7th Floor Penthouse', $floor7->label);

        // 3. Update Floor
        $this->put(route('floors.update', $floor7->id), [
            'floor_number' => 7,
            'label' => 'Executive Suite 7',
        ]);

        $floor7->refresh();
        $this->assertEquals('Executive Suite 7', $floor7->label);

        // 4. Delete Floor
        $this->delete(route('floors.destroy', $floor7->id));
        $this->assertEquals(6, $mainWing->floors()->count());
    }
}

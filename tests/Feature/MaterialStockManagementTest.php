<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MaterialCategory;
use App\Models\MaterialInward;
use App\Models\MaterialStock;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialStockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $company;
    protected Project $project;
    protected MaterialCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Skyline Developers',
            'slug' => 'skyline-dev',
        ]);

        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Store Manager',
            'email' => 'store@skyline.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'project_name' => 'Skyline Heights',
            'project_status' => 'Under Construction',
        ]);

        $this->category = MaterialCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Bandhkam (Cement)',
        ]);
    }

    public function test_can_receive_inward_stock_replenishment_and_auto_increment_balance()
    {
        $response = $this->actingAs($this->admin)->post(route('projects.inwards.store', $this->project->id), [
            'category_id' => $this->category->id,
            'material_name' => 'Ultratech PPC Cement',
            'qty_received' => 500,
            'unit_of_measure' => 'Bags',
            'unit_cost' => 350.00,
            'received_date' => date('Y-m-d'),
            'min_threshold_qty' => 100,
            'invoice_number' => 'INV-9901',
        ]);

        $response->assertRedirect();

        // Assert Inward Created
        $this->assertDatabaseHas('material_inwards', [
            'project_id' => $this->project->id,
            'material_name' => 'Ultratech PPC Cement',
            'qty_received' => 500,
        ]);

        // Assert Stock Ledger Updated
        $stock = MaterialStock::where('project_id', $this->project->id)
            ->where('material_name', 'Ultratech PPC Cement')
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(500, $stock->current_stock_qty);
        $this->assertEquals(100, $stock->min_threshold_qty);
        $this->assertFalse($stock->isLowStock());
        $this->assertFalse($stock->isOutOfStock());
    }

    public function test_logging_usage_decrements_live_stock_balance_and_triggers_low_stock_warning()
    {
        // 1. Initial Stock of 50 Bags
        $stock = MaterialStock::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'material_name' => 'Ultratech PPC Cement',
            'unit_of_measure' => 'Bags',
            'current_stock_qty' => 50,
            'min_threshold_qty' => 20,
        ]);

        // 2. Consume 35 Bags (Leaving 15 Bags, which is < min threshold 20)
        $response = $this->actingAs($this->admin)->post(route('projects.materials.store', $this->project->id), [
            'category_id' => $this->category->id,
            'material_name' => 'Ultratech PPC Cement',
            'quantity' => 35,
            'unit_of_measure' => 'Bags',
            'entry_date' => date('Y-m-d'),
        ]);

        $response->assertRedirect();

        $stock->refresh();
        $this->assertEquals(15, $stock->current_stock_qty);
        $this->assertTrue($stock->isLowStock());
    }
}

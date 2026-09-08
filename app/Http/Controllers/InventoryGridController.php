<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use App\Services\PricingCalculatorService;
use Illuminate\Http\Request;

class InventoryGridController extends Controller
{
    protected PricingCalculatorService $pricingCalculator;

    public function __construct(PricingCalculatorService $pricingCalculator)
    {
        $this->pricingCalculator = $pricingCalculator;
    }

    public function index(Request $request)
    {
        $query = Unit::with([
            'project',
            'building',
            'wing',
            'floor',
            'unitType',
            'pricing',
            'statusHistories.user',
        ]);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        if ($request->filled('unit_type_id')) {
            $query->where('unit_type_id', $request->unit_type_id);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->filled('facing')) {
            $query->where('facing', $request->facing);
        }

        if ($request->filled('inventory_status')) {
            $query->where('inventory_status', $request->inventory_status);
        }

        if ($request->filled('min_area')) {
            $query->where('carpet_area', '>=', $request->min_area);
        }

        if ($request->filled('max_area')) {
            $query->where('carpet_area', '<=', $request->max_area);
        }

        if ($request->filled('min_price')) {
            $query->where('total_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('total_price', '<=', $request->max_price);
        }

        $units = $query->get();

        // Group units by Floor -> Wing -> Building -> Project
        $projects = Project::with(['buildings.wings.floors'])->get();
        $selectedProjectId = $request->get('project_id', optional($projects->first())->id);
        $activeProject = $projects->firstWhere('id', $selectedProjectId);

        $unitTypes = UnitType::all();
        $buildings = $activeProject ? $activeProject->buildings : Building::all();

        // Count aggregate inventory statistics
        $statusCounts = [
            'Total' => $units->count(),
            'Available' => $units->where('inventory_status', 'Available')->count(),
            'Hold' => $units->where('inventory_status', 'Hold')->count(),
            'Booked' => $units->where('inventory_status', 'Booked')->count(),
            'Sold' => $units->where('inventory_status', 'Sold')->count(),
            'Blocked' => $units->where('inventory_status', 'Blocked')->count(),
            'Cancelled' => $units->where('inventory_status', 'Cancelled')->count(),
        ];

        return view('inventory.grid', compact(
            'projects',
            'activeProject',
            'buildings',
            'unitTypes',
            'units',
            'statusCounts'
        ));
    }

    public function updateStatus(Request $request, Unit $unit)
    {
        $request->validate([
            'inventory_status' => 'required|in:Available,Hold,Booked,Sold,Cancelled,Blocked',
            'reason' => 'nullable|string',
        ]);

        $unit->updateInventoryStatus($request->inventory_status, $request->reason, auth()->id());

        return back()->with('success', "Inventory status for Unit #{$unit->unit_number} updated to {$unit->inventory_status}!");
    }

    public function updatePricing(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'rate_per_sqft' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'floor_rise_rate' => 'nullable|numeric|min:0',
            'facing_premium' => 'nullable|numeric|min:0',
            'plc_amount' => 'nullable|numeric|min:0',
            'parking_charges' => 'nullable|numeric|min:0',
            'clubhouse_charges' => 'nullable|numeric|min:0',
            'infrastructure_charges' => 'nullable|numeric|min:0',
            'maintenance_deposit' => 'nullable|numeric|min:0',
            'legal_charges' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $floorNumber = $unit->floor ? $unit->floor->floor_number : 1;
        $areaSqFt = $unit->carpet_area > 0 ? $unit->carpet_area : $unit->super_built_up_area;

        $calcResult = $this->pricingCalculator->calculate($data, (float) $areaSqFt, $floorNumber);

        $data['calculated_total_price'] = $calcResult['calculated_total_price'];

        $unit->pricing()->updateOrCreate(['unit_id' => $unit->id], $data);

        // Update unit total_price for quick queries
        $unit->update([
            'base_rate_per_sqft' => $data['rate_per_sqft'],
            'total_price' => $calcResult['calculated_total_price'],
        ]);

        return back()->with('success', "Pricing structure updated for Unit #{$unit->unit_number}! Total calculated: $" . number_format($calcResult['calculated_total_price'], 2));
    }
}

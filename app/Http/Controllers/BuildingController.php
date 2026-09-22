<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Wing;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Store new Building / Tower with default Wing and Floors.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'total_floors' => 'nullable|integer|min:1|max:100',
            'number_of_floors' => 'nullable|integer|min:1|max:100',
            'status' => 'required|string',
        ]);

        $numFloors = (int) ($validated['number_of_floors'] ?? $validated['total_floors'] ?? 1);

        $building = Building::create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'number_of_floors' => $numFloors,
            'status' => $validated['status'],
        ]);

        // Auto-create default Wing & Floors for convenience
        $wing = Wing::create([
            'building_id' => $building->id,
            'name' => 'Main Wing',
            'code' => 'W1',
            'number_of_floors' => $numFloors,
            'status' => 'Active',
        ]);

        for ($i = 1; $i <= $numFloors; $i++) {
            Floor::create([
                'wing_id' => $wing->id,
                'floor_number' => $i,
                'label' => "Floor {$i}",
                'status' => 'Active',
            ]);
        }

        return redirect()->back()
            ->with('success', "Building '{$building->name}' created with {$numFloors} floors!");
    }

    /**
     * Update Building / Tower details and sync floors count.
     */
    public function update(Request $request, Building $building)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'number_of_floors' => 'required|integer|min:1|max:100',
            'status' => 'required|string',
        ]);

        $newNumFloors = (int) $validated['number_of_floors'];

        $building->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'number_of_floors' => $newNumFloors,
            'status' => $validated['status'],
        ]);

        // Ensure default wing exists
        $wing = $building->wings()->first();
        if (!$wing) {
            $wing = Wing::create([
                'building_id' => $building->id,
                'name' => 'Main Wing',
                'code' => 'W1',
                'number_of_floors' => $newNumFloors,
                'status' => 'Active',
            ]);
        } else {
            $wing->update(['number_of_floors' => $newNumFloors]);
        }

        // If number of floors increased, generate missing floor records
        $existingFloorCount = $wing->floors()->count();
        if ($newNumFloors > $existingFloorCount) {
            $maxFloorNum = $wing->floors()->max('floor_number') ?? 0;
            for ($i = $maxFloorNum + 1; $i <= $newNumFloors; $i++) {
                Floor::create([
                    'wing_id' => $wing->id,
                    'floor_number' => $i,
                    'label' => "Floor {$i}",
                    'status' => 'Active',
                ]);
            }
        }

        return redirect()->back()
            ->with('success', "Building '{$building->name}' updated to {$newNumFloors} floors!");
    }

    /**
     * Delete Building / Tower.
     */
    public function destroy(Building $building)
    {
        $name = $building->name;
        $building->delete();

        return redirect()->back()
            ->with('success', "Building '{$name}' deleted successfully.");
    }
}

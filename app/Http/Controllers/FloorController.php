<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Wing;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    /**
     * Store / Add new Floor to a Building / Wing.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wing_id' => 'required|exists:wings,id',
            'floor_number' => 'nullable|integer|min:-5|max:200',
            'label' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
        ]);

        $wing = Wing::findOrFail($validated['wing_id']);
        $building = $wing->building;

        $nextFloorNum = isset($validated['floor_number']) && $validated['floor_number'] !== '' 
            ? (int) $validated['floor_number'] 
            : (($wing->floors()->max('floor_number') ?? 0) + 1);

        $floor = Floor::create([
            'wing_id' => $wing->id,
            'floor_number' => $nextFloorNum,
            'label' => $validated['label'],
            'status' => $validated['status'] ?? 'Active',
        ]);

        // Sync floor count on wing and building
        $totalFloorsCount = $wing->floors()->count();
        $wing->update(['number_of_floors' => $totalFloorsCount]);
        if ($building) {
            $building->update(['number_of_floors' => max($building->number_of_floors, $totalFloorsCount)]);
        }

        return redirect()->back()
            ->with('success', "'{$floor->label}' added successfully!");
    }

    /**
     * Update Floor details.
     */
    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'floor_number' => 'required|integer|min:-5|max:200',
            'label' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
        ]);

        $floor->update([
            'floor_number' => $validated['floor_number'],
            'label' => $validated['label'],
            'status' => $validated['status'] ?? $floor->status,
        ]);

        return redirect()->back()
            ->with('success', "Floor updated to '{$floor->label}'!");
    }

    /**
     * Delete Floor.
     */
    public function destroy(Floor $floor)
    {
        $wing = $floor->wing;
        $building = $wing ? $wing->building : null;
        $label = $floor->label ?? "Floor {$floor->floor_number}";

        $floor->delete();

        if ($wing) {
            $totalFloorsCount = $wing->floors()->count();
            $wing->update(['number_of_floors' => $totalFloorsCount]);
            if ($building) {
                $building->update(['number_of_floors' => max(1, $totalFloorsCount)]);
            }
        }

        return redirect()->back()
            ->with('success', "'{$label}' deleted successfully.");
    }
}

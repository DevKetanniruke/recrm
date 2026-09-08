<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Wing;

use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'total_floors' => 'required|integer|min:1|max:100',
            'status' => 'required|string',
        ]);

        $building = Building::create($data);

        // Auto-create default Wing & Floors for convenience
        $wing = Wing::create([
            'company_id' => $building->company_id,
            'building_id' => $building->id,
            'name' => 'Main Wing',
            'code' => 'W1',
        ]);

        for ($i = 1; $i <= $building->total_floors; $i++) {
            Floor::create([
                'company_id' => $building->company_id,
                'wing_id' => $wing->id,
                'floor_number' => $i,
                'floor_name' => "Floor {$i}",
            ]);
        }

        return redirect()->route('projects.show', $building->project_id)
            ->with('success', "Building '{$building->name}' created with {$building->total_floors} floors!");
    }
}

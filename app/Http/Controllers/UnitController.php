<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with(['floor.wing.building.project']);

        if ($request->filled('project_id')) {
            $query->whereHas('floor.wing.building', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        $units = $query->paginate(24);
        $projects = Project::all();

        return view('units.index', compact('units', 'projects'));
    }

    public function matrix(Request $request)
    {
        $selectedProjectId = $request->get('project_id');
        $projects = Project::with(['buildings.wings.floors.units'])->get();

        $activeProject = $selectedProjectId
            ? $projects->firstWhere('id', $selectedProjectId)
            : $projects->first();

        return view('units.matrix', compact('projects', 'activeProject'));
    }

    public function store(StoreUnitRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('layout_plan')) {
            $data['layout_plan_path'] = $request->file('layout_plan')->store('layouts', 'public');
        }

        $unit = Unit::create($data);

        return back()->with('success', "Unit #{$unit->unit_number} added successfully!");
    }

    public function updateStatus(Request $request, Unit $unit)
    {
        $request->validate([
            'status' => 'required|in:Available,On Hold,Booked,Sold,Blocked',
        ]);

        $unit->update(['status' => $request->status]);

        return back()->with('success', "Unit #{$unit->unit_number} status updated to {$unit->status}!");
    }
}

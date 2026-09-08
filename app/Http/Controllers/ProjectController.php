<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectV2Request;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['projectManager'])
            ->withCount(['buildings', 'units'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $managers = User::where('company_id', auth()->user()->company_id)->get();
        return view('projects.create', compact('managers'));
    }

    public function store(StoreProjectV2Request $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $project = Project::create($data);

        return redirect()->route('projects.show', $project->id)
            ->with('success', "Project '{$project->project_name}' created successfully!");
    }

    public function show(Project $project)
    {
        $project->load([
            'projectManager',
            'buildings.wings.floors.units.unitType',
            'buildings.wings.floors.units.pricing',
        ]);

        $managers = User::where('company_id', auth()->user()->company_id)->get();

        return view('projects.show', compact('project', 'managers'));
    }

    public function edit(Project $project)
    {
        $managers = User::where('company_id', auth()->user()->company_id)->get();
        return view('projects.edit', compact('project', 'managers'));
    }

    public function update(StoreProjectV2Request $request, Project $project)
    {
        $data = $request->validated();
        $project->update($data);

        return redirect()->route('projects.show', $project->id)
            ->with('success', "Project '{$project->project_name}' updated successfully!");
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}

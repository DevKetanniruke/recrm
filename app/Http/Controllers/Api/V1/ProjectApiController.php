<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectApiController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $projects = Project::where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->paginate(20);

        return ProjectResource::collection($projects);
    }

    public function show(Project $project)
    {
        if ($project->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized access to project resource.'], 403);
        }

        return new ProjectResource($project);
    }
}

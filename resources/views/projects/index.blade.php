@extends('layouts.app')

@section('title', 'Projects Portal')
@section('page-title', 'Projects & Real Estate Developments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="brand-font mb-0">All Real Estate Projects</h5>
    @if(auth()->user()->hasPermissionTo('projects.create'))
        <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Project
        </a>
    @endif
</div>

<div class="row g-4">
    @forelse($projects as $project)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="bg-primary bg-opacity-10 p-4 text-center border-bottom">
                    <i class="bi bi-buildings fs-1 text-primary"></i>
                    <h5 class="brand-font mt-2 mb-0 text-dark">{{ $project->project_name }}</h5>
                    <small class="text-secondary"><i class="bi bi-geo-alt"></i> {{ $project->city ?? 'N/A' }}, {{ $project->state ?? '' }}</small>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-dark border">{{ $project->project_type }}</span>
                        <span class="badge @if($project->project_status == 'Under Construction') bg-warning text-dark @elseif($project->project_status == 'Ready to Possess') bg-info text-white @elseif($project->project_status == 'Completed') bg-success @else bg-secondary @endif">
                            {{ $project->project_status }}
                        </span>
                    </div>

                    <p class="text-secondary small line-clamp-2">{{ $project->description ?? 'No project overview description provided.' }}</p>

                    <div class="row g-2 text-secondary small border-top pt-2 mt-2">
                        <div class="col-6"><strong>Towers/Buildings:</strong> {{ $project->buildings_count }}</div>
                        <div class="col-6"><strong>Total Units:</strong> {{ $project->units_count }}</div>
                        <div class="col-6"><strong>RERA Reg:</strong> {{ $project->RERA_number ?? 'N/A' }}</div>
                        <div class="col-6"><strong>Land Area:</strong> {{ number_format($project->total_land_area, 0) }} sqft</div>
                        <div class="col-12"><strong>Project Manager:</strong> {{ $project->projectManager->name ?? 'Unassigned' }}</div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3">
                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-outline-primary w-100 btn-sm fw-semibold">
                        View Project Details & Towers <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-folder-x fs-1 text-secondary"></i>
            <h5 class="mt-2 text-dark">No Projects Available</h5>
            <p class="text-secondary">Click below to add your first real estate development project.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary mt-2">Add New Project</a>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $projects->links() }}
</div>
@endsection

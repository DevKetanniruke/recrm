@extends('layouts.app')

@section('title', $project->project_name)
@section('page-title', 'Project Details: ' . $project->project_name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-2">{{ $project->project_type }}</span>
                <h4 class="brand-font text-dark mb-1">{{ $project->project_name }}</h4>
                <p class="text-secondary small mb-3"><i class="bi bi-geo-alt"></i> {{ $project->address ?? $project->city }}, {{ $project->state }} {{ $project->pincode }}</p>

                <div class="border-top pt-3 text-secondary small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Project Status:</span>
                        <span class="badge bg-warning text-dark">{{ $project->project_status }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>RERA Reg Number:</span>
                        <strong class="text-dark font-monospace">{{ $project->RERA_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>RERA Date:</span>
                        <strong class="text-dark">{{ $project->RERA_registration_date ? $project->RERA_registration_date->format('M d, Y') : 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Land Area:</span>
                        <strong class="text-dark">{{ number_format($project->total_land_area, 2) }} sqft</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Project Manager:</span>
                        <strong class="text-dark">{{ $project->projectManager->name ?? 'Unassigned' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Start Date:</span>
                        <strong class="text-dark">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Expected Completion:</span>
                        <strong class="text-dark">{{ $project->expected_completion ? $project->expected_completion->format('M d, Y') : 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buildings List & Add Building Form -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-building me-2 text-primary"></i> Buildings & Towers</h6>
                @if(auth()->user()->hasPermissionTo('projects.create'))
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Tower / Building
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($project->buildings as $b)
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 brand-font text-dark"><i class="bi bi-building"></i> {{ $b->name }}</h6>
                                    <span class="badge bg-secondary">{{ $b->status }}</span>
                                </div>
                                <small class="text-secondary d-block">Floors: <strong>{{ $b->number_of_floors }} Floors</strong></small>
                                <small class="text-secondary d-block mb-2">Wings: <strong>{{ $b->wings->count() }} Wings</strong></small>
                                <a href="{{ route('inventory.grid', ['project_id' => $project->id, 'building_id' => $b->id]) }}" class="btn btn-xs btn-outline-primary btn-sm w-100">
                                    View Tower Inventory Grid <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-secondary">
                            No buildings or towers registered under this project yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Building -->
<div class="modal fade" id="addBuildingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('buildings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-header border-0">
                    <h5 class="modal-title brand-font">Add Building / Tower</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Building / Tower Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tower A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Building Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. TWR-A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Number of Floors *</label>
                        <input type="number" name="total_floors" class="form-control" value="10" min="1" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Status</label>
                        <select name="status" class="form-select">
                            <option value="Under Construction">Under Construction</option>
                            <option value="Completed">Completed</option>
                            <option value="Planning">Planning</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Create Tower</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

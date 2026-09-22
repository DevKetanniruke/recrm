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

    <!-- Buildings List & Management -->
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
                        @php
                            $allFloorsCount = max($b->number_of_floors, $b->wings->flatMap->floors->count());
                        @endphp
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light position-relative">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 brand-font text-dark"><i class="bi bi-building text-primary me-1"></i> {{ $b->name }}</h6>
                                    <span class="badge bg-secondary">{{ $b->status }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-secondary">Floors: <strong class="text-dark">{{ $allFloorsCount }} Floors</strong></small>
                                    <small class="text-secondary">Wings: <strong class="text-dark">{{ $b->wings->count() }} Wing(s)</strong></small>
                                </div>

                                <div class="d-flex gap-1 my-3 flex-wrap">
                                    <button class="btn btn-xs btn-outline-dark btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editBuildingModal{{ $b->id }}">
                                        <i class="bi bi-pencil me-1"></i> Edit Tower
                                    </button>
                                    <button class="btn btn-xs btn-outline-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#manageFloorsModal{{ $b->id }}">
                                        <i class="bi bi-layers me-1"></i> Manage Floors ({{ $allFloorsCount }})
                                    </button>
                                    <form action="{{ route('buildings.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $b->name }} and all its floors?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <a href="{{ route('inventory.grid', ['project_id' => $project->id, 'building_id' => $b->id]) }}" class="btn btn-sm btn-primary w-100">
                                    View Tower Inventory Grid <i class="bi bi-arrow-right me-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- MODAL: Edit Building -->
                        <div class="modal fade" id="editBuildingModal{{ $b->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="{{ route('buildings.update', $b->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title brand-font fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i> Edit Tower: {{ $b->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Tower / Building Name *</label>
                                                <input type="text" name="name" class="form-control" value="{{ $b->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Building Code</label>
                                                <input type="text" name="code" class="form-control" value="{{ $b->code }}" placeholder="e.g. TWR-A">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Number of Floors *</label>
                                                <input type="number" name="number_of_floors" class="form-control" value="{{ $allFloorsCount }}" min="1" max="100" required>
                                                <small class="text-muted">Increasing this will auto-generate missing floor records.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="Under Construction" {{ $b->status === 'Under Construction' ? 'selected' : '' }}>Under Construction</option>
                                                    <option value="Completed" {{ $b->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Planning" {{ $b->status === 'Planning' ? 'selected' : '' }}>Planning</option>
                                                    <option value="On Hold" {{ $b->status === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-semibold px-4">Update Tower</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL: Manage Floors -->
                        <div class="modal fade" id="manageFloorsModal{{ $b->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <div class="modal-header border-0 bg-light">
                                        <h5 class="modal-title brand-font fw-bold"><i class="bi bi-layers text-info me-2"></i> Manage Floors — {{ $b->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        @php
                                            $mainWing = $b->wings->first();
                                        @endphp

                                        @if($mainWing)
                                            <!-- Add New Floor Form -->
                                            <div class="card border border-info border-opacity-25 bg-info bg-opacity-10 rounded-3 mb-4">
                                                <div class="card-body">
                                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-plus-circle me-1"></i> Add New Floor</h6>
                                                    <form action="{{ route('floors.store') }}" method="POST" class="row g-2 align-items-center">
                                                        @csrf
                                                        <input type="hidden" name="wing_id" value="{{ $mainWing->id }}">
                                                        
                                                        <div class="col-md-3">
                                                            <label class="form-label small text-muted mb-0">Floor Number</label>
                                                            <input type="number" name="floor_number" class="form-control form-control-sm" placeholder="e.g. 7" value="{{ ($mainWing->floors->max('floor_number') ?? 0) + 1 }}">
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted mb-0">Floor Label *</label>
                                                            <input type="text" name="label" class="form-control form-control-sm" placeholder="e.g. Floor 7 or Penthouse Level" required>
                                                        </div>

                                                        <div class="col-md-3 d-flex align-items-end mt-auto">
                                                            <button type="submit" class="btn btn-sm btn-info text-white w-100"><i class="bi bi-check-lg me-1"></i> Add Floor</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Existing Floors List -->
                                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-nested me-1"></i> Floor Listing ({{ $mainWing->floors->count() }} Floors)</h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle border">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 15%;">Floor #</th>
                                                            <th>Floor Label</th>
                                                            <th style="width: 25%;">Status</th>
                                                            <th style="width: 25%;" class="text-end">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($mainWing->floors->sortBy('floor_number') as $fl)
                                                            <tr>
                                                                <form action="{{ route('floors.update', $fl->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <td class="fw-bold text-center">
                                                                        <input type="number" name="floor_number" value="{{ $fl->floor_number }}" class="form-control form-control-sm text-center">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="label" value="{{ $fl->label ?? 'Floor ' . $fl->floor_number }}" class="form-control form-control-sm">
                                                                    </td>
                                                                    <td>
                                                                        <select name="status" class="form-select form-select-sm">
                                                                            <option value="Active" {{ $fl->status === 'Active' ? 'selected' : '' }}>Active</option>
                                                                            <option value="Completed" {{ $fl->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                                                            <option value="Planning" {{ $fl->status === 'Planning' ? 'selected' : '' }}>Planning</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <button type="submit" class="btn btn-xs btn-primary btn-sm me-1" title="Save changes">
                                                                            <i class="bi bi-check-circle"></i> Save
                                                                        </button>
                                                                </form>
                                                                        <form action="{{ route('floors.destroy', $fl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $fl->label }}?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-xs btn-outline-danger btn-sm" title="Delete floor">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted py-3">No floors added yet. Use the form above to add floors.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">No wing configured for this building yet.</p>
                                        @endif
                                    </div>
                                </div>
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
                    <h5 class="modal-title brand-font fw-bold"><i class="bi bi-building-add text-primary me-2"></i> Add Building / Tower</h5>
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
                        <input type="number" name="number_of_floors" class="form-control" value="6" min="1" max="100" required>
                        <input type="hidden" name="total_floors" value="6">
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

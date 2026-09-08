@extends('layouts.app')

@section('title', 'Unit Inventory Matrix')
@section('page-title', 'Interactive Inventory Visual Matrix')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form action="{{ route('units.matrix') }}" method="GET" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fw-semibold text-secondary">Select Project:</label>
            <select name="project_id" class="form-select" onchange="this.form.submit()">
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ optional($activeProject)->id == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->city }})
                    </option>
                @endforeach
            </select>
        </form>

        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-available px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Available</span>
            <span class="badge badge-onhold px-3 py-2"><i class="bi bi-circle-fill me-1"></i> On Hold</span>
            <span class="badge badge-booked px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Booked</span>
            <span class="badge badge-sold px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Sold</span>
            <span class="badge badge-blocked px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Blocked</span>
        </div>
    </div>
</div>

@if($activeProject)
    @forelse($activeProject->buildings as $building)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 brand-font text-dark">
                    <i class="bi bi-building me-2 text-primary"></i> {{ $building->name }}
                    <span class="badge bg-light text-secondary border fw-normal fs-6 ms-2">{{ $building->total_floors }} Floors</span>
                </h5>
            </div>
            <div class="card-body">
                @foreach($building->wings as $wing)
                    <h6 class="text-secondary fw-bold mt-2 mb-3 border-bottom pb-1"><i class="bi bi-diagram-2 me-1"></i> Wing: {{ $wing->name }}</h6>
                    
                    @foreach($wing->floors->sortByDesc('floor_number') as $floor)
                        <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom border-light">
                            <div class="fw-bold text-secondary text-nowrap" style="width: 100px;">
                                Floor {{ $floor->floor_number }}
                            </div>
                            <div class="d-flex flex-wrap gap-2 flex-grow-1">
                                @forelse($floor->units as $unit)
                                    @php
                                        $badgeClass = match($unit->status) {
                                            'Available' => 'badge-available',
                                            'On Hold' => 'badge-onhold',
                                            'Booked' => 'badge-booked',
                                            'Sold' => 'badge-sold',
                                            default => 'badge-blocked',
                                        };
                                    @endphp
                                    <div class="inventory-cell {{ $badgeClass }}" 
                                         data-bs-toggle="modal" 
                                         data-bs-target="#unitModal{{ $unit->id }}"
                                         style="min-width: 110px;">
                                        <div class="fs-6">#{{ $unit->unit_number }}</div>
                                        <small class="d-block" style="font-size: 0.72rem;">{{ $unit->unit_type }} | {{ $unit->carpet_area_sqft }} sqft</small>
                                        <small class="d-block fw-bold">${{ number_format($unit->total_price, 0) }}</small>
                                    </div>

                                    <!-- Unit Detail Modal -->
                                    <div class="modal fade" id="unitModal{{ $unit->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title brand-font">Unit #{{ $unit->unit_number }} - {{ $unit->unit_type }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="badge {{ $badgeClass }} px-3 py-2 fs-6">{{ $unit->status }}</span>
                                                        <span class="fw-bold fs-5 text-primary">${{ number_format($unit->total_price, 2) }}</span>
                                                    </div>
                                                    
                                                    <div class="row g-2 mb-3 text-secondary small">
                                                        <div class="col-6"><strong>Project:</strong> {{ $activeProject->name }}</div>
                                                        <div class="col-6"><strong>Building:</strong> {{ $building->name }}</div>
                                                        <div class="col-6"><strong>Floor:</strong> Floor {{ $floor->floor_number }}</div>
                                                        <div class="col-6"><strong>Facing:</strong> {{ $unit->facing }}</div>
                                                        <div class="col-6"><strong>Carpet Area:</strong> {{ $unit->carpet_area_sqft }} sq.ft</div>
                                                        <div class="col-6"><strong>Super Builtup:</strong> {{ $unit->super_builtup_area_sqft }} sq.ft</div>
                                                        <div class="col-6"><strong>Base Rate:</strong> ${{ $unit->base_rate_per_sqft }}/sq.ft</div>
                                                    </div>

                                                    <form action="{{ route('units.update-status', $unit->id) }}" method="POST" class="mb-3">
                                                        @csrf
                                                        <label class="form-label small fw-semibold text-secondary">Quick Change Status:</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="status" class="form-select">
                                                                <option value="Available" {{ $unit->status == 'Available' ? 'selected' : '' }}>Available</option>
                                                                <option value="On Hold" {{ $unit->status == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                                                <option value="Booked" {{ $unit->status == 'Booked' ? 'selected' : '' }}>Booked</option>
                                                                <option value="Sold" {{ $unit->status == 'Sold' ? 'selected' : '' }}>Sold</option>
                                                                <option value="Blocked" {{ $unit->status == 'Blocked' ? 'selected' : '' }}>Blocked</option>
                                                            </select>
                                                            <button type="submit" class="btn btn-dark">Update</button>
                                                        </div>
                                                    </form>

                                                    @if($unit->status == 'Available')
                                                        <a href="{{ route('bookings.create', ['unit_id' => $unit->id]) }}" class="btn btn-primary w-100 py-2 fw-semibold">
                                                            <i class="bi bi-file-earmark-plus me-1"></i> Proceed to Book This Unit
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-secondary small italic">No units registered on this floor.</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-building-x fs-1 text-secondary"></i>
            <h5 class="mt-2">No buildings found for this project.</h5>
        </div>
    @endforelse
@else
    <div class="text-center py-5 bg-white rounded-4 border">
        <h5 class="text-secondary">Please create a project to view the inventory matrix.</h5>
        <a href="{{ route('projects.create') }}" class="btn btn-primary mt-2">Create New Project</a>
    </div>
@endif
@endsection

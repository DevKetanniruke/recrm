@extends('layouts.app')

@section('title', 'Site Visit Operations & Logistics')
@section('page-title', 'Site Visits Logistics')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Site Visit Operations & Transport Logistics</h4>
            <p class="text-muted small mb-0">Track site walkthrough appointments, assign transport cabs, verify GPS check-ins, and collect unit ratings.</p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal">
                <i class="bi bi-plus-lg me-1"></i> Schedule Site Visit
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('site-visits.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search lead or mobile..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Driver Assigned" {{ request('status') == 'Driver Assigned' ? 'selected' : '' }}>Driver Assigned</option>
                        <option value="In Transit" {{ request('status') == 'In Transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="Checked In" {{ request('status') == 'Checked In' ? 'selected' : '' }}>Checked In</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">All Escort Agents</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->id }}" {{ request('assigned_to') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>Visit #</th>
                        <th>Lead Customer</th>
                        <th>Project Site</th>
                        <th>Scheduled Date/Time</th>
                        <th>Transportation</th>
                        <th>GPS Check-In</th>
                        <th>Escort Agent</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siteVisits as $visit)
                        <tr>
                            <td>
                                <a href="{{ route('site-visits.show', $visit->id) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $visit->visit_number ?? ('SV-' . $visit->id) }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('leads.show', $visit->lead_id) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $visit->lead?->full_name }}
                                </a>
                                <div><small class="text-muted"><i class="bi bi-telephone me-1"></i> {{ $visit->lead?->mobile }}</small></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><i class="bi bi-building me-1"></i> {{ $visit->project?->name }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $visit->visit_date->format('M d, Y') }}</div>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i> {{ $visit->visit_date->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($visit->transportation_type === 'Company Cab')
                                    <span class="badge bg-info text-dark"><i class="bi bi-car-front-fill me-1"></i> Cab: {{ $visit->driver_name ?? 'Assigned' }}</span>
                                    @if($visit->driver_phone)
                                        <div><small class="text-muted"><i class="bi bi-telephone-out"></i> {{ $visit->driver_phone }}</small></div>
                                    @endif
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">Self Drive</span>
                                @endif
                            </td>
                            <td>
                                @if($visit->check_in_at)
                                    <small class="text-success fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $visit->check_in_at->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted small">Not Checked In</span>
                                @endif
                            </td>
                            <td>
                                <small class="fw-semibold text-dark">{{ $visit->assignedTo?->name ?? 'Unassigned' }}</small>
                            </td>
                            <td>
                                @php
                                    $stColor = match($visit->status) {
                                        'Completed' => 'success',
                                        'Checked In' => 'info text-dark',
                                        'Driver Assigned' => 'primary',
                                        'Cancelled' => 'danger',
                                        default => 'warning text-dark'
                                    };
                                @endphp
                                <span class="badge bg-{{ $stColor }}">{{ $visit->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('site-visits.show', $visit->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="View & Walkthrough">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                                @if(in_array($visit->status, ['Scheduled', 'Driver Assigned']))
                                    <button class="btn btn-sm btn-outline-info py-0 px-2 text-dark" data-bs-toggle="modal" data-bs-target="#dispatchCabModal{{ $visit->id }}" title="Assign Cab">
                                        <i class="bi bi-car-front"></i> Cab
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Dispatch Cab -->
                        <div class="modal fade" id="dispatchCabModal{{ $visit->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <form action="{{ route('site-visits.dispatch', $visit->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-car-front-fill text-info me-2"></i> Dispatch Transport Cab</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-2">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Driver Name <span class="text-danger">*</span></label>
                                                <input type="text" name="driver_name" class="form-control form-control-sm" required placeholder="John Driver" value="{{ $visit->driver_name }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Driver Phone <span class="text-danger">*</span></label>
                                                <input type="text" name="driver_phone" class="form-control form-control-sm" required placeholder="9876543210" value="{{ $visit->driver_phone }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Pickup Location</label>
                                                <input type="text" name="pickup_location" class="form-control form-control-sm" placeholder="Client residence address" value="{{ $visit->pickup_location }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-info btn-sm text-dark px-3">Dispatch Cab</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-geo-alt fs-2 d-block mb-2 text-secondary"></i>
                                No site visits found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($siteVisits->hasPages())
            <div class="card-footer bg-white border-0 py-2">
                {{ $siteVisits->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Schedule Visit -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('site-visits.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt text-primary me-2"></i> Schedule Site Visit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Lead Customer <span class="text-danger">*</span></label>
                        <select name="lead_id" class="form-select form-select-sm" required>
                            <option value="">Select Lead</option>
                            @foreach($leads as $l)
                                <option value="{{ $l->id }}">{{ $l->full_name }} ({{ $l->mobile }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Project Site <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select form-select-sm" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Scheduled Visit Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="visit_date" class="form-control form-control-sm" required value="{{ now()->addDay()->setHour(10)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Escort Executive</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Assign to Me ({{ auth()->user()->name }})</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Transportation Mode</label>
                        <select name="transportation_type" class="form-select form-select-sm">
                            <option value="Self">Self Drive (Client arranges transport)</option>
                            <option value="Company Cab">Company Cab Pickup</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Pickup Location (If Cab)</label>
                        <input type="text" name="pickup_location" class="form-control form-control-sm" placeholder="Pickup address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Schedule Visit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

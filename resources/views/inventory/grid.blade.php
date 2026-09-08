@extends('layouts.app')

@section('title', 'Inventory Visual Grid')
@section('page-title', 'Interactive Inventory Grid & Status Matrix')

@section('content')
<!-- Filter Panel -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('inventory.grid') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label mb-0 small text-secondary fw-semibold">Target Project:</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ optional($activeProject)->id == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }} ({{ $p->city ?? 'Location' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-0 small text-secondary fw-semibold">Building / Tower:</label>
                <select name="building_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Towers</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}" {{ request('building_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-0 small text-secondary fw-semibold">Unit Type Template:</label>
                <select name="unit_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach($unitTypes as $ut)
                        <option value="{{ $ut->id }}" {{ request('unit_type_id') == $ut->id ? 'selected' : '' }}>{{ $ut->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-0 small text-secondary fw-semibold">Inventory Status:</label>
                <select name="inventory_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(['Available', 'Hold', 'Booked', 'Sold', 'Cancelled', 'Blocked'] as $st)
                        <option value="{{ $st }}" {{ request('inventory_status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label mb-0 small text-secondary fw-semibold">Facing Direction:</label>
                <select name="facing" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Facings</option>
                    <option value="East" {{ request('facing') == 'East' ? 'selected' : '' }}>East</option>
                    <option value="North-East" {{ request('facing') == 'North-East' ? 'selected' : '' }}>North-East</option>
                    <option value="North" {{ request('facing') == 'North' ? 'selected' : '' }}>North</option>
                    <option value="West" {{ request('facing') == 'West' ? 'selected' : '' }}>West</option>
                    <option value="South" {{ request('facing') == 'South' ? 'selected' : '' }}>South</option>
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-dark w-100 mt-3">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Summary Bar -->
<div class="row g-2 mb-4">
    <div class="col-auto">
        <span class="badge bg-light text-dark border px-3 py-2 fw-normal">Total: <strong>{{ $statusCounts['Total'] }} Units</strong></span>
    </div>
    <div class="col-auto">
        <span class="badge badge-available px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Available ({{ $statusCounts['Available'] }})</span>
    </div>
    <div class="col-auto">
        <span class="badge badge-onhold px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Hold ({{ $statusCounts['Hold'] }})</span>
    </div>
    <div class="col-auto">
        <span class="badge badge-booked px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Booked ({{ $statusCounts['Booked'] }})</span>
    </div>
    <div class="col-auto">
        <span class="badge badge-sold px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Sold ({{ $statusCounts['Sold'] }})</span>
    </div>
    <div class="col-auto">
        <span class="badge badge-blocked px-3 py-2"><i class="bi bi-circle-fill me-1"></i> Blocked ({{ $statusCounts['Blocked'] }})</span>
    </div>
</div>

<!-- Grid Matrix View -->
@if($activeProject)
    @forelse($activeProject->buildings as $building)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 brand-font text-dark">
                    <i class="bi bi-building me-2 text-primary"></i> {{ $building->name }}
                    <span class="badge bg-light text-secondary border fw-normal fs-6 ms-2">{{ $building->number_of_floors }} Floors</span>
                </h5>
            </div>
            <div class="card-body">
                @foreach($building->wings as $wing)
                    <h6 class="text-secondary fw-bold mt-2 mb-3 border-bottom pb-1"><i class="bi bi-diagram-2 me-1"></i> Wing: {{ $wing->name }}</h6>
                    
                    @foreach($wing->floors->sortByDesc('floor_number') as $floor)
                        <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom border-light">
                            <div class="fw-bold text-secondary text-nowrap" style="width: 100px;">
                                {{ $floor->label ?? 'Floor ' . $floor->floor_number }}
                            </div>
                            <div class="d-flex flex-wrap gap-2 flex-grow-1">
                                @forelse($floor->units as $unit)
                                    @php
                                        $currentStatus = $unit->inventory_status ?? $unit->status;
                                        $badgeClass = match($currentStatus) {
                                            'Available' => 'badge-available',
                                            'Hold' => 'badge-onhold',
                                            'Booked' => 'badge-booked',
                                            'Sold' => 'badge-sold',
                                            'Cancelled' => 'badge-sold',
                                            default => 'badge-blocked',
                                        };
                                    @endphp
                                    <div class="inventory-cell {{ $badgeClass }}" 
                                         data-bs-toggle="modal" 
                                         data-bs-target="#unitDrawerModal{{ $unit->id }}"
                                         style="min-width: 110px;">
                                        <div class="fs-6">#{{ $unit->unit_number }}</div>
                                        <small class="d-block" style="font-size: 0.72rem;">{{ $unit->bedrooms }}BHK | {{ $unit->carpet_area }} sqft</small>
                                        <small class="d-block fw-bold">${{ number_format($unit->total_price, 0) }}</small>
                                    </div>

                                    <!-- Unit Detail Drawer Modal -->
                                    <div class="modal fade text-start" id="unitDrawerModal{{ $unit->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <div>
                                                        <h5 class="modal-title brand-font text-dark mb-0">Unit #{{ $unit->unit_number }} - {{ $unit->unitType->name ?? $unit->bedrooms . ' BHK' }}</h5>
                                                        <small class="text-secondary">{{ $activeProject->project_name }} | {{ $building->name }} | {{ $wing->name }} | {{ $floor->label ?? 'Floor ' . $floor->floor_number }}</small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Nav Tabs -->
                                                    <ul class="nav nav-tabs mb-3" role="tablist">
                                                        <li class="nav-item">
                                                            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#specsTab{{ $unit->id }}">Unit Specs</button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#pricingTab{{ $unit->id }}">Pricing Breakdown</button>
                                                        </li>
                                                        <li class="nav-item">
                                                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#historyTab{{ $unit->id }}">Status History</button>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content">
                                                        <!-- Specs Tab -->
                                                        <div class="tab-pane fade show active" id="specsTab{{ $unit->id }}">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <span class="badge {{ $badgeClass }} px-3 py-2 fs-6">Status: {{ $currentStatus }}</span>
                                                                <span class="fw-bold fs-4 text-primary">${{ number_format($unit->total_price, 2) }}</span>
                                                            </div>

                                                            <div class="row g-2 mb-3 text-secondary small bg-light p-3 rounded">
                                                                <div class="col-6"><strong>Carpet Area:</strong> {{ $unit->carpet_area }} sq.ft</div>
                                                                <div class="col-6"><strong>Built-up Area:</strong> {{ $unit->built_up_area }} sq.ft</div>
                                                                <div class="col-6"><strong>Super Built-up:</strong> {{ $unit->super_built_up_area }} sq.ft</div>
                                                                <div class="col-6"><strong>Balcony / Terrace:</strong> {{ $unit->balcony_area + $unit->terrace_area }} sq.ft</div>
                                                                <div class="col-6"><strong>Bedrooms / Baths:</strong> {{ $unit->bedrooms }} Beds | {{ $unit->bathrooms }} Baths</div>
                                                                <div class="col-6"><strong>Facing Direction:</strong> {{ $unit->facing }}</div>
                                                                <div class="col-6"><strong>Parking Slot:</strong> {{ $unit->parking }}</div>
                                                                <div class="col-6"><strong>Possession Status:</strong> {{ $unit->possession_status }}</div>
                                                                <div class="col-12"><strong>RERA Unit Ref:</strong> {{ $unit->RERA_unit_number ?? 'N/A' }}</div>
                                                            </div>

                                                            <!-- Change Inventory Status Form -->
                                                            <form action="{{ route('inventory.update-status', $unit->id) }}" method="POST" class="border-top pt-3">
                                                                @csrf
                                                                <label class="form-label small fw-semibold text-secondary">Update Inventory Status:</label>
                                                                <div class="row g-2">
                                                                    <div class="col-md-5">
                                                                        <select name="inventory_status" class="form-select form-select-sm">
                                                                            <option value="Available" {{ $currentStatus == 'Available' ? 'selected' : '' }}>Available</option>
                                                                            <option value="Hold" {{ $currentStatus == 'Hold' ? 'selected' : '' }}>Hold</option>
                                                                            <option value="Booked" {{ $currentStatus == 'Booked' ? 'selected' : '' }}>Booked</option>
                                                                            <option value="Sold" {{ $currentStatus == 'Sold' ? 'selected' : '' }}>Sold</option>
                                                                            <option value="Blocked" {{ $currentStatus == 'Blocked' ? 'selected' : '' }}>Blocked</option>
                                                                            <option value="Cancelled" {{ $currentStatus == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason for status change...">
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <button type="submit" class="btn btn-sm btn-dark w-100">Update</button>
                                                                    </div>
                                                                </div>
                                                            </form>

                                                            @if($currentStatus == 'Available')
                                                                <div class="mt-3">
                                                                    <a href="{{ route('bookings.create', ['unit_id' => $unit->id]) }}" class="btn btn-primary w-100 py-2 fw-semibold">
                                                                        <i class="bi bi-file-earmark-plus me-1"></i> Reserve / Book Unit (V0.3 Integration)
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Pricing Tab -->
                                                        <div class="tab-pane fade" id="pricingTab{{ $unit->id }}">
                                                            <form action="{{ route('inventory.update-pricing', $unit->id) }}" method="POST">
                                                                @csrf
                                                                <div class="row g-2 mb-3 text-secondary small">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Rate / SqFt ($)</label>
                                                                        <input type="number" step="0.01" name="rate_per_sqft" value="{{ optional($unit->pricing)->rate_per_sqft ?? 150 }}" class="form-control form-control-sm" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Floor Rise Rate ($)</label>
                                                                        <input type="number" step="0.01" name="floor_rise_rate" value="{{ optional($unit->pricing)->floor_rise_rate ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Facing Premium ($)</label>
                                                                        <input type="number" step="0.01" name="facing_premium" value="{{ optional($unit->pricing)->facing_premium ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">PLC Amount ($)</label>
                                                                        <input type="number" step="0.01" name="plc_amount" value="{{ optional($unit->pricing)->plc_amount ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Parking Charges ($)</label>
                                                                        <input type="number" step="0.01" name="parking_charges" value="{{ optional($unit->pricing)->parking_charges ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Clubhouse ($)</label>
                                                                        <input type="number" step="0.01" name="clubhouse_charges" value="{{ optional($unit->pricing)->clubhouse_charges ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">GST (%)</label>
                                                                        <input type="number" step="0.01" name="gst_percent" value="{{ optional($unit->pricing)->gst_percent ?? 5.00 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Discount ($)</label>
                                                                        <input type="number" step="0.01" name="discount_amount" value="{{ optional($unit->pricing)->discount_amount ?? 0 }}" class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-md-4 d-flex align-items-end">
                                                                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">Calculate Pricing</button>
                                                                    </div>
                                                                </div>
                                                            </form>

                                                            @if($unit->pricing)
                                                                <div class="p-3 bg-light rounded border small">
                                                                    <div class="d-flex justify-content-between mb-1"><span>Base Price:</span><strong>${{ number_format($unit->pricing->base_price, 2) }}</strong></div>
                                                                    <div class="d-flex justify-content-between mb-1"><span>Floor Rise & Facing Premium:</span><strong>${{ number_format($unit->pricing->facing_premium, 2) }}</strong></div>
                                                                    <div class="d-flex justify-content-between mb-1"><span>PLC & Parking:</span><strong>${{ number_format($unit->pricing->plc_amount + $unit->pricing->parking_charges, 2) }}</strong></div>
                                                                    <div class="d-flex justify-content-between mb-1"><span>GST ({{ $unit->pricing->gst_percent }}%):</span><strong>Calculated</strong></div>
                                                                    <div class="d-flex justify-content-between border-top pt-2 mt-2 fs-6 fw-bold text-primary">
                                                                        <span>Calculated Total Price:</span>
                                                                        <span>${{ number_format($unit->pricing->calculated_total_price, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Status History Tab -->
                                                        <div class="tab-pane fade" id="historyTab{{ $unit->id }}">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm align-middle mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Transition</th>
                                                                            <th>Reason</th>
                                                                            <th>User</th>
                                                                            <th>Timestamp</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($unit->statusHistories as $hist)
                                                                            <tr>
                                                                                <td>
                                                                                    <small class="badge bg-secondary">{{ $hist->previous_status ?? 'Initial' }}</small>
                                                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                                                    <small class="badge bg-primary">{{ $hist->new_status }}</small>
                                                                                </td>
                                                                                <td><small class="text-secondary">{{ $hist->reason ?? 'N/A' }}</small></td>
                                                                                <td><small>{{ $hist->user->name ?? 'System' }}</small></td>
                                                                                <td><small class="text-muted">{{ $hist->created_at->format('M d, g:i A') }}</small></td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="4" class="text-center py-3 text-secondary">No status transition history recorded.</td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
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
            <h5 class="mt-2 text-dark">No buildings or towers found for this project.</h5>
        </div>
    @endforelse
@else
    <div class="text-center py-5 bg-white rounded-4 border">
        <h5 class="text-secondary">Please select or create a project to view the inventory grid matrix.</h5>
    </div>
@endif
@endsection

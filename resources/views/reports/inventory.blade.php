<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Units</small>
            <h4 class="fw-bold mb-0 text-dark mt-1">{{ number_format($reportData['totalUnits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Available</small>
            <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($reportData['available']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">On Hold</small>
            <h4 class="fw-bold mb-0 text-warning mt-1">{{ number_format($reportData['hold']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Booked</small>
            <h4 class="fw-bold mb-0 text-info mt-1">{{ number_format($reportData['booked']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Sold</small>
            <h4 class="fw-bold mb-0 text-danger mt-1">{{ number_format($reportData['sold']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Blocked</small>
            <h4 class="fw-bold mb-0 text-secondary mt-1">{{ number_format($reportData['blocked']) }}</h4>
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Unit #</th>
                <th>Project</th>
                <th>Building</th>
                <th>Wing</th>
                <th>Floor</th>
                <th>Unit Type</th>
                <th>Super Builtup Area</th>
                <th>Base Price (₹)</th>
                <th>Total Cost (₹)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['units'] as $unit)
                <tr>
                    <td class="fw-bold text-dark">#{{ $unit->unit_number }}</td>
                    <td>{{ $unit->project?->name ?? 'N/A' }}</td>
                    <td>{{ $unit->building?->name ?? 'N/A' }}</td>
                    <td>{{ $unit->wing?->name ?? 'N/A' }}</td>
                    <td>Floor {{ $unit->floor?->floor_number ?? 'N/A' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $unit->unitType?->name ?? 'N/A' }}</span></td>
                    <td>{{ $unit->super_builtup_area ? number_format($unit->super_builtup_area) . ' sq.ft' : 'N/A' }}</td>
                    <td>₹{{ number_format($unit->base_price, 2) }}</td>
                    <td class="fw-bold text-primary">₹{{ number_format($unit->total_price, 2) }}</td>
                    <td>
                        <span class="badge @if($unit->status == 'Available') bg-success @elseif($unit->status == 'Booked') bg-info @elseif($unit->status == 'Sold') bg-danger @elseif($unit->status == 'Hold' || $unit->status == 'On Hold') bg-warning text-dark @else bg-secondary @endif">
                            {{ $unit->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-secondary">No property units found matching query criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($reportData['units'], 'links'))
    <div class="mt-3">
        {{ $reportData['units']->links() }}
    </div>
@endif

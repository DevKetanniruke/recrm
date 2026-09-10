<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Scheduled</small>
            <h4 class="fw-bold mb-0 text-primary mt-1">{{ number_format($reportData['totalVisits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Completed</small>
            <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($reportData['completedVisits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Pending</small>
            <h4 class="fw-bold mb-0 text-warning mt-1">{{ number_format($reportData['scheduledVisits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Cancelled</small>
            <h4 class="fw-bold mb-0 text-danger mt-1">{{ number_format($reportData['cancelledVisits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">No-Show</small>
            <h4 class="fw-bold mb-0 text-secondary mt-1">{{ number_format($reportData['noShowVisits']) }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Visit-to-Booking %</small>
            <h4 class="fw-bold mb-0 text-teal mt-1" style="color:#0d9488;">{{ $reportData['visitConversionRate'] }}%</h4>
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Visit ID</th>
                <th>Lead Name</th>
                <th>Mobile</th>
                <th>Project</th>
                <th>Visit Date & Time</th>
                <th>Executive</th>
                <th>Logistics Requested</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['visits'] as $visit)
                <tr>
                    <td class="fw-bold text-secondary">#{{ $visit->id }}</td>
                    <td>
                        @if($visit->lead)
                            <a href="{{ route('leads.show', $visit->lead_id) }}" class="fw-semibold text-dark text-decoration-none">{{ $visit->lead->full_name }}</a>
                        @else
                            <span class="text-secondary">N/A</span>
                        @endif
                    </td>
                    <td>{{ $visit->lead->mobile ?? 'N/A' }}</td>
                    <td>{{ $visit->project?->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d H:i') }}</td>
                    <td>{{ $visit->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td>
                        @if($visit->cab_required)
                            <span class="badge bg-warning text-dark"><i class="bi bi-car-front-fill me-1"></i> Cab Pick-up</span>
                        @else
                            <span class="badge bg-light text-secondary border">Self Visit</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge @if($visit->status == 'Completed') bg-success @elseif($visit->status == 'Scheduled') bg-primary @elseif($visit->status == 'Cancelled') bg-danger @else bg-secondary @endif">
                            {{ $visit->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-secondary">No site visits found for selected parameters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($reportData['visits'], 'links'))
    <div class="mt-3">
        {{ $reportData['visits']->links() }}
    </div>
@endif

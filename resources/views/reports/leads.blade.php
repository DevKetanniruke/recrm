<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Filtered Leads</small>
            <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($reportData['totalCount']) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Deals Won</small>
            <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($reportData['wonCount']) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Deals Lost</small>
            <h3 class="fw-bold mb-0 text-danger mt-1">{{ number_format($reportData['lostCount']) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Lead Conversion Rate</small>
            <h3 class="fw-bold mb-0 text-primary mt-1">{{ $reportData['conversionRate'] }}%</h3>
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Lead #</th>
                <th>Customer Name</th>
                <th>Mobile / Email</th>
                <th>Source</th>
                <th>Project</th>
                <th>Assigned Executive</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Lost Reason</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['leads'] as $lead)
                <tr>
                    <td class="fw-bold text-primary">{{ $lead->lead_number }}</td>
                    <td>
                        <a href="{{ route('leads.show', $lead->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $lead->full_name }}</a>
                    </td>
                    <td>
                        <div>{{ $lead->mobile }}</div>
                        <small class="text-secondary">{{ $lead->email }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $lead->source ?? 'Direct' }}</span></td>
                    <td>{{ $lead->project?->name ?? 'N/A' }}</td>
                    <td>{{ $lead->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $lead->status }}</span></td>
                    <td>
                        <span class="badge @if($lead->priority == 'Hot') bg-danger @elseif($lead->priority == 'High') bg-warning text-dark @else bg-secondary @endif">
                            {{ $lead->priority }}
                        </span>
                    </td>
                    <td><small class="text-secondary">{{ $lead->lost_reason ?? '-' }}</small></td>
                    <td>{{ $lead->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-secondary">No leads found matching the filter parameters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($reportData['leads'], 'links'))
    <div class="mt-3">
        {{ $reportData['leads']->links() }}
    </div>
@endif

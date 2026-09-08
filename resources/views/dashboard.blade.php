@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('page-title', 'Executive Lead & Sales Dashboard')

@section('content')
<!-- Lead KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">Total Leads</small>
                <h4 class="mb-0 mt-1 brand-font text-dark fw-bold">{{ $metrics['totalLeads'] }}</h4>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary fs-5">
                <i class="bi bi-person-badge"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">New Leads</small>
                <h4 class="mb-0 mt-1 brand-font text-info fw-bold">{{ $metrics['newLeads'] }}</h4>
            </div>
            <div class="stat-icon bg-info bg-opacity-10 text-info fs-5">
                <i class="bi bi-person-plus"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">🔥 Hot Leads</small>
                <h4 class="mb-0 mt-1 brand-font text-danger fw-bold">{{ $metrics['hotLeads'] }}</h4>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger fs-5">
                <i class="bi bi-fire"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">Follow-ups Today</small>
                <h4 class="mb-0 mt-1 brand-font text-warning fw-bold">{{ $metrics['followupsToday'] }}</h4>
            </div>
            <div class="stat-icon bg-warning bg-opacity-10 text-warning fs-5">
                <i class="bi bi-calendar-event"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">Overdue Tasks</small>
                <h4 class="mb-0 mt-1 brand-font text-danger fw-bold">{{ $metrics['overdueFollowups'] }}</h4>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger fs-5">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
                <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem;">Won Deals</small>
                <h4 class="mb-0 mt-1 brand-font text-success fw-bold">{{ $metrics['wonDeals'] }}</h4>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success fs-5">
                <i class="bi bi-trophy"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Lead Conversion Funnel -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-funnel text-primary me-2"></i> Lead Conversion Funnel</h6>
            </div>
            <div class="card-body">
                <canvas id="leadFunnelChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Leads by Source Donut -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-pie-chart text-info me-2"></i> Leads by Marketing Source</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="leadSourceChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Leads by Project -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-buildings text-secondary me-2"></i> Lead Demand by Project</h6>
            </div>
            <div class="card-body">
                <canvas id="leadProjectChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Inventory Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-pie-chart text-success me-2"></i> Unit Inventory Breakdown</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="unitStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tables -->
<div class="row g-4">
    <!-- Recent Leads -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Leads</h6>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light text-primary fw-semibold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned Executive</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $lead->full_name }}</a>
                                    <div class="text-secondary small"><i class="bi bi-telephone"></i> {{ $lead->mobile }}</div>
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $lead->status }}</span></td>
                                <td>
                                    <span class="badge @if($lead->priority == 'Hot') bg-danger @elseif($lead->priority == 'High') bg-warning text-dark @else bg-secondary @endif">
                                        {{ $lead->priority }}
                                    </span>
                                </td>
                                <td class="small">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-secondary">No leads recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Site Visits -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-calendar-check me-2 text-success"></i> Upcoming Site Visits</h6>
                <a href="{{ route('site-visits.index') }}" class="btn btn-sm btn-light text-primary fw-semibold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Lead Name</th>
                            <th>Project</th>
                            <th>Visit Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingSiteVisits as $visit)
                            <tr>
                                <td>{{ $visit->lead->full_name ?? 'N/A' }}</td>
                                <td>{{ $visit->project->name ?? 'N/A' }}</td>
                                <td>{{ $visit->visit_date->format('M d, Y g:i A') }}</td>
                                <td><span class="badge bg-primary">{{ $visit->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-secondary">No site visits scheduled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Funnel Chart
        const funnelCtx = document.getElementById('leadFunnelChart').getContext('2d');
        const funnelData = @json($metrics['leadFunnel']);
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(funnelData),
                datasets: [{
                    label: 'Leads',
                    data: Object.values(funnelData),
                    backgroundColor: ['#3b82f6', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981', '#f59e0b', '#22c55e', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Source Chart
        const sourceCtx = document.getElementById('leadSourceChart').getContext('2d');
        const sourceData = @json($metrics['leadsBySource']);
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(sourceData),
                datasets: [{
                    data: Object.values(sourceData),
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#64748b']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Demand by Project Chart
        const projCtx = document.getElementById('leadProjectChart').getContext('2d');
        const projData = @json($metrics['leadsByProject']);
        new Chart(projCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(projData),
                datasets: [{
                    label: 'Leads per Project',
                    data: Object.values(projData),
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Unit Inventory Donut Chart
        const unitCtx = document.getElementById('unitStatusChart').getContext('2d');
        const unitData = @json($metrics['unitStats']);
        new Chart(unitCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(unitData),
                datasets: [{
                    data: Object.values(unitData),
                    backgroundColor: ['#10b981', '#f59e0b', '#6366f1', '#e11d48', '#64748b']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    });
</script>
@endpush
@endsection

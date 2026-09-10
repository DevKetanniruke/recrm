@extends('layouts.app')

@section('title', 'Management Dashboard')
@section('page-title', 'Executive Management Dashboard & Visual Analytics')

@section('content')
<!-- Header Banner with Quick Reports Link -->
<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded-4 shadow-sm border">
    <div>
        <h5 class="fw-bold mb-1 text-dark brand-font"><i class="bi bi-speedometer2 text-primary me-2"></i> Real Estate Executive Control Center</h5>
        <p class="text-secondary small mb-0">Real-time KPI metrics, conversion funnels, financial collections, and inventory velocity.</p>
    </div>
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill">
            <i class="bi bi-bar-chart-fill me-1"></i> Open Central Reports Hub
        </a>
    </div>
</div>

<!-- 13 Real-time KPI Cards Grid -->
<div class="row g-3 mb-4">
    <!-- Projects & Inventory Group -->
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-primary p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Projects</small>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary p-2 rounded-circle fs-6"><i class="bi bi-buildings"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-dark fw-bold">{{ number_format($metrics['totalProjects']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-dark p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Units</small>
                <div class="stat-icon bg-dark bg-opacity-10 text-dark p-2 rounded-circle fs-6"><i class="bi bi-grid-3x3-gap"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-dark fw-bold">{{ number_format($metrics['totalUnits']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-success p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Available Units</small>
                <div class="stat-icon bg-success bg-opacity-10 text-success p-2 rounded-circle fs-6"><i class="bi bi-check-circle"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-success fw-bold">{{ number_format($metrics['availableUnits']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-info p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Booked Units</small>
                <div class="stat-icon bg-info bg-opacity-10 text-info p-2 rounded-circle fs-6"><i class="bi bi-bookmark-check"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-info fw-bold">{{ number_format($metrics['bookedUnits']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-danger p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Sold Units</small>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger p-2 rounded-circle fs-6"><i class="bi bi-house-lock"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-danger fw-bold">{{ number_format($metrics['soldUnits']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-secondary p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Leads</small>
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary p-2 rounded-circle fs-6"><i class="bi bi-person-lines-fill"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-dark fw-bold">{{ number_format($metrics['totalLeads']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-warning p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Qualified Leads</small>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning p-2 rounded-circle fs-6"><i class="bi bi-star-fill"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-warning fw-bold">{{ number_format($metrics['qualifiedLeads']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-primary p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Site Visits</small>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary p-2 rounded-circle fs-6"><i class="bi bi-geo-alt-fill"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-primary fw-bold">{{ number_format($metrics['siteVisits']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-indigo p-3 bg-white rounded-3 shadow-sm" style="border-left-color: #6366f1 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Bookings</small>
                <div class="stat-icon bg-indigo bg-opacity-10 p-2 rounded-circle fs-6" style="color:#6366f1; background-color:rgba(99,102,241,0.1);"><i class="bi bi-journal-check"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font fw-bold" style="color: #6366f1;">{{ number_format($metrics['bookings']) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-teal p-3 bg-white rounded-3 shadow-sm" style="border-left-color: #0d9488 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Booking Value</small>
                <div class="stat-icon p-2 rounded-circle fs-6" style="color:#0d9488; background-color:rgba(13,148,136,0.1);"><i class="bi bi-currency-rupee"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font fw-bold" style="color:#0d9488;">₹{{ number_format($metrics['bookingValue'] / 100000, 1) }}L</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-success p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Collection</small>
                <div class="stat-icon bg-success bg-opacity-10 text-success p-2 rounded-circle fs-6"><i class="bi bi-wallet2"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-success fw-bold">₹{{ number_format($metrics['currentPeriodCollection'] / 100000, 1) }}L</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-warning p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Outstanding</small>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning p-2 rounded-circle fs-6"><i class="bi bi-clock-history"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-warning fw-bold">₹{{ number_format($metrics['outstandingPayments'] / 100000, 1) }}L</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-danger p-3 bg-white rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Overdue</small>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger p-2 rounded-circle fs-6"><i class="bi bi-exclamation-circle-fill"></i></div>
            </div>
            <h4 class="mb-0 mt-1 brand-font text-danger fw-bold">₹{{ number_format($metrics['overduePayments'] / 100000, 1) }}L</h4>
        </div>
    </div>
</div>

<!-- 8-Stage Sales Conversion Funnel Ribbon -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 brand-font fw-bold text-dark"><i class="bi bi-funnel-fill text-primary me-2"></i> 8-Stage Sales Conversion Funnel & Progression Rate</h6>
    </div>
    <div class="card-body py-2">
        <div class="row g-2 text-center align-items-center">
            @foreach($metrics['salesFunnel'] as $stageKey => $stage)
                <div class="col-md-3 col-6 mb-2">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="text-uppercase fw-bold text-secondary mb-1" style="font-size:0.7rem;">{{ $stage['stage'] }}</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stage['count'] }}</h4>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.65rem;">Conv: {{ $stage['stage_conversion'] }}%</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.65rem;">Overall: {{ $stage['overall_conversion'] }}%</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Charts Grid Section -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Sales & Booking Trends -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-graph-up text-primary me-2"></i> Booking Trends Over Time (₹)</h6>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Collection Trends -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-cash-stack text-success me-2"></i> Monthly Revenue Collections (₹)</h6>
            </div>
            <div class="card-body">
                <canvas id="collectionTrendChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart 3: Lead Sources Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-pie-chart text-info me-2"></i> Lead Sources Distribution</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="leadSourceChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 4: Inventory Status Overview -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-houses text-secondary me-2"></i> Inventory Status Overview</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="unitStatusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Activity Feeds -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Leads Activity</h6>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light text-primary fw-semibold">View Pipeline</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Lead Name</th>
                            <th>Status</th>
                            <th>Executive</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $lead->full_name }}</a>
                                    <div class="text-secondary small">{{ $lead->mobile }}</div>
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $lead->status }}</span></td>
                                <td class="small">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3 text-secondary">No leads recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-receipt me-2 text-success"></i> Recent Collections</h6>
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light text-success fw-semibold">View Payments</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $payment->receipt_number }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($payment->amount_paid, 2) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $payment->payment_mode }}</span></td>
                                <td class="small text-secondary">{{ $payment->payment_date }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-secondary">No recent payments.</td></tr>
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
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        const salesData = @json($metrics['salesTrends']);
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: Object.keys(salesData),
                datasets: [{
                    label: 'Booking Value (₹)',
                    data: Object.values(salesData),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Collection Trend Chart
        const colCtx = document.getElementById('collectionTrendChart').getContext('2d');
        const colData = @json($metrics['collectionTrends']);
        new Chart(colCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(colData),
                datasets: [{
                    label: 'Collection (₹)',
                    data: Object.values(colData),
                    backgroundColor: '#10b981',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
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

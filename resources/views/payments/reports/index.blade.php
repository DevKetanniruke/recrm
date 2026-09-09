@extends('layouts.app')

@section('title', 'Financial Management Reports & Dashboard - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Financial Management Dashboard</h3>
            <p class="text-muted small mb-0">Real-time collections, outstanding balances, project ledgers, and payment-mode distribution.</p>
        </div>
        <div>
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-journal-text me-1"></i> Collection Ledger
            </a>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle p-3 text-success me-3">
                        <i class="bi bi-calendar-check fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Today's Collections</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($todayCollections, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary me-3">
                        <i class="bi bi-graph-up fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Monthly Collections</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($monthlyCollections, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning-emphasis me-3">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Received</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($totalCollections, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle p-3 text-danger me-3">
                        <i class="bi bi-exclamation-octagon fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Overdue Outstanding</div>
                        <div class="fs-4 fw-bold text-danger">₹{{ number_format($overdueOutstanding, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Charts & Ledgers -->
    <div class="row g-4 mb-4">
        <!-- Project-Wise Outstanding Report Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building text-primary me-2"></i> Project-Wise Collection & Outstanding Ledger</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Project Name</th>
                                <th>Units</th>
                                <th>Total Agreed Value</th>
                                <th>Total Collected</th>
                                <th>Outstanding Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectsReport as $p)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $p['project_name'] }}
                                        <div class="small text-muted font-monospace">{{ $p['project_code'] }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $p['total_units'] }}</span></td>
                                    <td class="fw-semibold text-dark">₹{{ number_format($p['total_agreed'], 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($p['total_collected'], 2) }}</td>
                                    <td class="fw-bold text-danger">₹{{ number_format($p['total_outstanding'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No project ledger data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Mode Distribution Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart text-info me-2"></i> Payment Mode Usage</h5>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush">
                        @forelse($modeDistribution as $m)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 border-0 px-0">
                                <div>
                                    <span class="fw-semibold text-dark">{{ $m->payment_mode }}</span>
                                    <div class="small text-muted">{{ $m->total_count }} transaction(s)</div>
                                </div>
                                <span class="fw-bold text-success">₹{{ number_format($m->total_amount, 2) }}</span>
                            </li>
                        @empty
                            <li class="text-muted text-center py-3 small">No payment mode data logged yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking-Wise Outstanding Top Balances -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-stars text-danger me-2"></i> Top Outstanding Balances by Booking</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Booking #</th>
                        <th>Customer</th>
                        <th>Project & Unit</th>
                        <th>Package Total</th>
                        <th>Amount Paid</th>
                        <th>Balance Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookingLedger as $bkg)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $bkg['booking_number'] }}</td>
                            <td class="fw-semibold text-dark">{{ $bkg['customer_name'] }}</td>
                            <td>
                                <div class="fw-bold text-primary">Unit #{{ $bkg['unit_number'] }}</div>
                                <div class="small text-muted">{{ $bkg['project_name'] }}</div>
                            </td>
                            <td class="fw-semibold text-dark">₹{{ number_format($bkg['total_amount'], 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($bkg['paid_amount'], 2) }}</td>
                            <td class="fw-bold text-danger">₹{{ number_format($bkg['balance_due'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No outstanding booking records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

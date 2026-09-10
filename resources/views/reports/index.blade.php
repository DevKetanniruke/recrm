@extends('layouts.app')

@section('title', 'Central Reporting & Analytics')
@section('page-title', 'Multi-Dimensional CRM Reports Portal')

@section('content')
<style>
    @media print {
        .sidebar, .top-navbar, .no-print, .btn, .nav-tabs {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card, .table-responsive {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<!-- Top Header & Actions -->
<div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <div>
        <h5 class="fw-bold mb-1 text-dark brand-font"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i> Comprehensive Reports & Analytics Hub</h5>
        <p class="text-secondary small mb-0">Query, filter, slice, and export granular reports across sales, inventory, finance, and partner performance.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
        <a href="{{ route('reports.export', array_merge(request()->all(), ['type' => $tab])) }}" class="btn btn-success btn-sm shadow-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Dynamic Multi-Parameter Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 no-print">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-bold brand-font text-dark"><i class="bi bi-funnel me-2 text-primary"></i> Multi-Parameter Report Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Project</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Sales Executive</label>
                <select name="assigned_to" class="form-select form-select-sm">
                    <option value="">All Executives</option>
                    @foreach($executives as $exec)
                        <option value="{{ $exec->id }}" {{ request('assigned_to') == $exec->id ? 'selected' : '' }}>{{ $exec->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($tab == 'leads')
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Lead Source</label>
                    <input type="text" name="source" value="{{ request('source') }}" placeholder="e.g. Website, MagicBricks" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Lead Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                        <option value="Contacted" {{ request('status') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="Qualified" {{ request('status') == 'Qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="Site Visit Planned" {{ request('status') == 'Site Visit Planned' ? 'selected' : '' }}>Site Visit Planned</option>
                        <option value="Negotiation" {{ request('status') == 'Negotiation' ? 'selected' : '' }}>Negotiation</option>
                        <option value="Won" {{ request('status') == 'Won' ? 'selected' : '' }}>Won</option>
                        <option value="Lost" {{ request('status') == 'Lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
            @elseif($tab == 'site_visits')
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Visit Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="No Show" {{ request('status') == 'No Show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
            @elseif($tab == 'inventory' || $tab == 'sales')
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Unit Type</label>
                    <select name="unit_type_id" class="form-select form-select-sm">
                        <option value="">All Unit Types</option>
                        @foreach($unitTypes as $ut)
                            <option value="{{ $ut->id }}" {{ request('unit_type_id') == $ut->id ? 'selected' : '' }}>{{ $ut->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($tab == 'channel_partners')
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Channel Partner</label>
                    <select name="partner_id" class="form-select form-select-sm">
                        <option value="">All Partners</option>
                        @foreach($channelPartners as $cp)
                            <option value="{{ $cp->id }}" {{ request('partner_id') == $cp->id ? 'selected' : '' }}>{{ $cp->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($tab == 'finance')
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm">
                        <option value="">All Modes</option>
                        <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="NEFT/RTGS" {{ request('payment_mode') == 'NEFT/RTGS' ? 'selected' : '' }}>NEFT/RTGS</option>
                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI</option>
                    </select>
                </div>
            @endif

            <div class="col-12 d-flex justify-content-end gap-2 mt-3 border-top pt-3">
                <a href="{{ route('reports.index', ['tab' => $tab]) }}" class="btn btn-light btn-sm text-secondary">Reset Filters</a>
                <button type="submit" class="btn btn-primary btn-sm px-4"><i class="bi bi-search me-1"></i> Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- 7 Report Navigation Tabs -->
<ul class="nav nav-tabs mb-4 border-bottom border-2 no-print" style="font-weight: 500;">
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'leads' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'leads'])) }}">
            <i class="bi bi-person-lines-fill me-1 text-primary"></i> A. Lead Reports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'site_visits' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'site_visits'])) }}">
            <i class="bi bi-geo-alt-fill me-1 text-success"></i> B. Site Visits
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'sales' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'sales'])) }}">
            <i class="bi bi-cart-check-fill me-1 text-info"></i> C. Sales & Bookings
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'inventory' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'inventory'])) }}">
            <i class="bi bi-buildings-fill me-1 text-warning"></i> D. Inventory Breakdown
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'finance' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'finance'])) }}">
            <i class="bi bi-currency-rupee me-1 text-teal" style="color:#0d9488;"></i> E. Finance & Collections
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'channel_partners' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'channel_partners'])) }}">
            <i class="bi bi-person-badge-fill me-1 text-indigo" style="color:#6366f1;"></i> F. Channel Partners
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab == 'executives' ? 'active fw-bold border-bottom-0' : 'text-secondary' }}" href="{{ route('reports.index', array_merge(request()->all(), ['tab' => 'executives'])) }}">
            <i class="bi bi-graph-up-arrow me-1 text-danger"></i> G. Executive Performance
        </a>
    </li>
</ul>

<!-- Active Tab Content -->
@include('reports.' . $tab)

@endsection

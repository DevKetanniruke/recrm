@extends('layouts.app')

@section('title', 'Site & Material Management - ' . $project->project_name)
@section('page-title', 'Site & Material Management — ' . $project->project_name)

@section('content')
<!-- Header Banner -->
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4 bg-white p-3 rounded-4 shadow-sm border">
    <div>
        <h5 class="fw-bold mb-1 text-dark brand-font"><i class="bi bi-tools text-primary me-2"></i> Site Materials, Labour & Vendor Portal</h5>
        <p class="text-secondary small mb-0">Project: <strong class="text-dark">{{ $project->project_name }}</strong> | Record daily material entries, labour wages, and vendor cash/cheque payments.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-info text-white btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addInwardModal">
            <i class="bi bi-truck me-1"></i> + Receive Inward Stock
        </button>
        <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
            <i class="bi bi-box-seam me-1"></i> - Log Usage (Outward)
        </button>
        <button class="btn btn-success btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addLabourModal">
            <i class="bi bi-person-workspace me-1"></i> + Labour Entry
        </button>
        <button class="btn btn-dark btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addVendorPaymentModal">
            <i class="bi bi-currency-rupee me-1"></i> + Vendor Payment
        </button>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i> Export Reports
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item small" href="{{ route('projects.site-management.export-excel', [$project->id, 'export_type' => 'materials']) }}"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> Export Usage Logs (CSV)</a></li>
                <li><a class="dropdown-item small" href="{{ route('projects.site-management.export-excel', [$project->id, 'export_type' => 'labour']) }}"><i class="bi bi-file-earmark-spreadsheet text-info me-2"></i> Export Labour (CSV)</a></li>
                <li><a class="dropdown-item small" href="{{ route('projects.site-management.export-excel', [$project->id, 'export_type' => 'vendors']) }}"><i class="bi bi-file-earmark-spreadsheet text-warning me-2"></i> Export Vendor Payments (CSV)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item small" href="{{ route('projects.site-management.export-pdf', [$project->id, 'vendor_id' => $selectedVendorId]) }}" target="_blank"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Printable Vendor Statement (PDF)</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- 5 Real-Time Summary KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-primary p-3 bg-white rounded-3 shadow-sm">
            <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Material Spend</small>
            <h4 class="mb-0 mt-1 brand-font text-primary fw-bold">₹{{ number_format($totalMaterialSpend, 2) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-success p-3 bg-white rounded-3 shadow-sm">
            <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Labour Wages</small>
            <h4 class="mb-0 mt-1 brand-font text-success fw-bold">₹{{ number_format($totalLabourSpend, 2) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-info p-3 bg-white rounded-3 shadow-sm">
            <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Cash Paid (Vendors)</small>
            <h4 class="mb-0 mt-1 brand-font text-info fw-bold">₹{{ number_format($totalCashPaid, 2) }}</h4>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card border-start border-4 border-warning p-3 bg-white rounded-3 shadow-sm">
            <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Stock Items / Low Alerts</small>
            <h4 class="mb-0 mt-1 brand-font text-warning fw-bold">{{ $materialStocks->count() }} <span class="fs-6 text-danger fw-normal">({{ $lowStockItems->count() }} Low)</span></h4>
        </div>
    </div>

    <div class="col-xl-4 col-md-8 col-12">
        <div class="stat-card border-start border-4 border-danger p-3 bg-white rounded-3 shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem;">Total Vendor Dues / Billed</small>
                <h4 class="mb-0 mt-1 brand-font text-danger fw-bold">₹{{ number_format($totalRemainingDue, 2) }} <span class="fs-6 text-muted fw-normal">/ ₹{{ number_format($totalVendorBilled, 2) }}</span></h4>
            </div>
            <button class="btn btn-sm btn-light text-danger border fw-semibold" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                + New Vendor
            </button>
        </div>
    </div>
</div>

<!-- Date & Category Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('projects.site-management', $project->id) }}" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            
            <div class="col-12 mb-2 d-flex align-items-center gap-2 flex-wrap">
                <span class="small text-muted fw-bold me-1"><i class="bi bi-clock-history me-1"></i> Quick Presets:</span>
                <a href="{{ route('projects.site-management', [$project->id, 'tab' => $activeTab, 'preset' => 'today']) }}" class="btn btn-xs btn-outline-secondary rounded-pill px-3 {{ isset($preset) && $preset === 'today' ? 'active bg-secondary text-white' : '' }}">Today</a>
                <a href="{{ route('projects.site-management', [$project->id, 'tab' => $activeTab, 'preset' => 'this_week']) }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 {{ isset($preset) && $preset === 'this_week' ? 'active bg-primary text-white' : '' }}">This Week</a>
                <a href="{{ route('projects.site-management', [$project->id, 'tab' => $activeTab, 'preset' => 'this_month']) }}" class="btn btn-xs btn-outline-success rounded-pill px-3 {{ isset($preset) && $preset === 'this_month' ? 'active bg-success text-white' : '' }}">This Month</a>
                <a href="{{ route('projects.site-management', [$project->id, 'tab' => $activeTab, 'preset' => 'last_month']) }}" class="btn btn-xs btn-outline-dark rounded-pill px-3 {{ isset($preset) && $preset === 'last_month' ? 'active bg-dark text-white' : '' }}">Last Month</a>
            </div>

            <div class="col-md-3 col-6">
                <label class="form-label small text-muted mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-3 col-6">
                <label class="form-label small text-muted mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-1">Material Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($materialCategories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-6">
                <label class="form-label small text-muted mb-1">Payment Mode</label>
                <select name="payment_mode" class="form-select form-select-sm">
                    <option value="">All Modes</option>
                    <option value="Cash" {{ $selectedPaymentMode == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Cheque" {{ $selectedPaymentMode == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="NEFT/RTGS" {{ $selectedPaymentMode == 'NEFT/RTGS' ? 'selected' : '' }}>NEFT/RTGS</option>
                    <option value="UPI" {{ $selectedPaymentMode == 'UPI' ? 'selected' : '' }}>UPI</option>
                </select>
            </div>

            <div class="col-md-2 col-12 d-flex gap-2 mt-auto">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="{{ route('projects.site-management', $project->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Main Sub-Navigation Tabs -->
<ul class="nav nav-tabs nav-tabs-bordered mb-4" id="siteTabs">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'stocks' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'stocks']) }}">
            <i class="bi bi-boxes text-info me-1"></i> Live Stock Inventory ({{ $materialStocks->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'inwards' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'inwards']) }}">
            <i class="bi bi-truck text-primary me-1"></i> Inward Deliveries ({{ $materialInwards->total() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'summary' || $activeTab === 'materials' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'materials']) }}">
            <i class="bi bi-box-seam me-1"></i> Outward Usage Logs ({{ $materialEntries->total() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'labour' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'labour']) }}">
            <i class="bi bi-person-workspace me-1"></i> Daily Labour Wages ({{ $labourEntries->total() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'vendors' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'vendors']) }}">
            <i class="bi bi-people-fill me-1"></i> Vendors & Ledger ({{ $vendorPayments->total() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'matrix' ? 'active' : '' }}" href="{{ route('projects.site-management', [$project->id, 'tab' => 'matrix']) }}">
            <i class="bi bi-pie-chart-fill me-1"></i> Category Financial Summary
        </a>
    </li>
</ul>

<!-- Tab Content -->
@if($activeTab === 'stocks')
    <!-- Live Stock Inventory Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-boxes text-info me-2"></i> Live Material Stock Inventory Ledger</h6>
                <small class="text-secondary">Real-time safety threshold monitoring and stock balance control</small>
            </div>
            <button class="btn btn-sm btn-info text-white rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addInwardModal">+ Receive Inward Stock</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Material Name</th>
                        <th>Category</th>
                        <th>Current Available Stock</th>
                        <th>Safety Threshold</th>
                        <th>Stock Health Status</th>
                        <th>Last Replenished</th>
                        <th class="text-end">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialStocks as $st)
                        @php
                            $statusInfo = $st->stock_status;
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark fs-6">{{ $st->material_name }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border">{{ $st->category->name ?? 'General' }}</span></td>
                            <td>
                                <span class="fs-5 fw-bold {{ $st->isOutOfStock() ? 'text-danger' : ($st->isLowStock() ? 'text-warning' : 'text-success') }}">
                                    {{ number_format($st->current_stock_qty, 2) }}
                                </span>
                                <small class="text-muted fw-normal">{{ $st->unit_of_measure }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border font-monospace">{{ number_format($st->min_threshold_qty, 2) }} {{ $st->unit_of_measure }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusInfo['class'] }} mb-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.55rem;"></i> {{ $statusInfo['label'] }}</span>
                                <div class="progress mt-1" style="height: 6px; width: 120px;">
                                    <div class="progress-bar bg-{{ $statusInfo['pill'] }}" role="progressbar" style="width: {{ $statusInfo['percentage'] }}%"></div>
                                </div>
                            </td>
                            <td>
                                <small class="text-secondary">{{ $st->last_replenished_at ? $st->last_replenished_at->format('d M Y, h:i A') : 'No inward recorded yet' }}</small>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-xs btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#addInwardModal" onclick="document.querySelector('#addInwardModal input[name=material_name]').value='{{ $st->material_name }}';">
                                    + Inward
                                </button>
                                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal" onclick="document.querySelector('#addMaterialModal input[name=material_name]').value='{{ $st->material_name }}';">
                                    - Usage
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No stock inventory recorded yet. Click <strong>+ Receive Inward Stock</strong> to add inventory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($activeTab === 'inwards')
    <!-- Inward Deliveries Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-truck text-primary me-2"></i> Material Inward Replenishment Logs</h6>
            <button class="btn btn-sm btn-info text-white rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addInwardModal">+ Receive Stock Inward</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Received Date</th>
                        <th>Category</th>
                        <th>Material Name</th>
                        <th>Qty Received</th>
                        <th>Unit Rate (₹)</th>
                        <th>Total Cost (₹)</th>
                        <th>Supplier Vendor</th>
                        <th>Invoice / Gate Pass</th>
                        <th>Receiver</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialInwards as $inw)
                        <tr>
                            <td>{{ $inw->received_date->format('d M Y') }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border">{{ $inw->category->name ?? 'General' }}</span></td>
                            <td class="fw-bold text-dark">{{ $inw->material_name }}</td>
                            <td><span class="badge bg-success bg-opacity-10 text-success fw-bold border">+{{ number_format($inw->qty_received, 2) }} {{ $inw->unit_of_measure }}</span></td>
                            <td>₹{{ number_format($inw->unit_cost, 2) }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format($inw->total_cost, 2) }}</td>
                            <td>{{ $inw->supplierVendor->vendor_name ?? 'Direct Purchase' }}</td>
                            <td>
                                @if($inw->invoice_number)
                                    <span class="badge bg-light text-dark border font-monospace">Inv: {{ $inw->invoice_number }}</span>
                                @endif
                                @if($inw->gate_pass_number)
                                    <span class="badge bg-light text-muted border font-monospace">GP: {{ $inw->gate_pass_number }}</span>
                                @endif
                            </td>
                            <td><small class="text-secondary">{{ $inw->receiver->name ?? 'System' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No inward shipments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materialInwards->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $materialInwards->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

@elseif($activeTab === 'summary' || $activeTab === 'materials')
    <!-- Material Entries Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-box-seam text-primary me-2"></i> Category-wise Daily Material Entries</h6>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add Category</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Material Name</th>
                        <th>Quantity</th>
                        <th>Unit Rate (₹)</th>
                        <th>Total Cost (₹)</th>
                        <th>Supplier / Vendor</th>
                        <th>Ref Invoice</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialEntries as $mat)
                        <tr>
                            <td>{{ $mat->entry_date->format('d M Y') }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border">{{ $mat->category->name ?? 'General' }}</span></td>
                            <td class="fw-bold text-dark">{{ $mat->material_name }}</td>
                            <td>{{ $mat->quantity }} <small class="text-muted">{{ $mat->unit_of_measure }}</small></td>
                            <td>₹{{ number_format($mat->unit_cost, 2) }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format($mat->total_cost, 2) }}</td>
                            <td>{{ $mat->supplierVendor->vendor_name ?? 'Direct Purchase' }}</td>
                            <td class="small text-muted">{{ $mat->invoice_number ?? 'N/A' }}</td>
                            <td class="text-end">
                                <form action="{{ route('projects.materials.destroy', [$project->id, $mat->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this material entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-secondary">No material entries recorded. Click "+ Material Entry" to add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $materialEntries->links() }}
        </div>
    </div>
@elseif($activeTab === 'labour')
    <!-- Labour Entries Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-person-workspace text-success me-2"></i> Daily Labour Wage Log</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Labour Name / ID</th>
                        <th>Work Trade / Category</th>
                        <th>Days Worked</th>
                        <th>Daily Rate (₹)</th>
                        <th>Total Wages (₹)</th>
                        <th>Tower/Building</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labourEntries as $lab)
                        <tr>
                            <td>{{ $lab->work_date->format('d M Y') }}</td>
                            <td class="fw-bold text-dark">{{ $lab->labour_identifier }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $lab->work_category }}</span></td>
                            <td>{{ $lab->days_worked }} days</td>
                            <td>₹{{ number_format($lab->daily_wage_rate, 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($lab->total_wages, 2) }}</td>
                            <td class="small text-muted">{{ $lab->building->name ?? 'General Site' }}</td>
                            <td>
                                <span class="badge {{ $lab->payment_status === 'Paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $lab->payment_status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('projects.labour.destroy', [$project->id, $lab->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove labour wage log?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-secondary">No labour wage entries recorded. Click "+ Labour Entry" to add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $labourEntries->links() }}
        </div>
    </div>
@elseif($activeTab === 'vendors')
    <!-- Vendor Payments Table & Registry -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-people-fill text-warning me-2"></i> Vendor Financial Payments & Cash vs Cheque Ledger</h6>
            <button class="btn btn-sm btn-outline-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addVendorModal">+ Register Vendor</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Vendor Name</th>
                        <th>Category</th>
                        <th>Payment Mode</th>
                        <th>Ref / Cheque No</th>
                        <th>Billed Amount (₹)</th>
                        <th>Amount Paid (₹)</th>
                        <th>Recorded By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendorPayments as $pay)
                        <tr>
                            <td>{{ $pay->payment_date->format('d M Y') }}</td>
                            <td class="fw-bold text-dark">{{ $pay->vendor->vendor_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $pay->vendor->category->name ?? 'N/A' }}</span></td>
                            <td>
                                @if($pay->payment_mode === 'Cash')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold">Cash</span>
                                @elseif($pay->payment_mode === 'Cheque')
                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold">Cheque</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $pay->payment_mode }}</span>
                                @endif
                            </td>
                            <td class="small">{{ $pay->cheque_number ?? ($pay->transaction_reference ?? 'N/A') }}</td>
                            <td class="text-secondary">₹{{ number_format($pay->invoice_bill_amount, 2) }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format($pay->amount, 2) }}</td>
                            <td class="small text-muted">{{ $pay->creator->name ?? 'Admin' }}</td>
                            <td class="text-end">
                                <form action="{{ route('projects.vendor-payments.destroy', [$project->id, $pay->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove vendor payment transaction?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-secondary">No vendor payment transactions recorded. Click "+ Vendor Payment" to add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $vendorPayments->links() }}
        </div>
    </div>
@elseif($activeTab === 'matrix')
    <!-- Category Financial Summary Matrix -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-pie-chart-fill text-info me-2"></i> Category-wise Vendor Payment Summary Matrix</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Vendor Category</th>
                        <th class="text-end">Cash Paid (₹)</th>
                        <th class="text-end">Cheque Paid (₹)</th>
                        <th class="text-end">Total Billed (₹)</th>
                        <th class="text-end">Total Paid (₹)</th>
                        <th class="text-end">Remaining Due (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoryVendorSummary as $row)
                        <tr>
                            <td class="fw-bold text-dark">{{ $row->category_name }}</td>
                            <td class="text-end text-success font-monospace">₹{{ number_format($row->cash_amount, 2) }}</td>
                            <td class="text-end text-info font-monospace">₹{{ number_format($row->cheque_amount, 2) }}</td>
                            <td class="text-end text-secondary font-monospace">₹{{ number_format($row->total_billed, 2) }}</td>
                            <td class="text-end fw-bold text-dark font-monospace">₹{{ number_format($row->total_paid, 2) }}</td>
                            <td class="text-end fw-bold text-danger font-monospace">₹{{ number_format(max(0, $row->total_billed - $row->total_paid), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-secondary">No vendor transactions recorded for category analysis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- MODAL 1: Add Material Entry -->
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('projects.materials.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i> Daily Material Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Material Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($materialCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Material Name <span class="text-danger">*</span></label>
                    <input type="text" name="material_name" placeholder="e.g. Ultratech Cement / 12mm Steel" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" name="quantity" placeholder="e.g. 500" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Unit of Measure <span class="text-danger">*</span></label>
                    <select name="unit_of_measure" class="form-select" required>
                        <option value="Bags">Bags</option>
                        <option value="Brass">Brass</option>
                        <option value="Tons">Tons</option>
                        <option value="Sq.Ft">Sq.Ft</option>
                        <option value="Pieces">Pieces</option>
                        <option value="Kgs">Kgs</option>
                        <option value="Liters">Liters</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Unit Cost (₹) <span class="badge bg-light text-muted border font-monospace">Optional</span></label>
                    <input type="number" step="0.01" name="unit_cost" placeholder="e.g. 380.00 (Admin default)" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Date of Entry <span class="text-danger">*</span></label>
                    <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tower/Building (Optional)</label>
                    <select name="building_id" class="form-select">
                        <option value="">All Project / Common</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Supplier Vendor (Optional)</label>
                    <select name="supplier_vendor_id" class="form-select">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->vendor_name }} ({{ $v->category->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Invoice / Delivery Ref</label>
                    <input type="text" name="invoice_number" placeholder="e.g. INV-9921" class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Notes</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Material Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Add Labour Entry -->
<div class="modal fade" id="addLabourModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('projects.labour.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font fw-bold"><i class="bi bi-person-workspace me-2 text-success"></i> Daily Labour Wage Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold">Labour Name or Contractor Group ID <span class="text-danger">*</span></label>
                    <input type="text" name="labour_identifier" placeholder="e.g. Ramesh Kumar / Team-A Shuttering" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Work Trade / Category <span class="text-danger">*</span></label>
                    <input type="text" name="work_category" placeholder="e.g. Masonry, Shuttering, Helper" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Days Worked <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" name="days_worked" value="1.0" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Daily Wage Rate (₹) <span class="badge bg-light text-muted border font-monospace">Optional</span></label>
                    <input type="number" step="0.01" name="daily_wage_rate" placeholder="e.g. 800.00 (Admin default)" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Work Date <span class="text-danger">*</span></label>
                    <input type="date" name="work_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tower / Building</label>
                    <select name="building_id" class="form-select">
                        <option value="">General Project Work</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="Paid">Paid</option>
                        <option value="Pending" selected>Pending</option>
                        <option value="Partial">Partial</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success px-4">Save Labour Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Add Vendor Payment -->
<div class="modal fade" id="addVendorPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('projects.vendor-payments.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font fw-bold"><i class="bi bi-currency-rupee me-2 text-dark"></i> Record Vendor Payment Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" class="form-select" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->vendor_name }} ({{ $v->category->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="NEFT/RTGS">NEFT/RTGS</option>
                        <option value="UPI">UPI</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Amount Paid (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" placeholder="e.g. 50000.00" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Invoice / Bill Amount (₹)</label>
                    <input type="number" step="0.01" name="invoice_bill_amount" placeholder="Agreed invoice total" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Cheque No (Required for Cheque)</label>
                    <input type="text" name="cheque_number" placeholder="e.g. CHQ-882012" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Bank Name</label>
                    <input type="text" name="bank_name" placeholder="e.g. HDFC Bank" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Transaction Reference / UTR</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UTR-99210291" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark px-4">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: Register Vendor Master -->
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('projects.vendors.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i> Register New Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold">Vendor / Firm Name <span class="text-danger">*</span></label>
                    <input type="text" name="vendor_name" placeholder="e.g. Apex Hardware & Suppliers" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Category / Type <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($vendorCategories as $vCat)
                            <option value="{{ $vCat->id }}">{{ $vCat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contact Person</label>
                    <input type="text" name="contact_person" placeholder="e.g. Suresh Patel" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Mobile</label>
                    <input type="text" name="mobile" placeholder="Mobile Number" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Email</label>
                    <input type="email" name="email" placeholder="Email Address" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">GST Number</label>
                    <input type="text" name="gst_number" placeholder="27AAAAA0000A1Z5" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">PAN Number</label>
                    <input type="text" name="pan_number" placeholder="ABCDE1234F" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Vendor Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 5: Add Material Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('projects.material-categories.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title brand-font fw-bold"><i class="bi bi-tags me-2 text-primary"></i> Add Material Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Bandhkam, Plywoods, Steel, Bricks" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Category Code</label>
                    <input type="text" name="code" placeholder="e.g. CAT-STEEL" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 6: Receive Inward Stock Replenishment -->
<div class="modal fade" id="addInwardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('projects.inwards.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-info bg-opacity-10">
                <h5 class="modal-title brand-font fw-bold text-dark"><i class="bi bi-truck me-2 text-info"></i> Receive Inward Stock Shipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Material Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($materialCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Material Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="material_name" placeholder="e.g. Ultratech PPC Cement / 12mm TMT Steel" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Quantity Received <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" name="qty_received" placeholder="e.g. 500" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Unit of Measure <span class="text-danger">*</span></label>
                    <select name="unit_of_measure" class="form-select" required>
                        <option value="Bags">Bags</option>
                        <option value="Tons">Tons</option>
                        <option value="Brass">Brass</option>
                        <option value="Sq.Ft">Sq.Ft</option>
                        <option value="Pieces">Pieces</option>
                        <option value="Kgs">Kgs</option>
                        <option value="Liters">Liters</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Safety Min Threshold Qty</label>
                    <input type="number" step="0.001" name="min_threshold_qty" placeholder="e.g. 150 (Alert level)" class="form-control" value="10">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Unit Cost (₹)</label>
                    <input type="number" step="0.01" name="unit_cost" placeholder="e.g. 360.00" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Received Date <span class="text-danger">*</span></label>
                    <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Supplier Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Select Vendor Supplier</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->vendor_name }} ({{ $v->category->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Supplier Invoice Number</label>
                    <input type="text" name="invoice_number" placeholder="e.g. INV-99120" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Gate Pass Number</label>
                    <input type="text" name="gate_pass_number" placeholder="e.g. GP-8812" class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Notes / Delivery Condition</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional delivery notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info text-white px-4">Receive & Update Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 Real-time Stock Alerts Engine -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(isset($lowStockItems) && $lowStockItems->count() > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger non-intrusive sweet toast for low/out of stock items
        const lowCount = {{ $lowStockItems->count() }};
        const itemNames = {!! json_encode($lowStockItems->take(3)->pluck('material_name')->toArray()) !!}.join(', ');
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            confirmButtonText: '📦 View Stock Ledger',
            confirmButtonColor: '#0ea5e9',
            showCancelButton: true,
            cancelButtonText: 'Snooze 24h',
            timer: 10000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'warning',
            title: `⚠️ Stock Alert (${lowCount} Item${lowCount > 1 ? 's' : ''})`,
            html: `Low/Out-of-stock detected: <strong>${itemNames}</strong>`
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('projects.site-management', [$project->id, 'tab' => 'stocks']) }}";
            }
        });
    });
</script>
@endif
@endsection

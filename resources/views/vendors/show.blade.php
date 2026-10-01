@extends('layouts.app')

@section('title', 'Vendor Details - ' . $vendor->vendor_name)
@section('page-title', 'Vendor Ledger & Details')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb & Header Actions -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}" class="text-decoration-none">Vendors Directory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $vendor->vendor_name }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                {{ $vendor->vendor_name }}
                @if($vendor->status === 'Active')
                    <span class="badge bg-success-subtle text-success fs-6 fw-semibold rounded-pill px-3"><i class="bi bi-check-circle me-1"></i>Active</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary fs-6 fw-semibold rounded-pill px-3"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                @endif
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Vendors
            </a>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editVendorModal">
                <i class="bi bi-pencil-square me-1"></i> Edit Vendor
            </button>
        </div>
    </div>

    <!-- Overview Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Vendor Profile Info Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle-fill text-primary me-2"></i>Vendor Information</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">Category:</span>
                            <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $vendor->category->name ?? 'Unassigned' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">Contact Person:</span>
                            <span class="fw-bold text-dark">{{ $vendor->contact_person ?: '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">Mobile:</span>
                            <span class="fw-bold text-dark">{{ $vendor->mobile ?: '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">Email:</span>
                            <span class="text-dark">{{ $vendor->email ?: '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">GST Number:</span>
                            <span class="badge bg-light text-dark border">{{ $vendor->gst_number ?: 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2 px-0">
                            <span class="text-muted fw-semibold">PAN Number:</span>
                            <span class="badge bg-light text-dark border">{{ $vendor->pan_number ?: 'N/A' }}</span>
                        </li>
                        <li class="list-group-item py-2 px-0">
                            <span class="text-muted fw-semibold d-block mb-1">Office Address:</span>
                            <p class="text-dark mb-0 small">{{ $vendor->address ?: 'No physical address logged.' }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Financial Summary KPI Cards -->
        <div class="col-12 col-lg-8">
            <div class="row g-3 h-100">
                <div class="col-12 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-primary">
                        <div class="text-muted small fw-semibold text-uppercase">Total Invoiced / Billed</div>
                        <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($totalBilled, 2) }}</h3>
                        <small class="text-muted">Total invoice bill value across projects</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-success">
                        <div class="text-muted small fw-semibold text-uppercase">Total Paid Amount</div>
                        <h3 class="fw-bold mb-0 text-success">₹{{ number_format($totalPaid, 2) }}</h3>
                        <small class="text-muted">Total payments settled to vendor</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-warning">
                        <div class="text-muted small fw-semibold text-uppercase">Net Outstanding Balance</div>
                        <h3 class="fw-bold mb-0 {{ $remainingDue > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($remainingDue, 2) }}</h3>
                        <small class="text-muted">Remaining pending payment balance</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-info">
                        <div class="text-muted small fw-semibold text-uppercase">Payment Mode Breakdown</div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge bg-light text-dark border">Cash: ₹{{ number_format($cashPaid, 2) }}</span>
                            <span class="badge bg-light text-dark border">Cheque: ₹{{ number_format($chequePaid, 2) }}</span>
                            <span class="badge bg-light text-dark border">NEFT: ₹{{ number_format($neftPaid, 2) }}</span>
                            <span class="badge bg-light text-dark border">UPI: ₹{{ number_format($upiPaid, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Payment Ledger History Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Vendor Payment Ledger & Transaction History</h6>
            <span class="badge bg-light text-dark border">{{ $vendor->payments->count() }} Payment Records</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Payment Date</th>
                        <th>Project Site</th>
                        <th>Payment Mode</th>
                        <th>Ref / Cheque #</th>
                        <th>Invoice / Bill Amount</th>
                        <th>Paid Amount</th>
                        <th>Recorded By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendor->payments as $index => $pay)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                            <td class="fw-semibold small">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M, Y') }}</td>
                            <td>
                                <a href="{{ route('projects.site-management', [$pay->project_id, 'tab' => 'vendors', 'vendor_id' => $vendor->id]) }}" class="text-primary text-decoration-none fw-semibold small">
                                    {{ $pay->project->project_name ?? 'Project #' . $pay->project_id }}
                                </a>
                            </td>
                            <td>
                                @if($pay->payment_mode === 'Cash')
                                    <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-cash me-1"></i>Cash</span>
                                @elseif($pay->payment_mode === 'Cheque')
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1"><i class="bi bi-bank me-1"></i>Cheque</span>
                                @else
                                    <span class="badge bg-info-subtle text-info px-2 py-1"><i class="bi bi-phone me-1"></i>{{ $pay->payment_mode }}</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($pay->cheque_number)
                                    <div>Chq #: <strong>{{ $pay->cheque_number }}</strong></div>
                                @endif
                                @if($pay->bank_name)
                                    <div class="text-muted">Bank: {{ $pay->bank_name }}</div>
                                @endif
                                @if($pay->transaction_reference)
                                    <div>Ref: {{ $pay->transaction_reference }}</div>
                                @endif
                                @if(!$pay->cheque_number && !$pay->bank_name && !$pay->transaction_reference)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold small">₹{{ number_format($pay->invoice_bill_amount, 2) }}</td>
                            <td class="fw-bold text-success small">₹{{ number_format($pay->amount, 2) }}</td>
                            <td class="small text-muted">{{ $pay->creator->name ?? 'System' }}</td>
                            <td class="small text-muted">{{ $pay->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                No payment ledger transactions recorded for this vendor yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Vendor Modal -->
<div class="modal fade" id="editVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('vendors.update', $vendor->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Vendor: {{ $vendor->vendor_name }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vendor / Firm Name <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_name" class="form-control form-control-sm" required value="{{ $vendor->vendor_name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select form-select-sm" required>
                            @foreach(\App\Models\VendorCategory::where('company_id', auth()->user()->company_id)->orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}" {{ $vendor->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control form-control-sm" value="{{ $vendor->contact_person }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control form-control-sm" value="{{ $vendor->mobile }}">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $vendor->email }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="Active" {{ $vendor->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $vendor->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">GST Number</label>
                            <input type="text" name="gst_number" class="form-control form-control-sm" value="{{ $vendor->gst_number }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control form-control-sm" value="{{ $vendor->pan_number }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Address</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2">{{ $vendor->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Update Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

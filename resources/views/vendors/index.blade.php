@extends('layouts.app')

@section('title', 'Vendors & Suppliers Directory')
@section('page-title', 'Vendor Directory & Category Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Session Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header Actions & Overview KPI Cards -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1 brand-font text-dark"><i class="bi bi-truck-front-fill text-primary me-2"></i>Vendor & Supplier Directory</h4>
            <p class="text-muted small mb-0">Manage project vendors, contractors, category filters, contact details, and payment ledgers.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                <i class="bi bi-tags-fill"></i> Manage Categories
            </button>
            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                <i class="bi bi-plus-lg"></i> Add New Vendor
            </button>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Vendors</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalVendorsCount) }}</h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Vendors</div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($activeVendorsCount) }}</h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Vendor Categories</div>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format($totalCategoriesCount) }}</h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="bi bi-grid-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Outstanding Dues</div>
                        <h3 class="fw-bold mb-0 text-warning">₹{{ number_format($totalOutstandingDue, 2) }}</h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Quick-Filter Pill Badges -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold text-dark small text-uppercase tracking-wider"><i class="bi bi-funnel-fill text-primary me-1"></i> Filter Category-Wise:</span>
                @if($selectedCategory || $selectedStatus || $search)
                    <a href="{{ route('vendors.index') }}" class="btn btn-link btn-sm text-decoration-none p-0 text-danger"><i class="bi bi-x-circle me-1"></i>Reset All Filters</a>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('vendors.index', array_merge(request()->except('category_id', 'page'))) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ !$selectedCategory ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Categories <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $totalVendorsCount }}</span>
                </a>
                @foreach($vendorCategories as $cat)
                    <a href="{{ route('vendors.index', array_merge(request()->except('page'), ['category_id' => $cat->id])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ (string)$selectedCategory === (string)$cat->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $cat->name }} <span class="badge {{ (string)$selectedCategory === (string)$cat->id ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 rounded-pill">{{ $cat->vendors_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('vendors.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search by name, contact, mobile, GST, PAN..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Vendor Categories --</option>
                        @foreach($vendorCategories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)$selectedCategory === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="Active" {{ $selectedStatus === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $selectedStatus === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
                    <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Vendors Directory Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-stars text-primary me-2"></i>Vendor Master List</h6>
            <span class="badge bg-light text-dark border">Showing {{ $vendors->firstItem() ?? 0 }} - {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Vendor / Business Name</th>
                        <th>Category</th>
                        <th>Contact Person</th>
                        <th>Mobile / Email</th>
                        <th>GST / PAN</th>
                        <th>Billed Amount</th>
                        <th>Total Paid</th>
                        <th>Outstanding Due</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $index => $vendor)
                        @php
                            $totalBilled = $vendor->payments->sum('invoice_bill_amount');
                            $totalPaid = $vendor->payments->sum('amount');
                            $due = max(0, $totalBilled - $totalPaid);
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">{{ $vendors->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('vendors.show', $vendor->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                    {{ $vendor->vendor_name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded-pill">
                                    {{ $vendor->category->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="small">{{ $vendor->contact_person ?: '-' }}</td>
                            <td class="small">
                                <div><i class="bi bi-telephone-fill text-muted me-1"></i>{{ $vendor->mobile ?: '-' }}</div>
                                @if($vendor->email)
                                    <div class="text-muted"><i class="bi bi-envelope-fill me-1"></i>{{ $vendor->email }}</div>
                                @endif
                            </td>
                            <td class="small">
                                @if($vendor->gst_number)
                                    <div><span class="badge bg-light text-dark border">GST: {{ $vendor->gst_number }}</span></div>
                                @endif
                                @if($vendor->pan_number)
                                    <div class="mt-1"><span class="badge bg-light text-dark border">PAN: {{ $vendor->pan_number }}</span></div>
                                @endif
                                @if(!$vendor->gst_number && !$vendor->pan_number)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold small">₹{{ number_format($totalBilled, 2) }}</td>
                            <td class="fw-semibold text-success small">₹{{ number_format($totalPaid, 2) }}</td>
                            <td class="fw-bold {{ $due > 0 ? 'text-danger' : 'text-muted' }} small">
                                ₹{{ number_format($due, 2) }}
                            </td>
                            <td>
                                @if($vendor->status === 'Active')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-outline-secondary" title="View Details & Ledger">
                                        <i class="bi bi-eye-fill text-primary"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editVendorModal_{{ $vendor->id }}" title="Edit Vendor">
                                        <i class="bi bi-pencil-square text-dark"></i>
                                    </button>
                                    <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete vendor {{ addslashes($vendor->vendor_name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary text-danger" title="Delete Vendor">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Vendor Modal -->
                        <div class="modal fade" id="editVendorModal_{{ $vendor->id }}" tabindex="-1" aria-hidden="true">
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
                                                    @foreach($vendorCategories as $cat)
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
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                No vendors found matching your selected category or search filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('vendors.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-lg me-2"></i>Add New Vendor / Supplier</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vendor / Firm Name <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_name" class="form-control form-control-sm" required placeholder="e.g. Acme Cement Traders">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Category --</option>
                            @foreach($vendorCategories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)$selectedCategory === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="e.g. Ramesh Kumar">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control form-control-sm" placeholder="e.g. 9876543210">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="vendor@example.com">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">GST Number</label>
                            <input type="text" name="gst_number" class="form-control form-control-sm" placeholder="27AAAAA0000A1Z5">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control form-control-sm" placeholder="ABCDE1234F">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Address</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2" placeholder="Full office/shop address..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Save Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold"><i class="bi bi-tags-fill text-primary me-2"></i>Manage Vendor Categories</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add Category Form -->
                <form action="{{ route('vendor-categories.store') }}" method="POST" class="mb-4">
                    @csrf
                    <label class="form-label small fw-bold">Add New Vendor Category</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Glass & Aluminium Contractors">
                        <button type="submit" class="btn btn-primary px-3"><i class="bi bi-plus-lg me-1"></i>Add</button>
                    </div>
                </form>

                <h6 class="fw-bold small text-muted text-uppercase mb-2">Existing Categories</h6>
                <ul class="list-group list-group-flush border rounded">
                    @foreach($vendorCategories as $cat)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-2">
                            <div>
                                <span class="fw-semibold text-dark">{{ $cat->name }}</span>
                                <span class="badge bg-light text-muted border ms-2">{{ $cat->vendors_count }} vendors</span>
                            </div>
                            @if($cat->vendors_count === 0)
                                <form action="{{ route('vendor-categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($cat->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete Category"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            @else
                                <span class="text-muted small" title="Cannot delete category in use"><i class="bi bi-lock-fill"></i></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

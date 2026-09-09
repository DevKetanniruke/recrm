@extends('layouts.app')

@section('title', 'Customer Management - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Customer Profile Registry</h3>
            <p class="text-muted small mb-0">Manage customer records, KYC verification, co-applicants, and document vaults.</p>
        </div>
        <div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-person-plus-fill me-1"></i> Add New Customer
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-2">
                <div class="col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Search by name, customer #, mobile, email, PAN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select name="kyc_status" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All KYC Statuses --</option>
                        <option value="Pending" {{ request('kyc_status') == 'Pending' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="Verified" {{ request('kyc_status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                        <option value="Rejected" {{ request('kyc_status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 col-lg-2 d-flex">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill me-1">Filter</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Data Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Customer # & Name</th>
                        <th>Contact Details</th>
                        <th>PAN / Occupation</th>
                        <th>Co-Applicants</th>
                        <th>KYC Status</th>
                        <th>Active Bookings</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $customer->full_name }}</div>
                                <span class="badge bg-light text-secondary border small fw-normal">{{ $customer->customer_number }}</span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><i class="bi bi-telephone text-primary me-1"></i> {{ $customer->primary_mobile }}</div>
                                <div class="small text-muted"><i class="bi bi-envelope me-1"></i> {{ $customer->email }}</div>
                            </td>
                            <td>
                                <div class="small fw-bold text-uppercase">{{ $customer->PAN ?? $customer->pan_number ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $customer->occupation ?: 'N/A' }}</div>
                            </td>
                            <td>
                                @if($customer->coApplicants->count() > 0)
                                    <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1">
                                        <i class="bi bi-people me-1"></i> {{ $customer->coApplicants->count() }} Co-Applicant(s)
                                    </span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->kyc_status == 'Verified')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>
                                @elseif($customer->kyc_status == 'Rejected')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> Pending</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $customer->bookings->count() }}</span> Unit(s)
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="bi bi-eye me-1"></i> 360 View
                                </a>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-light border rounded-pill px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No customer profiles found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

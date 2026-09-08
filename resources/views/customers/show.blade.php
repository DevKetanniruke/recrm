@extends('layouts.app')

@section('title', 'Customer: ' . $customer->full_name)
@section('page-title', 'Customer Profile: ' . $customer->full_name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge @if($customer->kyc_status == 'Verified') bg-success @elseif($customer->kyc_status == 'Rejected') bg-danger @else bg-warning text-dark @endif px-3 py-1">
                        KYC {{ $customer->kyc_status }}
                    </span>
                </div>

                <h4 class="brand-font text-dark mb-1">{{ $customer->full_name }}</h4>
                <p class="text-secondary small mb-3"><i class="bi bi-telephone"></i> {{ $customer->phone }} | <i class="bi bi-envelope"></i> {{ $customer->email ?? 'N/A' }}</p>

                <div class="border-top pt-3 text-secondary small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>PAN Card Number:</span>
                        <strong class="text-dark font-monospace">{{ $customer->pan_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ID:</span>
                        <strong class="text-dark font-monospace">{{ $customer->tax_id ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Address:</span>
                        <strong class="text-dark">{{ $customer->address ?? 'N/A' }}</strong>
                    </div>
                </div>

                <!-- Update KYC Status Form -->
                <div class="border-top pt-3 mt-3">
                    <form action="{{ route('customers.update-kyc', $customer->id) }}" method="POST">
                        @csrf
                        <label class="form-label small fw-semibold text-secondary">Update KYC Status:</label>
                        <div class="input-group mb-2">
                            <select name="kyc_status" class="form-select form-select-sm">
                                <option value="Pending" {{ $customer->kyc_status == 'Pending' ? 'selected' : '' }}>Pending Verification</option>
                                <option value="Verified" {{ $customer->kyc_status == 'Verified' ? 'selected' : '' }}>Verified</option>
                                <option value="Rejected" {{ $customer->kyc_status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-dark">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Bookings History -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-file-earmark-check me-2 text-primary"></i> Property Bookings History</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking Ref</th>
                            <th>Unit Details</th>
                            <th>Total Price</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->bookings as $b)
                            <tr>
                                <td><span class="fw-bold text-dark">{{ $b->booking_number }}</span></td>
                                <td>
                                    <div class="fw-semibold">Unit #{{ $b->unit->unit_number ?? 'N/A' }}</div>
                                    <small class="text-secondary">{{ $b->unit->floor->wing->building->project->name ?? '' }}</small>
                                </td>
                                <td class="fw-semibold">${{ number_format($b->total_amount, 2) }}</td>
                                <td class="text-success fw-bold">${{ number_format($b->booking_amount_paid, 2) }}</td>
                                <td><span class="badge bg-primary">{{ $b->status }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $b->id) }}" class="btn btn-sm btn-light border">
                                        Booking Sheet <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">No bookings associated with this buyer profile yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

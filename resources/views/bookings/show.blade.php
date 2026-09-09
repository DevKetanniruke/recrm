@extends('layouts.app')

@section('title', 'Booking #' . $booking->booking_number . ' - Details')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Top Header & Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('bookings.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Bookings Pipeline</a>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h3 class="fw-bold text-dark mb-0">Booking #{{ $booking->booking_number }}</h3>
                @if($booking->status == 'Confirmed')
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Confirmed</span>
                @elseif($booking->status == 'Cancelled')
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> Cancelled</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">{{ $booking->status }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bookings.pdf', $booking->id) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4">
                <i class="bi bi-printer me-1"></i> Printable Confirmation
            </a>
            @if($booking->status != 'Cancelled')
                <button type="button" data-bs-toggle="modal" data-bs-target="#cancelBookingModal" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="bi bi-x-circle me-1"></i> Cancel Booking
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Property & Financial Summary -->
        <div class="col-lg-8">
            <!-- Property Unit Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building text-primary me-2"></i> Property & Unit Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Unit Number</label>
                            <span class="fw-bold text-primary fs-5">Unit #{{ $booking->unit?->unit_number }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Project Name</label>
                            <span class="fw-bold text-dark fs-6">{{ $booking->unit?->project?->name ?: $booking->project?->name }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Floor & Location</label>
                            <span class="fw-semibold text-dark">Floor {{ $booking->unit?->floor_number }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial & Payment Summary -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i> Agreed Financial Package</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted small d-block">Quoted Price</label>
                            <span class="fw-semibold text-dark">₹{{ number_format($booking->quoted_price, 2) }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block">Agreed Price</label>
                            <span class="fw-bold text-dark fs-6">₹{{ number_format($booking->agreed_price, 2) }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block">Taxes (GST)</label>
                            <span class="fw-semibold text-dark">₹{{ number_format($booking->tax_amount, 2) }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block">Total Value</label>
                            <span class="fw-bold text-success fs-6">₹{{ number_format($booking->total_amount, 2) }}</span>
                        </div>

                        <hr class="text-muted my-2">

                        <div class="col-md-4">
                            <label class="text-muted small d-block">Initial Token Amount Paid</label>
                            <span class="badge bg-success-subtle text-success fs-6 px-3 py-1">₹{{ number_format($booking->booking_amount_paid, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Payment Mode</label>
                            <span class="fw-semibold text-dark">{{ $booking->payment_mode ?: 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Payment Reference</label>
                            <span class="fw-semibold text-dark">{{ $booking->payment_reference ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Audit Alert if Cancelled -->
            @if($booking->status == 'Cancelled')
                <div class="card border-0 bg-danger-subtle rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Booking Cancellation Audit Record</h6>
                        <div class="small text-danger-emphasis mb-1"><strong>Cancelled Date:</strong> {{ $booking->cancelled_at ? $booking->cancelled_at->format('d M Y, h:i A') : 'N/A' }}</div>
                        <div class="small text-danger-emphasis mb-1"><strong>Cancelled By:</strong> {{ $booking->cancelledBy?->name ?: 'Authorized Staff' }}</div>
                        <div class="small text-danger-emphasis mb-1"><strong>Reason:</strong> {{ $booking->cancellation_reason }}</div>
                        <div class="small text-danger-emphasis"><strong>Refund Amount:</strong> ₹{{ number_format($booking->cancellation_refund_amount, 2) }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: Customer & Co-Applicants Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-info me-2"></i> Customer Profile</h5>
                    @if($booking->customer)
                        <a href="{{ route('customers.show', $booking->customer->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">360 Profile</a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($booking->customer)
                        <div class="mb-3">
                            <label class="text-muted small d-block">Primary Customer</label>
                            <span class="fw-bold text-dark fs-6">{{ $booking->customer->full_name }}</span>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $booking->customer->primary_mobile }}</div>
                            <div class="small text-muted"><i class="bi bi-envelope me-1"></i> {{ $booking->customer->email }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">PAN Card</label>
                            <span class="fw-bold text-uppercase">{{ $booking->customer->PAN ?? $booking->customer->pan_number ?? 'N/A' }}</span>
                        </div>
                        @if($booking->customer->coApplicants->count() > 0)
                            <div>
                                <label class="text-muted small d-block mb-1">Co-Applicants</label>
                                @foreach($booking->customer->coApplicants as $co)
                                    <div class="small bg-light p-2 rounded mb-1 border">
                                        <div class="fw-bold text-dark">{{ $co->customer_name }}</div>
                                        <div class="text-muted text-capitalize">{{ $co->relationship }} ({{ number_format($co->ownership_percentage, 2) }}%)</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-muted small">No customer linked.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
@if($booking->status != 'Cancelled')
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-octagon me-2"></i> Cancel Unit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Cancelling this booking will release Unit #{{ $booking->unit?->unit_number }} back to <strong>Available</strong> status in the inventory matrix.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="Provide clear reason for cancellation..." required></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Refund Amount (₹)</label>
                    <input type="number" step="0.01" name="cancellation_refund_amount" class="form-control rounded-pill" value="0.00">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

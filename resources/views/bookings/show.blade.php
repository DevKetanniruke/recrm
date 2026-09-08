@extends('layouts.app')

@section('title', 'Booking #' . $booking->booking_number)
@section('page-title', 'Booking Agreement Details: #' . $booking->booking_number)

@section('content')
<div class="row g-4 mb-4">
    <!-- Left Column: Booking Overview -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success px-3 py-1">{{ $booking->status }}</span>
                    <small class="text-secondary">{{ $booking->booking_date->format('M d, Y') }}</small>
                </div>

                <h5 class="brand-font text-dark mb-1">Ref: {{ $booking->booking_number }}</h5>
                <p class="text-primary fw-bold fs-4 mb-3">${{ number_format($booking->total_amount, 2) }}</p>

                <div class="border-top pt-3 text-secondary small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Buyer Name:</span>
                        <strong class="text-dark">{{ $booking->customer->full_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phone:</span>
                        <strong class="text-dark">{{ $booking->customer->phone ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Unit Assigned:</span>
                        <strong class="text-dark">Unit #{{ $booking->unit->unit_number ?? 'N/A' }} ({{ $booking->unit->unit_type ?? '' }})</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Project:</span>
                        <strong class="text-dark">{{ $booking->unit->floor->wing->building->project->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sales Agent:</span>
                        <strong class="text-dark">{{ $booking->salesAgent->name ?? 'N/A' }}</strong>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mt-3 border">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Total Paid to Date:</span>
                        <strong class="text-success">${{ number_format($booking->totalPaid(), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Balance Outstanding:</span>
                        <strong class="text-danger">${{ number_format($booking->balanceDue(), 2) }}</strong>
                    </div>
                </div>

                @if($booking->status == 'Confirmed')
                    <button class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                        Cancel Booking & Release Unit
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Payment Schedule & Financial Collections -->
    <div class="col-lg-8">
        <!-- Record Payment Quick Action -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Payment Schedule Milestones</h6>
                <button class="btn btn-sm btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="bi bi-plus-circle me-1"></i> Record Milestone Payment
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Milestone</th>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->paymentSchedules as $sch)
                            <tr>
                                <td><span class="fw-semibold text-dark">{{ $sch->milestone_name }}</span></td>
                                <td>{{ $sch->due_date->format('M d, Y') }}</td>
                                <td>${{ number_format($sch->amount_due, 2) }}</td>
                                <td class="text-success fw-semibold">${{ number_format($sch->amount_paid, 2) }}</td>
                                <td>
                                    <span class="badge @if($sch->status == 'Paid') bg-success @elseif($sch->status == 'Partially Paid') bg-warning text-dark @else bg-secondary @endif">
                                        {{ $sch->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-secondary">No milestone schedules configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recorded Payments History -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi text-success bi-receipt me-2"></i> Payment Receipts History</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt Ref</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount Paid</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $pmt)
                            <tr>
                                <td><span class="fw-bold text-dark">{{ $pmt->receipt_number }}</span></td>
                                <td>{{ $pmt->payment_date->format('M d, Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $pmt->payment_method }}</span></td>
                                <td class="fw-bold text-success">${{ number_format($pmt->amount_paid, 2) }}</td>
                                <td>
                                    <a href="{{ route('payments.receipt', $pmt->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        Print Receipt <i class="bi bi-printer ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-secondary">No payment receipts recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <div class="modal-header border-0">
                    <h5 class="modal-title brand-font">Record Milestone Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Target Milestone (Optional)</label>
                        <select name="payment_schedule_id" class="form-select">
                            <option value="">-- Auto-Allocate to Pending Milestones --</option>
                            @foreach($booking->paymentSchedules as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->milestone_name }} (Due: ${{ number_format($sch->amount_due - $sch->amount_paid, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Amount Paid ($) *</label>
                        <input type="number" step="0.01" name="amount_paid" class="form-control" placeholder="10000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="UPI">UPI / Digital</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Transaction Ref / Cheque No.</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. TXN-99882211">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cancel Booking -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title brand-font text-danger">Cancel Booking #{{ $booking->booking_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small">Cancelling this booking will release Unit #{{ $booking->unit->unit_number ?? '' }} back to <strong>Available</strong> status.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Reason for Cancellation *</label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Refund Amount ($) *</label>
                        <input type="number" step="0.01" name="cancellation_refund_amount" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger fw-semibold">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

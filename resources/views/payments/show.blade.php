@extends('layouts.app')

@section('title', 'Payment Receipt #' . $payment->receipt_number)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('payments.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Collection Ledger</a>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h3 class="fw-bold text-dark mb-0">Receipt #{{ $payment->receipt_number }}</h3>
                <span class="badge bg-light text-secondary border font-monospace fs-6">{{ $payment->payment_number }}</span>
                @if($payment->is_reversed)
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Reversed / Refunded</span>
                @else
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4">
                <i class="bi bi-printer me-1"></i> Printable Receipt
            </a>
            @if(!$payment->is_reversed)
                <button type="button" data-bs-toggle="modal" data-bs-target="#reversePaymentModal" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reverse / Refund
                </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Transaction & Allocation Breakdown -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i> Payment Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Amount Received</label>
                            <span class="fw-bold text-success fs-4">₹{{ number_format($payment->amount_paid, 2) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Payment Date</label>
                            <span class="fw-semibold text-dark fs-6">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small d-block">Payment Mode</label>
                            <span class="badge bg-light text-dark border fs-6">{{ $payment->payment_mode ?: $payment->payment_method }}</span>
                        </div>

                        <hr class="text-muted my-2">

                        <div class="col-md-6">
                            <label class="text-muted small d-block">Transaction / UTR Reference</label>
                            <span class="fw-semibold font-monospace text-dark">{{ $payment->transaction_reference ?: 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Bank / Cheque #</label>
                            <span class="fw-semibold font-monospace text-dark">{{ $payment->bank_cheque_number ?: 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Received By Staff</label>
                            <span class="fw-semibold text-dark">{{ $payment->receivedBy?->name ?: 'System' }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small d-block">Notes / Remarks</label>
                            <span class="small text-dark">{{ $payment->notes ?: 'None' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestone Allocation Ledger Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i> Milestone Allocation Ledger</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4">Milestone Name</th>
                                    <th>Due Date</th>
                                    <th>Schedule Total</th>
                                    <th>Allocated Amount</th>
                                    <th>Allocation Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payment->allocations as $alloc)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $alloc->paymentSchedule?->milestone_name }}</td>
                                        <td class="small text-muted">{{ $alloc->paymentSchedule?->due_date ? $alloc->paymentSchedule->due_date->format('d M Y') : 'N/A' }}</td>
                                        <td class="fw-semibold text-dark">₹{{ number_format($alloc->paymentSchedule?->amount_due, 2) }}</td>
                                        <td class="fw-bold text-success">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                                        <td class="small text-muted">{{ $alloc->allocated_at ? $alloc->allocated_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No allocations linked to specific schedules.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reversal Audit Record if Reversed -->
            @if($payment->is_reversed)
                <div class="card border-0 bg-danger-subtle rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Payment Reversal & Refund Audit Record</h6>
                        <div class="small text-danger-emphasis mb-1"><strong>Reversal Date:</strong> {{ $payment->reversed_at ? $payment->reversed_at->format('d M Y, h:i A') : 'N/A' }}</div>
                        <div class="small text-danger-emphasis mb-1"><strong>Reversed By:</strong> {{ $payment->reversedBy?->name ?: 'Authorized Staff' }}</div>
                        <div class="small text-danger-emphasis mb-1"><strong>Reason:</strong> {{ $payment->reversal_reason }}</div>
                        @if($payment->refunds->count() > 0)
                            <div class="small text-danger-emphasis mt-2">
                                <strong>Linked Refund Record:</strong> #{{ $payment->refunds->first()->refund_number }} (₹{{ number_format($payment->refunds->first()->refund_amount, 2) }})
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: Booking & Customer Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check text-info me-2"></i> Booking Info</h5>
                    @if($payment->booking)
                        <a href="{{ route('bookings.show', $payment->booking->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Booking</a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($payment->booking)
                        <div class="mb-3">
                            <label class="text-muted small d-block">Booking Number</label>
                            <span class="fw-bold text-dark fs-6">{{ $payment->booking->booking_number }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">Property Unit</label>
                            <span class="fw-bold text-primary">Unit #{{ $payment->booking->unit?->unit_number }}</span>
                            <div class="small text-muted">{{ $payment->booking->unit?->project?->name }}</div>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small d-block">Customer Name</label>
                            <span class="fw-bold text-dark">{{ $payment->booking->customer?->full_name }}</span>
                            <div class="small text-muted">{{ $payment->booking->customer?->primary_mobile }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reversal Modal -->
@if(!$payment->is_reversed)
<div class="modal fade" id="reversePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('payments.reverse', $payment->id) }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i> Reverse / Refund Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Reversing this payment will deduct allocated amounts from payment schedules and issue an explicit refund record (zero deletion policy).</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reversal Reason <span class="text-danger">*</span></label>
                    <textarea name="reversal_reason" class="form-control" rows="3" placeholder="Provide reason for payment reversal or cheque bounce..." required></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Refund Amount (₹)</label>
                    <input type="number" step="0.01" name="refund_amount" class="form-control rounded-pill" value="{{ $payment->amount_paid }}">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">Execute Reversal</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

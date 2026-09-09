@extends('layouts.app')

@section('title', 'Record Financial Payment - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3" x-data="{
    selectedBookingId: '{{ old('booking_id', $selectedBooking?->id ?? '') }}',
    amountPaid: {{ old('amount_paid', 50000) }},
}">
    <div class="mb-4">
        <a href="{{ route('payments.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Financial Collection Ledger</a>
        <h3 class="fw-bold text-dark mb-0">Record Payment Receipt</h3>
    </div>

    <form action="{{ route('payments.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Payment Details -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-receipt-cutoff text-success me-2"></i> Payment Receipt Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Target Booking <span class="text-danger">*</span></label>
                                <select name="booking_id" class="form-select rounded-pill" x-model="selectedBookingId" required>
                                    <option value="">-- Select Booking --</option>
                                    @foreach($bookings as $bkg)
                                        <option value="{{ $bkg->id }}">
                                            Booking #{{ $bkg->booking_number }} - {{ $bkg->customer?->full_name }} (Unit #{{ $bkg->unit?->unit_number }}) - Package: ₹{{ number_format($bkg->total_amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Amount Received (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount_paid" class="form-control rounded-pill fw-bold text-success" x-model="amountPaid" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control rounded-pill" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-select rounded-pill" required>
                                    <option value="Bank Transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="UPI">UPI / Instant Transfer</option>
                                    <option value="Card">Credit / Debit Card</option>
                                    <option value="Online Gateway">Online Gateway</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Transaction / UTR Reference #</label>
                                <input type="text" name="transaction_reference" class="form-control rounded-pill" placeholder="e.g. UTR #NEFT992019">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank / Cheque # (If Cheque)</label>
                                <input type="text" name="bank_cheque_number" class="form-control rounded-pill" placeholder="e.g. HDFC Cheque #884021">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Specific Schedule Item (Optional FIFO Fallback)</label>
                                <select name="payment_schedule_id" class="form-select rounded-pill">
                                    <option value="">-- Auto FIFO Allocation (Recommended) --</option>
                                    @if($selectedBooking)
                                        @foreach($selectedBooking->paymentSchedules as $sch)
                                            <option value="{{ $sch->id }}">
                                                {{ $sch->milestone_name }} - Due: ₹{{ number_format($sch->amount_due - $sch->amount_paid, 2) }} [{{ $sch->status }}]
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Collection Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Allocation Info Card -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-diagram-3 text-primary me-2"></i> Allocation Engine</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small">
                            Submitting this collection receipt will execute the automatic <strong>FIFO (First-In, First-Out) Allocation Engine</strong>.
                        </p>
                        <ul class="small text-muted ps-3 mb-4">
                            <li>Funds are automatically allocated against the oldest pending or overdue payment schedule items.</li>
                            <li>Partial payments update schedule outstanding balances dynamically.</li>
                            <li>Fully paid milestone schedules transition to <strong>Paid</strong> status.</li>
                            <li>A printable PDF receipt number (`REC-YYYY-XXXXX`) is issued instantly.</li>
                        </ul>

                        <button type="submit" class="btn btn-success rounded-pill px-5 py-2 w-100 fw-bold shadow">
                            <i class="bi bi-check-circle me-1"></i> Record & Issue Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

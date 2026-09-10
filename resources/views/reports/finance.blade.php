<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Agreement Value</small>
            <h4 class="fw-bold mb-0 text-dark mt-1">₹{{ number_format($reportData['totalAgreementValue'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Billed Amount</small>
            <h4 class="fw-bold mb-0 text-info mt-1">₹{{ number_format($reportData['totalBilled'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Collections Verified</small>
            <h4 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($reportData['totalCollected'], 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Overdue Balances</small>
            <h4 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($reportData['overdue'], 2) }}</h4>
        </div>
    </div>
</div>

<!-- Payment Mode Breakdown Ribbon -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-bold brand-font text-dark"><i class="bi bi-credit-card-2-front-fill me-2 text-primary"></i> Collections by Payment Mode</h6>
    </div>
    <div class="card-body py-2">
        <div class="row g-2 text-center">
            @forelse($reportData['paymentModeAnalysis'] as $mode)
                <div class="col-md-3 col-6">
                    <div class="p-2 bg-light rounded-3 border">
                        <small class="text-muted fw-bold text-uppercase d-block" style="font-size:0.65rem;">{{ $mode->payment_mode }}</small>
                        <span class="fw-bold text-dark fs-6">₹{{ number_format($mode->total_amount, 2) }}</span>
                        <small class="text-secondary d-block" style="font-size:0.7rem;">({{ $mode->count }} Txns)</small>
                    </div>
                </div>
            @empty
                <div class="col-12 py-2 text-secondary">No payment transactions recorded.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Receipt #</th>
                <th>Booking Code</th>
                <th>Customer</th>
                <th>Payment Mode</th>
                <th>Amount Paid (₹)</th>
                <th>Payment Date</th>
                <th>Transaction Ref</th>
                <th>Collected By</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['payments'] as $payment)
                <tr>
                    <td class="fw-bold text-primary">{{ $payment->receipt_number }}</td>
                    <td>
                        @if($payment->booking)
                            <a href="{{ route('bookings.show', $payment->booking_id) }}" class="text-decoration-none fw-semibold">{{ $payment->booking->booking_code }}</a>
                        @else
                            <span class="text-secondary">N/A</span>
                        @endif
                    </td>
                    <td>{{ $payment->booking?->customer?->full_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $payment->payment_mode }}</span></td>
                    <td class="fw-bold text-success">₹{{ number_format($payment->amount_paid, 2) }}</td>
                    <td>{{ $payment->payment_date }}</td>
                    <td><small class="text-secondary">{{ $payment->transaction_reference ?? '-' }}</small></td>
                    <td>{{ $payment->receivedBy?->name ?? 'System' }}</td>
                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ $payment->status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-secondary">No payment records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($reportData['payments'], 'links'))
    <div class="mt-3">
        {{ $reportData['payments']->links() }}
    </div>
@endif

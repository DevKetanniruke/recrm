<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $payment->receipt_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: sans-serif; padding: 2rem; }
        .receipt-card { background: #fff; border-radius: 1rem; border: 1px solid #e2e8f0; max-width: 750px; margin: auto; padding: 2.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { border: none; shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h4 class="fw-bold mb-0 text-primary">{{ $payment->company->name ?? 'PropFlow Real Estate' }}</h4>
                <small class="text-secondary">{{ $payment->company->address ?? 'Corporate Developer Office' }}</small>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-dark mb-0">OFFICIAL RECEIPT</h5>
                <span class="badge bg-success">Status: {{ $payment->status }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <small class="text-secondary d-block">Received From:</small>
                <h6 class="fw-bold text-dark mb-0">{{ $payment->booking->customer->full_name ?? 'N/A' }}</h6>
                <small class="text-secondary">Phone: {{ $payment->booking->customer->phone ?? '' }}</small>
            </div>
            <div class="col-6 text-end">
                <small class="text-secondary d-block">Receipt Ref:</small>
                <h6 class="fw-bold text-dark font-monospace mb-0">{{ $payment->receipt_number }}</h6>
                <small class="text-secondary">Date: {{ $payment->payment_date->format('M d, Y') }}</small>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>Description / Details</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="fw-bold">Payment against Booking #{{ $payment->booking->booking_number }}</div>
                        <small class="text-secondary">
                            Project: {{ $payment->booking->unit->floor->wing->building->project->name ?? 'N/A' }} | 
                            Unit #{{ $payment->booking->unit->unit_number ?? 'N/A' }}
                        </small>
                        @if($payment->paymentSchedule)
                            <div class="small text-muted">Milestone: {{ $payment->paymentSchedule->milestone_name }}</div>
                        @endif
                    </td>
                    <td class="text-end fw-bold text-dark">${{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="row mb-4">
            <div class="col-6">
                <small class="text-secondary d-block">Payment Method:</small>
                <strong class="text-dark">{{ $payment->payment_method }}</strong>
                @if($payment->transaction_reference)
                    <div class="small text-secondary">Ref: {{ $payment->transaction_reference }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <small class="text-secondary d-block">Total Received Amount:</small>
                <h3 class="fw-bold text-success mb-0">${{ number_format($payment->amount_paid, 2) }}</h3>
            </div>
        </div>

        <div class="border-top pt-4 mt-5 d-flex justify-content-between align-items-end">
            <div>
                <small class="text-secondary d-block">Issued By: {{ $payment->receivedBy->name ?? 'System Officer' }}</small>
                <small class="text-muted" style="font-size:0.75rem;">This is a computer-generated receipt.</small>
            </div>
            <div class="text-center" style="width: 150px;">
                <div class="border-bottom pb-4"></div>
                <small class="text-secondary mt-1 d-block">Authorized Signatory</small>
            </div>
        </div>

        <div class="text-center mt-4 pt-3 border-top no-print">
            <button onclick="window.print()" class="btn btn-primary px-4 fw-semibold">
                Print Official Receipt
            </button>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Payment Ledger - {{ $project->project_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #fff; color: #0f172a; font-size: 13px; }
        h1, h2, h3, h4, h5, .brand-font { font-family: 'Outfit', sans-serif; font-weight: 700; }
        .ledger-container { max-width: 900px; margin: 20px auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; }
        @media print {
            .no-print { display: none !important; }
            .ledger-container { border: none; padding: 0; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container text-end my-3 no-print" style="max-width: 900px;">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 shadow-sm rounded-pill">
            <i class="bi bi-printer-fill me-1"></i> Print / Download PDF
        </button>
    </div>

    <div class="ledger-container">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
            <div>
                <h3 class="brand-font text-primary mb-1"><i class="bi bi-building me-2"></i> {{ $project->project_name }}</h3>
                <div class="text-secondary small">Site Management & Vendor Financial Ledger</div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark text-white px-3 py-2 fs-6">Vendor Statement</span>
                <div class="text-muted small mt-1">Generated: {{ date('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Vendor Info Banner -->
        <div class="bg-light p-3 rounded-3 border mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Vendor Profile</div>
                    <h5 class="fw-bold text-dark mb-1">{{ $vendor->vendor_name ?? 'All Vendors' }}</h5>
                    <div class="text-secondary small">
                        @if($vendor)
                            Category: <strong>{{ $vendor->category->name ?? 'N/A' }}</strong> | Phone: {{ $vendor->mobile ?? 'N/A' }} <br>
                            GST: {{ $vendor->gst_number ?? 'N/A' }} | PAN: {{ $vendor->pan_number ?? 'N/A' }}
                        @else
                            Showing cumulative payments across all project contractors & suppliers.
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Financial Summary</div>
                    <div class="d-flex justify-content-md-end gap-3 mt-1">
                        <div>
                            <small class="text-muted d-block">Cash Paid</small>
                            <span class="fw-bold text-success">₹{{ number_format($totalCash, 2) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Cheque Paid</small>
                            <span class="fw-bold text-info">₹{{ number_format($totalCheque, 2) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Paid</small>
                            <span class="fw-bold text-dark">₹{{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Balance Due</small>
                            <span class="fw-bold text-danger">₹{{ number_format($remainingDue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <h6 class="fw-bold brand-font text-dark mb-3"><i class="bi bi-receipt me-1"></i> Payment Transaction History</h6>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Vendor Name</th>
                    <th>Category</th>
                    <th>Payment Mode</th>
                    <th>Ref / Cheque No</th>
                    <th class="text-end">Billed (₹)</th>
                    <th class="text-end">Amount Paid (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td class="fw-semibold text-dark">{{ $p->vendor->vendor_name ?? 'N/A' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->vendor->category->name ?? 'N/A' }}</span></td>
                        <td>
                            @if($p->payment_mode === 'Cash')
                                <span class="badge bg-success bg-opacity-10 text-success">Cash</span>
                            @elseif($p->payment_mode === 'Cheque')
                                <span class="badge bg-info bg-opacity-10 text-info">Cheque</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $p->payment_mode }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $p->cheque_number ?? ($p->transaction_reference ?? 'N/A') }}</td>
                        <td class="text-end text-secondary">₹{{ number_format($p->invoice_bill_amount, 2) }}</td>
                        <td class="text-end fw-bold text-dark">₹{{ number_format($p->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-secondary">No payment transactions recorded.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">Total Billed & Paid Aggregate:</td>
                    <td class="text-end text-secondary">₹{{ number_format($totalBilled, 2) }}</td>
                    <td class="text-end text-dark">₹{{ number_format($totalPaid, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
            <div>PropFlow Real Estate CRM &copy; {{ date('Y') }} — Site Management Module</div>
            <div>Authorized Signature: _______________________</div>
        </div>
    </div>
</body>
</html>

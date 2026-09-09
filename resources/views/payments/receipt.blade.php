<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 40px; font-size: 13px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #14532d; margin-bottom: 5px; text-transform: uppercase; }
        .company-meta { font-size: 11px; color: #64748b; }
        .doc-title { font-size: 18px; font-weight: bold; text-align: center; background: #f0fdf4; color: #166534; padding: 8px; border-radius: 4px; margin-bottom: 25px; letter-spacing: 1px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-top: 20px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: bold; color: #475569; font-size: 11px; text-uppercase; }
        .amount-box { font-size: 22px; font-weight: bold; color: #15803d; text-align: right; }
        .footer { margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 11px; text-align: center; color: #64748b; }
        .signatures { margin-top: 60px; display: flex; justify-content: space-between; }
        .sig-box { width: 45%; text-align: center; border-top: 1px dashed #94a3b8; padding-top: 8px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Acme Real Estate Developers' }}</div>
        <div class="company-meta">
            {{ $company->legal_name ?: $company->name }} | {{ $company->email ?: 'contact@company.com' }}
            @if(isset($settings['RERA_NUMBER'])) | RERA Reg No: {{ $settings['RERA_NUMBER'] }} @endif
        </div>
    </div>

    <div class="doc-title">OFFICIAL PAYMENT COLLECTION RECEIPT</div>

    <table>
        <tr>
            <td width="50%">
                <strong>Receipt Number:</strong> {{ $payment->receipt_number }}<br>
                <strong>Payment Number:</strong> {{ $payment->payment_number }}<br>
                <strong>Payment Date:</strong> {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : date('d M Y') }}
            </td>
            <td width="50%">
                <strong>Booking Number:</strong> {{ $payment->booking?->booking_number }}<br>
                <strong>Project & Unit:</strong> {{ $payment->booking?->unit?->project?->name }} (Unit #{{ $payment->booking?->unit?->unit_number }})<br>
                <strong>Received By:</strong> {{ $payment->receivedBy?->name ?: 'Accounts Officer' }}
            </td>
        </tr>
    </table>

    <div class="section-title">PAYEE & INSTRUMENT DETAILS</div>
    <table>
        <tr>
            <th width="30%">Customer Name</th>
            <th width="30%">Payment Mode / Instrument</th>
            <th width="40%">Transaction Reference / Cheque #</th>
        </tr>
        <tr>
            <td><strong>{{ $payment->booking?->customer?->full_name ?: $payment->customer?->full_name }}</strong></td>
            <td>{{ $payment->payment_mode ?: $payment->payment_method }}</td>
            <td>{{ $payment->transaction_reference ?: ($payment->bank_cheque_number ?: 'N/A') }}</td>
        </tr>
    </table>

    <div class="section-title">COLLECTION & BALANCE SUMMARY</div>
    <table>
        <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount (INR ₹)</th>
        </tr>
        <tr>
            <td>Total Agreed Agreement Value</td>
            <td style="text-align: right;">₹{{ number_format($financialSummary['total_package_value'] ?? $payment->booking?->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Total Cumulative Received to Date</td>
            <td style="text-align: right; color: #15803d; font-weight: bold;">₹{{ number_format($financialSummary['total_received_amount'] ?? $payment->booking?->totalPaid(), 2) }}</td>
        </tr>
        <tr>
            <td>Current Outstanding Balance Due</td>
            <td style="text-align: right; color: #dc2626;">₹{{ number_format($financialSummary['total_outstanding_amount'] ?? $payment->booking?->balanceDue(), 2) }}</td>
        </tr>
        <tr style="background: #f0fdf4;">
            <td style="font-size: 14px; font-weight: bold; color: #166534;">AMOUNT RECEIVED IN THIS RECEIPT</td>
            <td class="amount-box">₹{{ number_format($payment->amount_paid, 2) }}</td>
        </tr>
    </table>

    @if($payment->allocations->count() > 0)
        <div class="section-title">MILESTONE ALLOCATION BREAKDOWN</div>
        <table>
            <tr>
                <th>Milestone Item</th>
                <th>Due Date</th>
                <th style="text-align: right;">Allocated Amount</th>
            </tr>
            @foreach($payment->allocations as $alloc)
                <tr>
                    <td>{{ $alloc->paymentSchedule?->milestone_name }}</td>
                    <td>{{ $alloc->paymentSchedule?->due_date ? $alloc->paymentSchedule->due_date->format('d M Y') : 'N/A' }}</td>
                    <td style="text-align: right; font-weight: bold;">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div style="margin-top: 40px;">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none;" class="sig-box">Customer Signature</td>
                <td style="border: none;" class="sig-box">Authorized Cashier / Accountant</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Computer generated receipt. Issued by {{ $company->name }}. Subject to realization of cheque / electronic transfer.
    </div>
</body>
</html>

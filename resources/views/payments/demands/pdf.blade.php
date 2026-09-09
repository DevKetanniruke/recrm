<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Demand Notice - {{ $demand->demand_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 40px; font-size: 13px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #dc2626; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #991b1b; margin-bottom: 5px; text-transform: uppercase; }
        .company-meta { font-size: 11px; color: #64748b; }
        .doc-title { font-size: 18px; font-weight: bold; text-align: center; background: #fef2f2; color: #991b1b; padding: 8px; border-radius: 4px; margin-bottom: 25px; letter-spacing: 1px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-top: 20px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: bold; color: #475569; font-size: 11px; text-uppercase; }
        .amount-box { font-size: 20px; font-weight: bold; color: #dc2626; text-align: right; }
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

    <div class="doc-title">OFFICIAL PAYMENT DEMAND NOTICE</div>

    <table>
        <tr>
            <td width="50%">
                <strong>Demand Notice #:</strong> {{ $demand->demand_number }}<br>
                <strong>Demand Date:</strong> {{ $demand->demand_date ? $demand->demand_date->format('d M Y') : date('d M Y') }}<br>
                <strong>Payment Due Date:</strong> <span style="color: #dc2626; font-weight: bold;">{{ $demand->due_date ? $demand->due_date->format('d M Y') : 'N/A' }}</span>
            </td>
            <td width="50%">
                <strong>Booking #:</strong> {{ $demand->booking?->booking_number }}<br>
                <strong>Project & Unit:</strong> {{ $demand->booking?->unit?->project?->name }} (Unit #{{ $demand->booking?->unit?->unit_number }})<br>
                <strong>Customer:</strong> {{ $demand->customer?->full_name ?: $demand->booking?->customer?->full_name }}
            </td>
        </tr>
    </table>

    <div class="section-title">PAYMENT REQUEST DEMAND BREAKDOWN</div>
    <table>
        <tr>
            <th>Milestone / Description</th>
            <th style="text-align: right;">Demanded Amount (INR ₹)</th>
        </tr>
        <tr>
            <td>
                {{ $demand->paymentSchedule?->milestone_name ?: 'Payment Schedule Milestone' }}
                @if($demand->notes) <br><small style="color: #64748b;">{{ $demand->notes }}</small> @endif
            </td>
            <td style="text-align: right; font-weight: bold;">₹{{ number_format($demand->demand_amount, 2) }}</td>
        </tr>
        @if($demand->penalty_amount > 0)
            <tr>
                <td>Late Payment Interest / Penalty</td>
                <td style="text-align: right; color: #dc2626;">₹{{ number_format($demand->penalty_amount, 2) }}</td>
            </tr>
        @endif
        <tr style="background: #fef2f2;">
            <td style="font-size: 14px; font-weight: bold; color: #991b1b;">TOTAL DEMANDED AMOUNT PAYABLE</td>
            <td class="amount-box">₹{{ number_format($demand->demand_amount + $demand->penalty_amount, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">PAYMENT INSTRUCTIONS & BANK DETAILS</div>
    <div style="background: #f8fafc; padding: 15px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 12px;">
        Kindly remit payment via Cheque / NEFT / RTGS / Online Transfer before the due date <strong>{{ $demand->due_date ? $demand->due_date->format('d M Y') : 'N/A' }}</strong>.<br>
        <strong>Beneficiary Name:</strong> {{ $company->name }}<br>
        <strong>Bank Name:</strong> {{ $settings['BANK_NAME'] ?? 'HDFC Bank Ltd' }}<br>
        <strong>Account Number:</strong> {{ $settings['BANK_ACCOUNT_NUMBER'] ?? '50200019283819' }}<br>
        <strong>IFSC Code:</strong> {{ $settings['BANK_IFSC_CODE'] ?? 'HDFC0001234' }}
    </div>

    <div style="margin-top: 40px;">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none;" class="sig-box">Customer Acknowledgment</td>
                <td style="border: none;" class="sig-box">Authorized Financial Controller</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Issued by {{ $company->name }}. Payment due strictly per terms of agreement.
    </div>
</body>
</html>

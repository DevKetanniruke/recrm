<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation - {{ $booking->booking_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 40px; font-size: 13px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1e3a8a; margin-bottom: 5px; text-uppercase; }
        .company-meta { font-size: 11px; color: #64748b; }
        .doc-title { font-size: 18px; font-weight: bold; text-align: center; background: #f1f5f9; padding: 8px; border-radius: 4px; margin-bottom: 25px; letter-spacing: 1px; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-top: 20px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: bold; color: #475569; font-size: 11px; text-uppercase; }
        .total-row { font-weight: bold; background: #f0fdf4; font-size: 14px; }
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

    <div class="doc-title">OFFICIAL BOOKING CONFIRMATION LETTER</div>

    <table>
        <tr>
            <td width="50%">
                <strong>Booking Number:</strong> {{ $booking->booking_number }}<br>
                <strong>Booking Date:</strong> {{ $booking->booking_date ? $booking->booking_date->format('d M Y') : date('d M Y') }}<br>
                <strong>Status:</strong> {{ $booking->status }}
            </td>
            <td width="50%">
                <strong>Project:</strong> {{ $booking->unit?->project?->name ?: $booking->project?->name }}<br>
                <strong>Unit Number:</strong> Unit #{{ $booking->unit?->unit_number }} (Floor {{ $booking->unit?->floor_number }})<br>
                <strong>Sales Representative:</strong> {{ $booking->salesAgent?->name ?: 'N/A' }}
            </td>
        </tr>
    </table>

    <div class="section-title">CUSTOMER & APPLICANT DETAILS</div>
    <table>
        <tr>
            <th width="30%">Applicant Role</th>
            <th width="40%">Full Name</th>
            <th width="30%">Contact / Identification</th>
        </tr>
        <tr>
            <td><strong>Primary Applicant</strong></td>
            <td>{{ $booking->customer?->full_name }}</td>
            <td>Mob: {{ $booking->customer?->primary_mobile }}<br>PAN: {{ $booking->customer?->PAN ?: 'N/A' }}</td>
        </tr>
        @if($booking->customer?->coApplicants)
            @foreach($booking->customer->coApplicants as $co)
                <tr>
                    <td>Co-Applicant ({{ $co->relationship }})</td>
                    <td>{{ $co->customer_name }}</td>
                    <td>Mob: {{ $co->mobile ?: 'N/A' }}<br>PAN: {{ $co->pan_number ?: 'N/A' }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    <div class="section-title">AGREED FINANCIAL SUMMARY</div>
    <table>
        <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount (INR ₹)</th>
        </tr>
        <tr>
            <td>Base Quoted Package Price</td>
            <td style="text-align: right;">₹{{ number_format($booking->quoted_price, 2) }}</td>
        </tr>
        <tr>
            <td>Agreed Sale Price</td>
            <td style="text-align: right;">₹{{ number_format($booking->agreed_price, 2) }}</td>
        </tr>
        <tr>
            <td>Estimated Taxes & Statutory Charges (5% GST)</td>
            <td style="text-align: right;">₹{{ number_format($booking->tax_amount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL PACKAGE VALUE</td>
            <td style="text-align: right;">₹{{ number_format($booking->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Initial Booking Amount Received</td>
            <td style="text-align: right; color: #16a34a; font-weight: bold;">₹{{ number_format($booking->booking_amount_paid, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 40px;">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none;" class="sig-box">Customer Signature</td>
                <td style="border: none;" class="sig-box">Authorized Representative</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated booking confirmation issued by {{ $company->name }}. Subject to terms & conditions.
    </div>
</body>
</html>

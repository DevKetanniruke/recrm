<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Formal Offer Letter — {{ $offer->offer_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; padding: 40px; margin: 0; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .doc-title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th, .details-table td { padding: 10px; border: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        .details-table th { background-color: #f8fafc; font-weight: 600; }
        .terms-box { background-color: #f1f5f9; padding: 15px; border-radius: 6px; font-size: 13px; margin-bottom: 30px; }
        .signature-grid { display: flex; justify-content: space-between; margin-top: 60px; }
        .sig-box { width: 45%; border-top: 1px solid #94a3b8; pt-10; text-align: center; font-size: 14px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div>
            <div class="brand">{{ auth()->user()->company->name ?? 'Skyline Developers' }}</div>
            <small>{{ auth()->user()->company->address ?? 'Corporate Office' }}</small>
        </div>
        <div>
            <div class="doc-title">Formal Offer Letter</div>
            <small>Ref: {{ $offer->offer_number }} | Date: {{ $offer->created_at->format('M d, Y') }}</small>
        </div>
    </div>

    <table class="details-table">
        <tr>
            <th style="width: 30%;">Lead Buyer Name</th>
            <td>{{ $offer->lead?->full_name }} (Mobile: {{ $offer->lead?->mobile }})</td>
        </tr>
        <tr>
            <th>Target Unit</th>
            <td>Unit {{ $offer->unit?->unit_number }} (Project: {{ $offer->unit?->project?->name }})</td>
        </tr>
        <tr>
            <th>Original List Price</th>
            <td>₹{{ number_format($offer->original_unit_price, 2) }}</td>
        </tr>
        <tr>
            <th>Approved Offered Price</th>
            <td style="font-weight: bold; color: #059669; font-size: 16px;">₹{{ number_format($offer->offered_price, 2) }}</td>
        </tr>
        <tr>
            <th>Total Discount Granted</th>
            <td>₹{{ number_format($offer->discount_amount, 2) }} ({{ number_format($offer->discount_percentage, 1) }}%)</td>
        </tr>
        <tr>
            <th>Token Advance Agreed</th>
            <td>₹{{ number_format($offer->token_amount_offered, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Scheme</th>
            <td>{{ $offer->payment_plan_type }}</td>
        </tr>
        <tr>
            <th>Offer Validity Expiry</th>
            <td>{{ $offer->valid_until->format('M d, Y') }}</td>
        </tr>
    </table>

    <div class="terms-box">
        <strong>Terms & Conditions:</strong><br>
        1. This offer letter is valid up to {{ $offer->valid_until->format('M d, Y') }}.<br>
        2. Inventory unit status remains locked on Hold during validity duration.<br>
        3. All statutory taxes (GST, Stamp Duty, Registration) shall be charged extra as per actual government norms.<br>
        4. {{ $offer->terms_conditions }}
    </div>

    <div class="signature-grid">
        <div class="sig-box">
            <p>Authorized Builder Representative</p>
            <small>({{ $offer->approver?->name ?? $offer->creator?->name }})</small>
        </div>
        <div class="sig-box">
            <p>Buyer Acceptance Signature</p>
            <small>({{ $offer->lead?->full_name }})</small>
        </div>
    </div>
</body>
</html>

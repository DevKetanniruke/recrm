@extends('layouts.app')

@section('title', 'New Unit Booking - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3" x-data="{
    selectedUnitId: '{{ old('unit_id', $selectedUnit?->id ?? '') }}',
    quotedPrice: {{ old('quoted_price', $selectedUnit?->pricing?->calculated_total_price ?? 0) }},
    agreedPrice: {{ old('agreed_price', $selectedUnit?->pricing?->calculated_total_price ?? 0) }},
    taxAmount: 0,
    totalAmount: 0,
    updatePrices() {
        this.taxAmount = Math.round(this.agreedPrice * 0.05 * 100) / 100;
        this.totalAmount = Math.round((parseFloat(this.agreedPrice) + parseFloat(this.taxAmount)) * 100) / 100;
    }
}" x-init="updatePrices()">
    <div class="mb-4">
        <a href="{{ route('bookings.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Bookings</a>
        <h3 class="fw-bold text-dark mb-0">Create Transactional Unit Booking</h3>
    </div>

    <form action="{{ route('bookings.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Unit & Financial Details -->
            <div class="col-lg-7">
                <!-- Unit Selection Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building-check text-primary me-2"></i> 1. Select Property Unit</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Unit <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-select rounded-pill" x-model="selectedUnitId" required>
                                <option value="">-- Select Available or Hold Unit --</option>
                                @foreach($availableUnits as $unit)
                                    <option value="{{ $unit->id }}" data-price="{{ $unit->pricing?->calculated_total_price ?? 0 }}">
                                        Unit #{{ $unit->unit_number }} - {{ $unit->project?->name }} (Floor {{ $unit->floor_number }}) - ₹{{ number_format($unit->pricing?->calculated_total_price ?? 0, 2) }} [{{ $unit->status }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Financial Calculation Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-calculator text-success me-2"></i> 2. Pricing & Financial Breakdown</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Original Quoted Price (₹)</label>
                                <input type="number" step="0.01" name="quoted_price" x-model="quotedPrice" class="form-control rounded-pill" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Agreed Sale Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="agreed_price" x-model="agreedPrice" @input="updatePrices()" class="form-control rounded-pill" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estimated GST & Taxes (5%)</label>
                                <input type="number" step="0.01" name="tax_amount" x-model="taxAmount" class="form-control rounded-pill" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Final Total Package Value (₹)</label>
                                <input type="number" step="0.01" name="total_amount" x-model="totalAmount" class="form-control rounded-pill fw-bold text-success" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Initial Booking Amount Paid (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="booking_amount_paid" class="form-control rounded-pill" value="{{ old('booking_amount_paid', 50000) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Mode</label>
                                <select name="payment_mode" class="form-select rounded-pill">
                                    <option value="Cheque">Cheque</option>
                                    <option value="NEFT / RTGS">NEFT / RTGS</option>
                                    <option value="UPI / Online">UPI / Online Transfer</option>
                                    <option value="Demand Draft">Demand Draft</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Payment Reference / Cheque #</label>
                                <input type="text" name="payment_reference" class="form-control rounded-pill" placeholder="e.g. Cheque #884920 or UTR #NEFT99201">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Customer Details -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-info me-2"></i> 3. Customer Profile Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Existing Lead (Optional)</label>
                            <select name="lead_id" class="form-select rounded-pill">
                                <option value="">-- No Lead Associated --</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->lead_number }}) - {{ $lead->mobile }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="my-3 text-muted">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control rounded-pill" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control rounded-pill" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mobile <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" class="form-control rounded-pill" value="{{ old('mobile') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control rounded-pill" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Booking Date</label>
                                <input type="date" name="booking_date" class="form-control rounded-pill" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="small text-muted mb-3"><i class="bi bi-lock-fill text-warning me-1"></i> Submitting will execute a database transaction with pessimistic row-locking on the unit.</div>
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-2 w-100 fw-bold shadow">
                            <i class="bi bi-shield-check me-1"></i> Confirm & Book Unit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

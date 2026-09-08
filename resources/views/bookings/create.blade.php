@extends('layouts.app')

@section('title', 'New Booking Wizard')
@section('page-title', 'Property Unit Booking Wizard')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('bookings.store') }}" method="POST" x-data="{
            basePrice: {{ optional($selectedUnit)->total_price ?? 0 }},
            agreedPrice: {{ optional($selectedUnit)->total_price ?? 0 }},
            discount: 0,
            tax: 0,
            get totalAmount() {
                return (parseFloat(this.agreedPrice || 0) - parseFloat(this.discount || 0)) + parseFloat(this.tax || 0);
            }
        }">
            @csrf
            
            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-1-circle me-1"></i> Unit & Customer Selection</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Select Available Unit *</label>
                    <select name="unit_id" class="form-select" required x-on:change="
                        let selectedOption = $event.target.options[$event.target.selectedIndex];
                        let price = selectedOption.getAttribute('data-price') || 0;
                        basePrice = parseFloat(price);
                        agreedPrice = parseFloat(price);
                    ">
                        <option value="">-- Choose Available Unit --</option>
                        @foreach($availableUnits as $unit)
                            <option value="{{ $unit->id }}" data-price="{{ $unit->total_price }}" {{ optional($selectedUnit)->id == $unit->id ? 'selected' : '' }}>
                                Unit #{{ $unit->unit_number }} ({{ $unit->unit_type }} - {{ $unit->floor->wing->building->project->name ?? 'Project' }}) - ${{ number_format($unit->total_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Link Existing Lead (Optional)</label>
                    <select name="lead_id" class="form-select">
                        <option value="">-- Direct Customer / No Lead --</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Buyer First Name *</label>
                    <input type="text" name="customer_first_name" class="form-control" placeholder="Jane" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Buyer Last Name</label>
                    <input type="text" name="customer_last_name" class="form-control" placeholder="Smith">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Phone Number *</label>
                    <input type="text" name="customer_phone" class="form-control" placeholder="+1 555-0199" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="customer_email" class="form-control" placeholder="jane@example.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">PAN / Tax ID</label>
                    <input type="text" name="customer_pan_number" class="form-control" placeholder="ABCDE1234F">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold text-secondary">Address</label>
                    <input type="text" name="customer_address" class="form-control" placeholder="Residential Address">
                </div>
            </div>

            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-2-circle me-1"></i> Pricing & Payment Terms</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Agreed Price ($) *</label>
                    <input type="number" step="0.01" name="agreed_price" class="form-control" x-model="agreedPrice" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Discount Amount ($)</label>
                    <input type="number" step="0.01" name="discount_amount" class="form-control" x-model="discount">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Tax / Govt Fees ($)</label>
                    <input type="number" step="0.01" name="tax_amount" class="form-control" x-model="tax">
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-secondary d-block">Calculated Net Total Amount:</small>
                        <h4 class="mb-0 text-primary brand-font fw-bold">$<span x-text="totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})"></span></h4>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Booking Advance / Token Paid ($) *</label>
                    <input type="number" step="0.01" name="booking_amount_paid" class="form-control form-control-lg border-success" placeholder="e.g. 10000" required>
                    <small class="text-muted">This will automatically register as initial deposit receipt.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Booking Date</label>
                    <input type="date" name="booking_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-secondary">Special Terms & Conditions</label>
                    <textarea name="terms_conditions" class="form-control" rows="2" placeholder="e.g. 5% possession waiver, included parking slot..."></textarea>
                </div>
            </div>

            <div class="pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('bookings.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary fw-semibold px-4">Confirm & Generate Booking Sheet</button>
            </div>
        </form>
    </div>
</div>
@endsection

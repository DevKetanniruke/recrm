@extends('layouts.app')

@section('title', 'Create Formal Offer')
@section('page-title', 'Generate Property Offer')

@section('content')
<div class="container-fluid" x-data="offerForm()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-tag-fill text-primary me-2"></i> Create Formal Price Offer</h4>
            <p class="text-muted small mb-0">Submit a formal price proposal for a buyer lead and lock the unit on Hold for negotiation.</p>
        </div>
        <a href="{{ route('offers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Offers Pipeline
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('offers.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-person-badge me-2"></i> Select Buyer & Target Inventory Unit</h6></div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Lead Buyer <span class="text-danger">*</span></label>
                        <select name="lead_id" class="form-select form-select-sm" required>
                            <option value="">Select Buyer Lead</option>
                            @foreach($leads as $l)
                                <option value="{{ $l->id }}" {{ $selectedLeadId == $l->id ? 'selected' : '' }}>
                                    {{ $l->full_name }} ({{ $l->mobile }}) — Priority: {{ $l->priority }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Target Unit (Available) <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select form-select-sm" required x-model="selectedUnitId" @change="onUnitChange()">
                            <option value="">Select Unit</option>
                            @foreach($availableUnits as $u)
                                <option value="{{ $u->id }}" data-price="{{ $u->total_price ?? $u->pricing?->calculated_total_price }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                    Unit {{ $u->unit_number }} ({{ $u->project?->name }}) — List Price: ₹{{ number_format($u->total_price ?? $u->pricing?->calculated_total_price) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-calculator me-2"></i> Pricing Proposal & Discount Calculations</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Original Unit List Price (₹)</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold text-dark" readonly x-bind:value="'₹' + Number(originalPrice).toLocaleString()">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Proposed Offered Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="1000" name="offered_price" class="form-control form-control-sm fw-bold text-primary" required x-model.number="offeredPrice" @input="calculateDiscount()" placeholder="Enter offered price">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Token Advance Amount Offered (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="1000" name="token_amount_offered" class="form-control form-control-sm" required value="200000" placeholder="Token advance">
                    </div>

                    <!-- Discount Preview Card -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border border-light d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem;">Calculated Discount Amount</small>
                                <h5 class="mb-0 fw-bold text-danger" x-text="'₹' + Number(discountAmount).toLocaleString()">₹0</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem;">Discount Percentage & Routing</small>
                                <div>
                                    <span class="badge fs-6" :class="discountPercent > 12 ? 'bg-danger' : (discountPercent > 5 ? 'bg-warning text-dark' : 'bg-success')" x-text="discountPercent + '% OFF'">0% OFF</span>
                                </div>
                                <small class="text-muted" x-text="approvalRoutingText" style="font-size:0.75rem;"></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-clock-history me-2"></i> Terms & Lock Validity</h6></div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Payment Plan Scheme</label>
                        <select name="payment_plan_type" class="form-select form-select-sm">
                            <option value="Construction Linked">Construction Linked Plan (CLP)</option>
                            <option value="Downpayment">Downpayment Scheme (10/90 Plan)</option>
                            <option value="Time Linked">Time Linked Milestone Scheme</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Offer Validity Duration (Days)</label>
                        <select name="validity_days" class="form-select form-select-sm">
                            <option value="3">3 Days (Urgent)</option>
                            <option value="7" selected>7 Days (Standard)</option>
                            <option value="15">15 Days</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Special Offer Terms & Conditions</label>
                        <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Includes free covered parking slot and modular kitchen voucher..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('offers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Submit Formal Offer & Lock Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function offerForm() {
        return {
            selectedUnitId: '{{ $selectedUnitId }}',
            originalPrice: 0,
            offeredPrice: 0,
            discountAmount: 0,
            discountPercent: 0,
            approvalRoutingText: '',
            init() {
                this.onUnitChange();
            },
            onUnitChange() {
                const select = document.querySelector('select[name="unit_id"]');
                if (select && select.selectedIndex > 0) {
                    const opt = select.options[select.selectedIndex];
                    this.originalPrice = Number(opt.getAttribute('data-price')) || 0;
                    if (this.offeredPrice === 0) {
                        this.offeredPrice = this.originalPrice;
                    }
                    this.calculateDiscount();
                }
            },
            calculateDiscount() {
                if (this.originalPrice > 0 && this.offeredPrice > 0) {
                    this.discountAmount = Math.max(0, this.originalPrice - this.offeredPrice);
                    this.discountPercent = Number(((this.discountAmount / this.originalPrice) * 100).toFixed(2));

                    if (this.discountPercent <= 5.00) {
                        this.approvalRoutingText = 'Auto-approved on submission';
                    } else if (this.discountPercent <= 12.00) {
                        this.approvalRoutingText = 'Requires Sales Manager Approval';
                    } else {
                        this.approvalRoutingText = 'Requires VP / Admin Special Approval';
                    }
                } else {
                    this.discountAmount = 0;
                    this.discountPercent = 0;
                    this.approvalRoutingText = '';
                }
            }
        }
    }
</script>
@endpush
@endsection

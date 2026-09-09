@extends('layouts.app')

@section('title', 'Negotiation Room - Offer #' . $offer->offer_number)
@section('page-title', 'Offer Negotiation Room')

@section('content')
<div class="container-fluid">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="mb-0 fw-bold brand-font text-dark">Offer #{{ $offer->offer_number }}</h4>
                        <span class="badge bg-{{ $offer->status === 'Approved' ? 'success' : ($offer->status === 'Converted to Booking' ? 'primary' : ($offer->status === 'Rejected' ? 'danger' : 'warning text-dark')) }} px-3 py-2 fs-6">
                            {{ $offer->status }}
                        </span>
                        @if($offer->discount_percentage > 0)
                            <span class="badge bg-danger">{{ number_format($offer->discount_percentage, 1) }}% OFF</span>
                        @endif
                    </div>
                    <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                        <span><i class="bi bi-person text-secondary me-1"></i> Lead: <a href="{{ route('leads.show', $offer->lead_id) }}" class="fw-bold text-dark text-decoration-none">{{ $offer->lead?->full_name }}</a></span>
                        <span><i class="bi bi-door-open text-secondary me-1"></i> Unit: <strong class="text-dark">{{ $offer->unit?->unit_number }}</strong> ({{ $offer->unit?->project?->name }})</span>
                        <span><i class="bi bi-clock me-1"></i> Valid Until: {{ $offer->valid_until->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('offers.pdf', $offer->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Offer
                    </a>

                    @can('convertBooking', $offer)
                        <form action="{{ route('offers.convert-booking', $offer->id) }}" method="POST" onsubmit="return confirm('Convert this approved offer into an active Unit Booking?')">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                <i class="bi bi-file-earmark-check-fill me-1"></i> Convert to Booking Contract
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Threshold Approval Banner (If Pending Approval) -->
    @if(in_array($offer->status, ['Pending Manager Approval', 'Pending Admin Approval']))
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-exclamation text-warning fs-5 me-2"></i> Approval Action Required</h6>
                    <p class="small mb-0">This offer has a discount of <strong>{{ number_format($offer->discount_percentage, 1) }}% (₹{{ number_format($offer->discount_amount) }})</strong> requiring approval.</p>
                </div>
                @can('approve', $offer)
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle me-1"></i> Approve Offer
                        </button>
                        <button class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject Offer
                        </button>
                    </div>
                @endcan
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Sidebar: Offer Financials & Unit Lock Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calculator text-primary me-2"></i> Financial Proposal Terms</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Original List Price</span>
                            <span class="text-decoration-line-through text-muted">₹{{ number_format($offer->original_unit_price) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Offered Price Proposal</span>
                            <span class="fw-bold text-success fs-6">₹{{ number_format($offer->offered_price) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Total Discount</span>
                            <span class="fw-bold text-danger">₹{{ number_format($offer->discount_amount) }} ({{ number_format($offer->discount_percentage, 1) }}%)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Token Advance Offered</span>
                            <span class="fw-bold text-dark">₹{{ number_format($offer->token_amount_offered) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Payment Scheme</span>
                            <span class="badge bg-light text-dark border">{{ $offer->payment_plan_type }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Temporary Unit Lock</span>
                            <span class="fw-semibold text-primary">
                                @if($offer->unit_lock_expires_at)
                                    Expires {{ $offer->unit_lock_expires_at->format('M d, h:i A') }}
                                @else
                                    None
                                @endif
                            </span>
                        </li>
                    </ul>

                    @if($offer->terms_conditions)
                        <div class="mt-3 pt-3 border-top">
                            <label class="form-label small text-muted fw-bold mb-1">Terms & Conditions:</label>
                            <div class="p-2 bg-light rounded text-dark small">{{ $offer->terms_conditions }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Main: Multi-Round Negotiation Workspace -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-chat-left-dots text-primary me-2"></i> Multi-Round Negotiation History</h6>
                    <span class="badge bg-light text-dark border">{{ $offer->negotiationRounds->count() }} Rounds Logged</span>
                </div>
                <div class="card-body p-4">
                    @foreach($offer->negotiationRounds as $round)
                        <div class="p-3 mb-3 bg-light rounded border border-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">Round {{ $round->round_number }} — {{ $round->offered_by }} Proposal</span>
                                <small class="text-muted">{{ $round->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <span class="text-muted">Proposed Price:</span>
                                    <strong class="text-dark fs-6 ms-1">₹{{ number_format($round->proposed_price) }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted">Token Advance:</span>
                                    <strong class="text-dark ms-1">₹{{ number_format($round->requested_token_amount) }}</strong>
                                </div>
                                @if($round->comments)
                                    <div class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-10 text-secondary">
                                        <i class="bi bi-chat-quote me-1"></i> {{ $round->comments }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Submit Counter-Offer Form -->
                    @if(!in_array($offer->status, ['Converted to Booking', 'Rejected', 'Expired']))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-reply-fill text-primary me-1"></i> Submit Next Counter-Offer Round</h6>
                            <form action="{{ route('offers.counter', $offer->id) }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Counter Party</label>
                                        <select name="offered_by" class="form-select form-select-sm" required>
                                            <option value="Builder">Builder (Counter-Proposal)</option>
                                            <option value="Buyer">Buyer (Revised Offer)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Counter Proposed Price (₹)</label>
                                        <input type="number" step="1000" name="counter_price" class="form-control form-control-sm fw-bold text-primary" required value="{{ $offer->offered_price }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Token Amount (₹)</label>
                                        <input type="number" step="1000" name="token_amount" class="form-control form-control-sm" required value="{{ $offer->token_amount_offered }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Negotiation Comments</label>
                                        <input type="text" name="comments" class="form-control form-control-sm" placeholder="Builder counter offer including free parking slot...">
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-outline-primary btn-sm px-4">Submit Counter Proposal</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Approve Offer -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('offers.approve', $offer->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-2"></i> Approve Formal Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">You are approving Offer <strong>#{{ $offer->offer_number }}</strong> with a total discount of <strong>₹{{ number_format($offer->discount_amount) }} ({{ number_format($offer->discount_percentage, 1) }}%)</strong>.</p>
                    <label class="form-label small fw-bold">Approval Notes</label>
                    <textarea name="approval_notes" class="form-control form-control-sm" rows="3" placeholder="Discount approved by management..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Approve Offer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Offer -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('offers.reject', $offer->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-2"></i> Reject Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Rejecting this offer will release Unit <strong>{{ $offer->unit?->unit_number }}</strong> status back to <strong>Available</strong>.</p>
                    <label class="form-label small fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required placeholder="Discount requested exceeds margin limits..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Reject Offer & Release Lock</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

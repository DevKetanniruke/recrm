@extends('layouts.app')

@section('title', 'Offers & Negotiation Workspace')
@section('page-title', 'Offers & Negotiation Pipeline')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-tag-fill text-primary me-2"></i> Property Offers & Negotiation Pipeline</h4>
            <p class="text-muted small mb-0">Manage price negotiations, multi-round counter offers, threshold approval matrices, and unit locks.</p>
        </div>

        <a href="{{ route('offers.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Create Formal Offer
        </a>
    </div>

    <!-- Status Filters -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="{{ route('offers.index') }}" class="btn btn-sm btn-outline-secondary {{ !request('status') ? 'active' : '' }}">All Offers</a>
        <a href="{{ route('offers.index', ['status' => 'Pending Manager Approval']) }}" class="btn btn-sm btn-outline-warning {{ request('status') === 'Pending Manager Approval' ? 'active' : '' }}">Pending Approval</a>
        <a href="{{ route('offers.index', ['status' => 'Approved']) }}" class="btn btn-sm btn-outline-success {{ request('status') === 'Approved' ? 'active' : '' }}">Approved Offers</a>
        <a href="{{ route('offers.index', ['status' => 'Countered']) }}" class="btn btn-sm btn-outline-info {{ request('status') === 'Countered' ? 'active' : '' }}">Countered</a>
        <a href="{{ route('offers.index', ['status' => 'Converted to Booking']) }}" class="btn btn-sm btn-outline-primary {{ request('status') === 'Converted to Booking' ? 'active' : '' }}">Converted Bookings</a>
    </div>

    <!-- Matrix Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>Offer #</th>
                        <th>Buyer Lead</th>
                        <th>Target Unit</th>
                        <th>List Price</th>
                        <th>Offered Price</th>
                        <th>Discount %</th>
                        <th>Unit Lock Expiry</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>
                                <a href="{{ route('offers.show', $offer->id) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $offer->offer_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('leads.show', $offer->lead_id) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $offer->lead?->full_name }}
                                </a>
                                <div><small class="text-muted"><i class="bi bi-telephone"></i> {{ $offer->lead?->mobile }}</small></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    Unit {{ $offer->unit?->unit_number }} ({{ $offer->unit?->project?->name }})
                                </span>
                            </td>
                            <td><span class="text-muted text-decoration-line-through">₹{{ number_format($offer->original_unit_price) }}</span></td>
                            <td><span class="fw-bold text-dark">₹{{ number_format($offer->offered_price) }}</span></td>
                            <td>
                                @php
                                    $discColor = $offer->discount_percentage > 12 ? 'danger' : ($offer->discount_percentage > 5 ? 'warning text-dark' : 'success');
                                @endphp
                                <span class="badge bg-{{ $discColor }}">
                                    {{ number_format($offer->discount_percentage, 1) }}% OFF
                                </span>
                            </td>
                            <td>
                                @if($offer->unit_lock_expires_at)
                                    <small class="text-dark"><i class="bi bi-clock-history text-secondary me-1"></i> {{ $offer->unit_lock_expires_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $stBadge = match($offer->status) {
                                        'Approved' => 'success',
                                        'Converted to Booking' => 'primary',
                                        'Pending Manager Approval', 'Pending Admin Approval' => 'warning text-dark',
                                        'Countered' => 'info text-dark',
                                        'Rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $stBadge }}">{{ $offer->status }}</span>
                            </td>
                            <td><small class="fw-semibold text-dark">{{ $offer->creator?->name }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('offers.show', $offer->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Negotiation Room">
                                    <i class="bi bi-door-open"></i> Negotiate
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="bi bi-tag fs-2 d-block mb-2 text-secondary"></i>
                                No formal price offers found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
            <div class="card-footer bg-white border-0 py-2">
                {{ $offers->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

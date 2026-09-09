@extends('layouts.app')

@section('title', 'Site Visit #' . ($siteVisit->visit_number ?? $siteVisit->id))
@section('page-title', 'Site Visit Walkthrough & GPS Verification')

@section('content')
<div class="container-fluid" x-data="siteVisitGeo()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Visit Profile: {{ $siteVisit->visit_number ?? ('SV-' . $siteVisit->id) }}</h4>
            <p class="text-muted small mb-0">Site Walkthrough for {{ $siteVisit->lead?->full_name }} at {{ $siteVisit->project?->name }}.</p>
        </div>
        <a href="{{ route('site-visits.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Site Visits
        </a>
    </div>

    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0 fw-bold text-dark">{{ $siteVisit->lead?->full_name }}</h5>
                        <span class="badge bg-primary px-2 py-1"><i class="bi bi-building me-1"></i> {{ $siteVisit->project?->name }}</span>
                        <span class="badge bg-{{ $siteVisit->status === 'Completed' ? 'success' : ($siteVisit->status === 'Checked In' ? 'info text-dark' : 'warning text-dark') }}">
                            {{ $siteVisit->status }}
                        </span>
                    </div>
                    <div class="text-muted small d-flex flex-wrap gap-3">
                        <span><i class="bi bi-telephone text-secondary me-1"></i> {{ $siteVisit->lead?->mobile }}</span>
                        <span><i class="bi bi-calendar-event me-1"></i> Scheduled: {{ $siteVisit->visit_date->format('M d, Y h:i A') }}</span>
                        <span><i class="bi bi-person-circle me-1"></i> Escort Agent: {{ $siteVisit->assignedTo?->name ?? 'Unassigned' }}</span>
                    </div>
                </div>

                <!-- GPS Check-In Action -->
                @if(!in_array($siteVisit->status, ['Checked In', 'Completed', 'Cancelled']))
                    <form action="{{ route('site-visits.check-in', $siteVisit->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="latitude" x-model="lat">
                        <input type="hidden" name="longitude" x-model="lng">
                        <button type="submit" class="btn btn-success btn-lg shadow px-4" @click="getGeoLocation()">
                            <i class="bi bi-geo-fill me-2"></i> GPS Check-In at Site
                        </button>
                    </form>
                @elseif($siteVisit->status === 'Checked In')
                    <div class="badge bg-success py-2 px-3 fs-6"><i class="bi bi-check-circle-fill me-1"></i> Checked In at {{ $siteVisit->check_in_at->format('h:i A') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Logistics & Transport Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-car-front-fill text-primary me-2"></i> Logistics & Geolocation Log</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Transport Mode</span>
                            <span class="fw-bold text-dark">{{ $siteVisit->transportation_type }}</span>
                        </li>
                        @if($siteVisit->driver_name)
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Cab Driver</span>
                                <span class="fw-semibold text-dark">{{ $siteVisit->driver_name }} ({{ $siteVisit->driver_phone }})</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Check-In Time</span>
                            <span class="fw-semibold text-dark">{{ $siteVisit->check_in_at ? $siteVisit->check_in_at->format('M d, h:i A') : 'Pending' }}</span>
                        </li>
                        @if($siteVisit->check_in_lat)
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-muted">Check-In GPS Coordinates</span>
                                <span class="fw-mono text-primary small">{{ $siteVisit->check_in_lat }}, {{ $siteVisit->check_in_lng }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Check-Out Time</span>
                            <span class="fw-semibold text-dark">{{ $siteVisit->check_out_at ? $siteVisit->check_out_at->format('M d, h:i A') : 'Pending' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Walkthrough Feedback & Completion Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-card-checklist text-primary me-2"></i> Walkthrough Feedback & Unit Ratings</h6>
                </div>
                <div class="card-body p-4">
                    @if($siteVisit->status === 'Completed')
                        <div class="alert alert-success border-0 shadow-sm mb-3">
                            <h6 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> Site Visit Completed & Verified</h6>
                            <p class="small mb-0">Visit checked out at {{ $siteVisit->check_out_at?->format('h:i A') }}. Overall Rating: <strong>{{ $siteVisit->rating }}</strong>.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Customer Detailed Feedback:</label>
                            <div class="p-3 bg-light rounded text-dark small" style="white-space: pre-line;">{{ $siteVisit->feedback }}</div>
                        </div>
                        @if($unitsViewed->isNotEmpty())
                            <div>
                                <label class="form-label small fw-bold text-muted">Units Inspected During Walkthrough:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($unitsViewed as $u)
                                        <span class="badge bg-light text-dark border p-2"><i class="bi bi-door-open me-1"></i> Unit {{ $u->unit_number }} ({{ $u->unit_type }}) - ₹{{ number_format($u->total_price) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <form action="{{ route('site-visits.check-out', $siteVisit->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="latitude" x-model="lat">
                            <input type="hidden" name="longitude" x-model="lng">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Overall Buyer Interest Rating <span class="text-danger">*</span></label>
                                    <select name="rating" class="form-select form-select-sm" required>
                                        <option value="Hot">🔥 Hot (Extremely Interested, Ready for Offer)</option>
                                        <option value="Warm">Warm (Interested, Needs Price Negotiation)</option>
                                        <option value="Cold">Cold (Neutral / Considering Options)</option>
                                        <option value="Not Interested">Not Interested (Rejected Project Location)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tag Units Inspected</label>
                                    <select name="units_viewed[]" class="form-select form-select-sm" multiple style="height: 80px;">
                                        @foreach($allUnits as $u)
                                            <option value="{{ $u->id }}">Unit {{ $u->unit_number }} - {{ $u->unit_type }} (₹{{ number_format($u->total_price) }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size: 0.7rem;">Hold Ctrl/Cmd to select multiple units.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold">Detailed Customer Walkthrough Feedback <span class="text-danger">*</span></label>
                                    <textarea name="feedback" class="form-control form-control-sm" rows="4" required placeholder="Customer liked floor plan of Unit 202, requested discount on PLC charges..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                @if($siteVisit->lead)
                                    <a href="{{ route('offers.create', ['lead_id' => $siteVisit->lead_id]) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-tag-fill me-1"></i> Create Formal Offer Now
                                    </a>
                                @endif
                                <button type="submit" class="btn btn-primary px-4 shadow-sm" @click="getGeoLocation()">
                                    <i class="bi bi-check2-circle me-1"></i> Complete & Check-Out Visit
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function siteVisitGeo() {
        return {
            lat: null,
            lng: null,
            getGeoLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        this.lat = pos.coords.latitude;
                        this.lng = pos.coords.longitude;
                    }, (err) => console.warn(err.message));
                }
            }
        }
    }
</script>
@endpush
@endsection

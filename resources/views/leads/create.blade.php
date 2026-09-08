@extends('layouts.app')

@section('title', 'Register New Lead')
@section('page-title', 'Create Lead')

@section('content')
<div class="container-fluid" x-data="leadForm()">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-person-plus-fill text-primary me-2"></i> Register New Lead</h4>
            <p class="text-muted small mb-0">Capture new real estate customer enquiry with source tracking and duplicate check.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Pipeline
        </a>
    </div>

    @if(session('duplicate_warning'))
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Potential Duplicate Leads Found!</h6>
            <p class="small mb-2">A lead with matching phone or email already exists in your company CRM:</p>
            <ul class="small mb-3">
                @foreach(session('duplicate_warning') as $dup)
                    <li>
                        <strong>{{ $dup->lead_number }} - {{ $dup->full_name }}</strong> (Mobile: {{ $dup->mobile }}, Email: {{ $dup->email }})
                        — Status: {{ $dup->status }} — <a href="{{ route('leads.show', $dup->id) }}" target="_blank" class="alert-link">View Details <i class="bi bi-box-arrow-up-right"></i></a>
                    </li>
                @endforeach
            </ul>
            <p class="small mb-0 text-muted">To proceed anyway with creating this lead as a new record, click "Ignore Warning & Save Lead".</p>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('leads.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ignore_duplicates" x-model="ignoreDuplicates" value="0">

                <div class="row g-3">
                    <!-- Personal Info -->
                    <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-person-badge me-2"></i> Basic Information</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-sm" required value="{{ old('first_name') }}" placeholder="John">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name') }}" placeholder="Doe">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Primary Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control form-control-sm" required value="{{ old('mobile') }}" x-model="mobile" @input.debounce.500ms="checkDuplicate()" placeholder="9876543210">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Alternate Mobile</label>
                        <input type="text" name="alternate_mobile" class="form-control form-control-sm" value="{{ old('alternate_mobile') }}" placeholder="9876500000">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" x-model="email" @input.debounce.500ms="checkDuplicate()" placeholder="john.doe@example.com">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city') }}" placeholder="Mumbai">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Location / Area</label>
                        <input type="text" name="location" class="form-control form-control-sm" value="{{ old('location') }}" placeholder="Andheri West">
                    </div>

                    <!-- Live Duplicate Alert Banner -->
                    <div class="col-12" x-show="hasDuplicates" x-cloak>
                        <div class="alert alert-danger py-2 px-3 mb-0 small">
                            <i class="bi bi-shield-exclamation me-1"></i>
                            <strong>Duplicate Warning:</strong> <span x-text="duplicateCount"></span> matching lead(s) found in CRM database for this contact!
                        </div>
                    </div>

                    <!-- Source & Campaign -->
                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-funnel me-2"></i> Source & Marketing Campaign</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Lead Source <span class="text-danger">*</span></label>
                        <select name="source" class="form-select form-select-sm" required>
                            @foreach($sources as $src)
                                <option value="{{ $src->name }}" {{ old('source') == $src->name ? 'selected' : '' }}>{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Campaign Name</label>
                        <input type="text" name="campaign" class="form-control form-control-sm" value="{{ old('campaign') }}" placeholder="Monsoon Special Discount 2026">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Priority Level</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="Hot" {{ old('priority') == 'Hot' ? 'selected' : '' }}>🔥 Hot (High Intent)</option>
                            <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                            <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <!-- Requirement Details -->
                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-houses me-2"></i> Property Requirements & Budget</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Interested Project</label>
                        <select name="project_id" class="form-select form-select-sm">
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Unit Type Preference</label>
                        <input type="text" name="unit_type" class="form-control form-control-sm" value="{{ old('unit_type') }}" placeholder="2BHK Luxury / Villa">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Min Budget (₹)</label>
                        <input type="number" step="10000" name="minimum_budget" class="form-control form-control-sm" value="{{ old('minimum_budget') }}" placeholder="5000000">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Max Budget (₹)</label>
                        <input type="number" step="10000" name="maximum_budget" class="form-control form-control-sm" value="{{ old('maximum_budget') }}" placeholder="8500000">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Preferred Floor</label>
                        <input type="text" name="preferred_floor" class="form-control form-control-sm" value="{{ old('preferred_floor') }}" placeholder="Middle Floor / High Rise">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Preferred Facing</label>
                        <input type="text" name="preferred_facing" class="form-control form-control-sm" value="{{ old('preferred_facing') }}" placeholder="East / Garden Facing">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Purchase Timeline</label>
                        <select name="purchase_timeline" class="form-select form-select-sm">
                            <option value="Immediate" {{ old('purchase_timeline') == 'Immediate' ? 'selected' : '' }}>Immediate (Within 15 Days)</option>
                            <option value="1-3 Months" {{ old('purchase_timeline') == '1-3 Months' ? 'selected' : '' }}>1 to 3 Months</option>
                            <option value="3-6 Months" {{ old('purchase_timeline') == '3-6 Months' ? 'selected' : '' }}>3 to 6 Months</option>
                            <option value=">6 Months" {{ old('purchase_timeline') == '>6 Months' ? 'selected' : '' }}>Over 6 Months</option>
                        </select>
                    </div>

                    <!-- Assignment & Notes -->
                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-person-check me-2"></i> Assignment & Initial Notes</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assign to Sales Executive</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Assign to Me ({{ auth()->user()->name }})</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ old('assigned_to') == $ag->id ? 'selected' : '' }}>{{ $ag->name }} ({{ str_replace('_', ' ', $ag->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assign to Team</label>
                        <select name="assigned_team_id" class="form-select form-select-sm">
                            <option value="">No Team Assignment</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" {{ old('assigned_team_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Initial Status</label>
                        <select name="status" class="form-select form-select-sm">
                            @foreach($statuses as $st)
                                <option value="{{ $st->name }}" {{ old('status', 'New') == $st->name ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Lead Requirement Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Enter customer preferences, family requirements, site visit convenience, etc.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('leads.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4" x-text="hasDuplicates ? 'Save Lead (Override Warning)' : 'Save & Register Lead'" @click="ignoreDuplicates = 1"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function leadForm() {
        return {
            mobile: '{{ old("mobile") }}',
            email: '{{ old("email") }}',
            hasDuplicates: false,
            duplicateCount: 0,
            ignoreDuplicates: 0,
            checkDuplicate() {
                if (this.mobile.length < 5 && this.email.length < 5) return;
                fetch(`/leads/check-duplicates?mobile=${encodeURIComponent(this.mobile)}&email=${encodeURIComponent(this.email)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.hasDuplicates = data.has_duplicates;
                        this.duplicateCount = data.duplicates ? data.duplicates.length : 0;
                    })
                    .catch(err => console.error(err));
            }
        }
    }
</script>
@endpush
@endsection

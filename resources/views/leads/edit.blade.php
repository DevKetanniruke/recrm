@extends('layouts.app')

@section('title', 'Edit Lead - ' . $lead->full_name)
@section('page-title', 'Edit Lead')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Lead: {{ $lead->full_name }}</h4>
            <p class="text-muted small mb-0">Update contact info, property preferences, priority, or reassign agent/team.</p>
        </div>
        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Lead Details
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-person-badge me-2"></i> Basic Information</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-sm" required value="{{ old('first_name', $lead->first_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name', $lead->last_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Primary Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control form-control-sm" required value="{{ old('mobile', $lead->mobile) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Alternate Mobile</label>
                        <input type="text" name="alternate_mobile" class="form-control form-control-sm" value="{{ old('alternate_mobile', $lead->alternate_mobile) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $lead->email) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $lead->city) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Location / Area</label>
                        <input type="text" name="location" class="form-control form-control-sm" value="{{ old('location', $lead->location) }}">
                    </div>

                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-funnel me-2"></i> Source & Priority</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Lead Source</label>
                        <select name="source" class="form-select form-select-sm">
                            @foreach($sources as $src)
                                <option value="{{ $src->name }}" {{ old('source', $lead->source) == $src->name ? 'selected' : '' }}>{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Campaign</label>
                        <input type="text" name="campaign" class="form-control form-control-sm" value="{{ old('campaign', $lead->campaign) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Priority Level</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="Hot" {{ old('priority', $lead->priority) == 'Hot' ? 'selected' : '' }}>🔥 Hot</option>
                            <option value="High" {{ old('priority', $lead->priority) == 'High' ? 'selected' : '' }}>High</option>
                            <option value="Medium" {{ old('priority', $lead->priority) == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="Low" {{ old('priority', $lead->priority) == 'Low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-houses me-2"></i> Property Requirements</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Project</label>
                        <select name="project_id" class="form-select form-select-sm">
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $lead->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Unit Type</label>
                        <input type="text" name="unit_type" class="form-control form-control-sm" value="{{ old('unit_type', $lead->unit_type) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Min Budget (₹)</label>
                        <input type="number" step="10000" name="minimum_budget" class="form-control form-control-sm" value="{{ old('minimum_budget', $lead->minimum_budget) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Max Budget (₹)</label>
                        <input type="number" step="10000" name="maximum_budget" class="form-control form-control-sm" value="{{ old('maximum_budget', $lead->maximum_budget) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Preferred Floor</label>
                        <input type="text" name="preferred_floor" class="form-control form-control-sm" value="{{ old('preferred_floor', $lead->preferred_floor) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Preferred Facing</label>
                        <input type="text" name="preferred_facing" class="form-control form-control-sm" value="{{ old('preferred_facing', $lead->preferred_facing) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Purchase Timeline</label>
                        <select name="purchase_timeline" class="form-select form-select-sm">
                            <option value="Immediate" {{ old('purchase_timeline', $lead->purchase_timeline) == 'Immediate' ? 'selected' : '' }}>Immediate</option>
                            <option value="1-3 Months" {{ old('purchase_timeline', $lead->purchase_timeline) == '1-3 Months' ? 'selected' : '' }}>1 to 3 Months</option>
                            <option value="3-6 Months" {{ old('purchase_timeline', $lead->purchase_timeline) == '3-6 Months' ? 'selected' : '' }}>3 to 6 Months</option>
                            <option value=">6 Months" {{ old('purchase_timeline', $lead->purchase_timeline) == '>6 Months' ? 'selected' : '' }}>Over 6 Months</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-person-check me-2"></i> Assignment & Pipeline Status</h6></div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assigned Sales Executive</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Unassigned</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ old('assigned_to', $lead->assigned_to) == $ag->id ? 'selected' : '' }}>{{ $ag->name }} ({{ str_replace('_', ' ', $ag->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Assigned Team</label>
                        <select name="assigned_team_id" class="form-select form-select-sm">
                            <option value="">No Team</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" {{ old('assigned_team_id', $lead->assigned_team_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            @foreach($statuses as $st)
                                <option value="{{ $st->name }}" {{ old('status', $lead->status) == $st->name ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3">{{ old('notes', $lead->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Lead Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

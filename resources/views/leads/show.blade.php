@extends('layouts.app')

@section('title', 'Lead Profile - ' . $lead->full_name)
@section('page-title', 'Lead Details & Timeline')

@section('content')
<div class="container-fluid">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width:60px; height:60px;">
                        {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold brand-font text-dark">{{ $lead->full_name }}</h4>
                            <span class="badge bg-primary px-2 py-1">{{ $lead->lead_number ?? ('LD-' . $lead->id) }}</span>
                            <span class="badge bg-{{ $lead->priority === 'Hot' ? 'danger' : ($lead->priority === 'High' ? 'warning text-dark' : 'secondary') }}">
                                @if($lead->priority === 'Hot') 🔥 @endif {{ $lead->priority }}
                            </span>
                        </div>
                        <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                            <span><i class="bi bi-telephone text-secondary me-1"></i> {{ $lead->mobile }}</span>
                            @if($lead->email) <span><i class="bi bi-envelope text-secondary me-1"></i> {{ $lead->email }}</span> @endif
                            @if($lead->city) <span><i class="bi bi-geo-alt text-secondary me-1"></i> {{ $lead->city }}</span> @endif
                            <span><i class="bi bi-clock me-1"></i> Created {{ $lead->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                        <i class="bi bi-telephone-out me-1"></i> Log Activity
                    </button>
                    <button type="button" class="btn btn-info btn-sm text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#followupModal">
                        <i class="bi bi-calendar-event me-1"></i> Schedule Follow-up
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal">
                        <i class="bi bi-arrow-repeat me-1"></i> Update Status
                    </button>
                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Sidebar: Lead Attributes & Quick Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i> Lead Profile Summary</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Current Status</span>
                            <span class="fw-bold text-dark">{{ $lead->status }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Lead Source</span>
                            <span class="badge bg-light text-dark border">{{ $lead->source }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Marketing Campaign</span>
                            <span class="fw-semibold text-dark">{{ $lead->campaign ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Assigned Executive</span>
                            <span class="fw-semibold text-dark">{{ $lead->assignedTo?->name ?? 'Unassigned' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Assigned Team</span>
                            <span class="fw-semibold text-dark">{{ $lead->assignedTeam?->name ?? '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-houses text-primary me-2"></i> Property Requirements</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Interested Project</span>
                            <span class="fw-bold text-primary">{{ $lead->project?->name ?? 'Any / Undecided' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Unit Type</span>
                            <span class="fw-semibold text-dark">{{ $lead->unit_type ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Budget Range</span>
                            <span class="fw-bold text-success">
                                @if($lead->minimum_budget || $lead->maximum_budget)
                                    ₹{{ number_format($lead->minimum_budget / 100000, 1) }}L - ₹{{ number_format($lead->maximum_budget / 100000, 1) }}L
                                @else
                                    Not specified
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Floor & Facing</span>
                            <span class="fw-semibold text-dark">{{ $lead->preferred_floor ?? 'Any' }} / {{ $lead->preferred_facing ?? 'Any' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Purchase Timeline</span>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $lead->purchase_timeline ?? 'Flexible' }}</span>
                        </li>
                    </ul>
                    @if($lead->notes)
                        <div class="mt-3 pt-3 border-top">
                            <label class="form-label small text-muted fw-bold mb-1">Requirement Notes:</label>
                            <div class="p-2 bg-light rounded text-dark small" style="white-space: pre-line;">{{ $lead->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Main: Interactive Chronological Timeline -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i> Interactive Lead Timeline</h6>
                    <span class="badge bg-light text-dark border">{{ count($timeline) }} Events Logged</span>
                </div>
                <div class="card-body p-4">
                    @forelse($timeline as $item)
                        <div class="d-flex mb-4 position-relative">
                            <div class="me-3 d-flex flex-column align-items-center">
                                @php
                                    $icon = match($item['type']) {
                                        'activity' => 'bi-telephone-out-fill bg-primary text-white',
                                        'followup' => 'bi-calendar-check-fill bg-info text-dark',
                                        'assignment' => 'bi-person-check-fill bg-warning text-dark',
                                        'site_visit' => 'bi-geo-alt-fill bg-success text-white',
                                        default => 'bi-circle-fill bg-secondary text-white'
                                    };
                                @endphp
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width:36px; height:36px;">
                                    <i class="bi {{ $icon }} fs-6"></i>
                                </div>
                                <div class="h-100 border-start border-2 border-light mt-1"></div>
                            </div>
                            <div class="flex-grow-1 bg-light p-3 rounded shadow-sm border border-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $item['title'] }}</h6>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($item['timestamp'])->format('M d, Y h:i A') }}</small>
                                </div>
                                @if(!empty($item['description']))
                                    <p class="mb-2 text-secondary small" style="white-space: pre-line;">{{ $item['description'] }}</p>
                                @endif
                                @if(!empty($item['outcome']))
                                    <div class="small text-dark fw-semibold"><i class="bi bi-check2-circle text-success me-1"></i> Outcome: {{ $item['outcome'] }}</div>
                                @endif
                                @if(!empty($item['next_action']))
                                    <div class="small text-dark fw-semibold"><i class="bi bi-arrow-right-circle text-primary me-1"></i> Next Action: {{ $item['next_action'] }}</div>
                                @endif
                                <div class="mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center small text-muted">
                                    <span><i class="bi bi-person me-1"></i> By {{ $item['user'] }}</span>
                                    <span class="badge bg-white text-secondary border">{{ $item['badge'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            No timeline activities logged yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Activity -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('leads.add-activity', $lead->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-telephone-out text-primary me-2"></i> Log Customer Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Activity Type</label>
                        <select name="activity_type" class="form-select form-select-sm" required>
                            <option value="Call">Call</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Email">Email</option>
                            <option value="SMS">SMS</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Note">Note</option>
                            <option value="Site Visit">Site Visit</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Subject / Title</label>
                        <input type="text" name="subject" class="form-control form-control-sm" placeholder="Call with client regarding pricing">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Activity Summary / Notes <span class="text-danger">*</span></label>
                        <textarea name="summary" class="form-control form-control-sm" rows="3" required placeholder="Client discussed 2BHK flat requirements and requested discount..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Outcome</label>
                        <input type="text" name="outcome" class="form-control form-control-sm" placeholder="Interested in 4th floor">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Next Follow-up Date/Time</label>
                        <input type="datetime-local" name="next_followup_at" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Log Activity</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Schedule Followup -->
<div class="modal fade" id="followupModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('followups.store', $lead->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-event text-info me-2"></i> Schedule Next Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Follow-up Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="Call">Call</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Site Visit">Site Visit</option>
                            <option value="Email">Email</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Scheduled Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="followup_at" class="form-control form-control-sm" required value="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Follow-up Notes / Agenda</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Confirm site visit appointment with family..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-dark px-4">Schedule Follow-up</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Update Status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('leads.update-status', $lead->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat text-dark me-2"></i> Transition Lead Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">New Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach($statuses as $st)
                                <option value="{{ $st->name }}" {{ $lead->status == $st->name ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Notes / Lost Reason (If applicable)</label>
                        <textarea name="lost_reason" class="form-control form-control-sm" rows="3" placeholder="Reason for status change or why deal was lost..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Event Automation Engine')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Event-Driven Automation Engine</h3>
            <p class="text-muted small mb-0">Configure automated notification triggers based on lead creation, site visits, bookings, and payment schedules</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRuleModal">
            <i class="bi bi-lightning-charge"></i> Add Automation Rule
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Trigger Event</th>
                                    <th>Communication Template</th>
                                    <th>Delay</th>
                                    <th>Status</th>
                                    <th>Total Executions</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                    <tr>
                                        <td><span class="fw-bold text-dark">{{ $rule->name }}</span></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary font-monospace">{{ $rule->trigger_event }}</span>
                                        </td>
                                        <td>{{ $rule->template?->name ?? 'N/A' }} ({{ strtoupper($rule->template?->channel) }})</td>
                                        <td>{{ $rule->delay_minutes > 0 ? $rule->delay_minutes.' mins' : 'Instant' }}</td>
                                        <td>
                                            @if($rule->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $rule->executions->count() }}</span></td>
                                        <td class="text-end">
                                            <form action="{{ route('communication.automations.toggle', $rule) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    {{ $rule->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No automation rules configured. Click "Add Automation Rule" to create your first trigger.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Rule -->
<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-lightning-charge text-primary me-2"></i> Add Automation Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('communication.automations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rule Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. New Lead Acknowledgment SMS" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Trigger Event</label>
                        <select name="trigger_event" class="form-select" required>
                            <option value="lead.created">New Lead Registered (lead.created)</option>
                            <option value="site_visit.scheduled">Site Visit Scheduled (site_visit.scheduled)</option>
                            <option value="booking.confirmed">Booking Confirmed (booking.confirmed)</option>
                            <option value="payment.due">Payment Due Notice (payment.due)</option>
                            <option value="payment.overdue">Payment Overdue Alert (payment.overdue)</option>
                            <option value="followup.due">Follow-up Due (followup.due)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Communication Template</label>
                        <select name="communication_template_id" class="form-select" required>
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ strtoupper($tpl->channel) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Delay (Minutes)</label>
                        <input type="number" name="delay_minutes" class="form-control" value="0" min="0">
                        <div class="form-text">Set 0 for immediate execution upon event trigger.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Follow-up Task Schedule')
@section('page-title', 'Follow-up Management')

@section('content')
<div class="container-fluid">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-calendar-check-fill text-primary me-2"></i> Follow-up Task Schedule</h4>
            <p class="text-muted small mb-0">Never miss a buyer lead touchpoint. Manage scheduled calls, visits, and meetings.</p>
        </div>

        <div class="btn-group" role="group">
            <a href="{{ route('followups.index', ['filter' => 'today']) }}" class="btn btn-outline-primary btn-sm {{ $filter === 'today' ? 'active' : '' }}">
                <i class="bi bi-calendar-day"></i> Today's Follow-ups
            </a>
            <a href="{{ route('followups.index', ['filter' => 'overdue']) }}" class="btn btn-outline-danger btn-sm {{ $filter === 'overdue' ? 'active' : '' }}">
                <i class="bi bi-exclamation-octagon"></i> Overdue
            </a>
            <a href="{{ route('followups.index', ['filter' => 'upcoming']) }}" class="btn btn-outline-success btn-sm {{ $filter === 'upcoming' ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i> Upcoming
            </a>
            <a href="{{ route('followups.index', ['filter' => 'all']) }}" class="btn btn-outline-secondary btn-sm {{ $filter === 'all' ? 'active' : '' }}">
                All Tasks
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>Scheduled Time</th>
                        <th>Type</th>
                        <th>Lead Details</th>
                        <th>Notes / Agenda</th>
                        <th>Assigned Executive</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followups as $fol)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $fol->followup_at->format('M d, Y') }}</div>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i> {{ $fol->followup_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $fol->type }}</span>
                            </td>
                            <td>
                                <a href="{{ route('leads.show', $fol->lead_id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $fol->lead?->full_name }}
                                </a>
                                <div><small class="text-muted"><i class="bi bi-telephone"></i> {{ $fol->lead?->mobile }}</small></div>
                            </td>
                            <td>
                                <div class="text-dark small">{{ $fol->notes ?? '—' }}</div>
                                @if($fol->outcome)
                                    <small class="text-success"><i class="bi bi-check2 me-1"></i> Outcome: {{ $fol->outcome }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="fw-semibold text-dark">{{ $fol->user?->name ?? 'Unassigned' }}</small>
                            </td>
                            <td>
                                @php
                                    $stColor = match($fol->status) {
                                        'Completed' => 'success',
                                        'Pending' => ($fol->followup_at < now() ? 'danger' : 'warning text-dark'),
                                        'Cancelled' => 'secondary',
                                        default => 'info text-dark'
                                    };
                                @endphp
                                <span class="badge bg-{{ $stColor }}">{{ $fol->status }}</span>
                            </td>
                            <td class="text-end">
                                @if($fol->status === 'Pending')
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#updateFolModal{{ $fol->id }}" title="Mark Complete">
                                        <i class="bi bi-check-lg"></i> Complete
                                    </button>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check-all"></i> Done</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Update Status -->
                        <div class="modal fade" id="updateFolModal{{ $fol->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <form action="{{ route('followups.update-status', $fol->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Update Follow-up Task</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Status</label>
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="Completed" selected>Completed</option>
                                                    <option value="Missed">Missed</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Outcome Notes</label>
                                                <textarea name="outcome" class="form-control form-control-sm" rows="2" placeholder="Client confirmed site visit..."></textarea>
                                            </div>
                                            <div>
                                                <label class="form-label small fw-bold">Next Action</label>
                                                <input type="text" name="next_action" class="form-control form-control-sm" placeholder="Send WhatsApp brochure">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success btn-sm px-3">Save Outcome</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2 text-secondary"></i>
                                No follow-up tasks found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($followups->hasPages())
            <div class="card-footer bg-white border-0 py-2">
                {{ $followups->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

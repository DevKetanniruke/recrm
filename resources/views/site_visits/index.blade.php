@extends('layouts.app')

@section('title', 'Site Visits Schedule')
@section('page-title', 'Site Visit Schedule & Appointments')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-calendar-event me-2 text-primary"></i> Site Visit Appointments</h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Lead Name</th>
                    <th>Project</th>
                    <th>Visit Date & Time</th>
                    <th>Assigned Agent</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siteVisits as $visit)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $visit->lead->full_name ?? 'N/A' }}</div>
                            <small class="text-secondary"><i class="bi bi-telephone"></i> {{ $visit->lead->phone ?? '' }}</small>
                        </td>
                        <td>{{ $visit->project->name ?? 'N/A' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $visit->visit_date->format('M d, Y') }}</div>
                            <small class="text-secondary">{{ $visit->visit_date->format('g:i A') }}</small>
                        </td>
                        <td>{{ $visit->assignedTo->name ?? 'Unassigned' }}</td>
                        <td>
                            <span class="badge @if($visit->status == 'Completed') bg-success @elseif($visit->status == 'Scheduled') bg-primary @else bg-secondary @endif">
                                {{ $visit->status }}
                            </span>
                        </td>
                        <td><small class="text-secondary">{{ $visit->feedback ?? 'No feedback recorded yet.' }}</small></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editVisitModal{{ $visit->id }}">
                                Update Status
                            </button>

                            <!-- Edit Modal -->
                            <div class="modal fade text-start" id="editVisitModal{{ $visit->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <form action="{{ route('site-visits.update-status', $visit->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title brand-font">Update Site Visit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-secondary">Visit Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="Scheduled" {{ $visit->status == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                        <option value="Completed" {{ $visit->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                        <option value="Cancelled" {{ $visit->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                        <option value="No-Show" {{ $visit->status == 'No-Show' ? 'selected' : '' }}>No-Show</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-secondary">Visitor Feedback</label>
                                                    <textarea name="feedback" class="form-control" rows="3">{{ $visit->feedback }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">No site visit appointments scheduled.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $siteVisits->links() }}
</div>
@endsection

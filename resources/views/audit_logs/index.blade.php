@extends('layouts.app')

@section('title', 'Activity Audit Logs')
@section('page-title', 'System Activity & Audit Log Explorer')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search entity type, IP address, user name...">
            </div>
            <div class="col-md-3">
                <select name="event" class="form-select" onchange="this.form.submit()">
                    <option value="">All Events</option>
                    <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="password_updated" {{ request('event') == 'password_updated' ? 'selected' : '' }}>Password Updated</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search me-1"></i> Filter Activity</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action / Event</th>
                    <th>Target Entity</th>
                    <th>IP / Device</th>
                    <th class="text-end">Changes / Diff</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    <tr>
                        <td><small class="fw-semibold text-dark">{{ $log->created_at->format('M d, Y g:i:s A') }}</small></td>
                        <td>
                            <div class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</div>
                            <small class="text-secondary">{{ $log->user->email ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="badge @if($log->event == 'login') bg-info @elseif($log->event == 'created') bg-success @elseif($log->event == 'updated') bg-warning text-dark @elseif($log->event == 'deleted') bg-danger @else bg-secondary @endif">
                                {{ strtoupper($log->event) }}
                            </span>
                        </td>
                        <td><span class="font-monospace small text-dark">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</span></td>
                        <td>
                            <small class="d-block font-monospace text-secondary">{{ $log->ip_address }}</small>
                            <small class="d-block text-muted text-truncate" style="max-width: 180px;">{{ $log->user_agent }}</small>
                        </td>
                        <td class="text-end">
                            @if(!empty($log->old_values) || !empty($log->new_values))
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#diffModal{{ $log->id }}">
                                    View Diff <i class="bi bi-code-square ms-1"></i>
                                </button>

                                <!-- Diff Modal -->
                                <div class="modal fade text-start" id="diffModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title brand-font">Audit Log Diff: #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold text-danger mb-2"><i class="bi bi-dash-circle me-1"></i> Old Values (Before)</h6>
                                                        <pre class="bg-light p-3 rounded border text-danger font-monospace small" style="max-height: 300px; overflow: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold text-success mb-2"><i class="bi bi-plus-circle me-1"></i> New Values (After)</h6>
                                                        <pre class="bg-light p-3 rounded border text-success font-monospace small" style="max-height: 300px; overflow: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-secondary small italic">No modification payload</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">No audit logs matching search criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $auditLogs->links() }}
</div>
@endsection

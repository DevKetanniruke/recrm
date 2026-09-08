@extends('layouts.app')

@section('title', 'User Profile: ' . $user->name)
@section('page-title', 'User Account Profile: ' . $user->name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body text-center p-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold fs-2 mb-3" style="width:80px; height:80px;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5 class="brand-font text-dark mb-1">{{ $user->name }}</h5>
                <p class="text-secondary small mb-2">{{ $user->email }}</p>
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-1 font-monospace">{{ str_replace('_', ' ', strtoupper($user->role)) }}</span>

                <div class="border-top pt-3 text-secondary text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Mobile Phone:</span>
                        <strong class="text-dark">{{ $user->mobile ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Company:</span>
                        <strong class="text-dark">{{ $user->company->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Account Status:</span>
                        <span class="badge @if($user->status == 'Active') bg-success @elseif($user->status == 'Suspended') bg-danger @else bg-secondary @endif">
                            {{ $user->status }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Last Active Login:</span>
                        <strong class="text-dark">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Never' }}</strong>
                    </div>
                </div>

                <!-- Status Update Form -->
                <form action="{{ route('users.update-status', $user->id) }}" method="POST" class="border-top pt-3 mt-3">
                    @csrf
                    <label class="form-label small fw-semibold text-secondary text-start d-block">Change Status:</label>
                    <div class="input-group input-group-sm">
                        <select name="status" class="form-select">
                            <option value="Active" {{ $user->status == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $user->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Suspended" {{ $user->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <button type="submit" class="btn btn-dark">Apply</button>
                    </div>
                </form>

                @if(auth()->user()->hasPermissionTo('users.edit'))
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary w-100 btn-sm mt-3">
                        <i class="bi bi-pencil me-1"></i> Edit Account Settings
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i> User Activity & Modification Audit Trail</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Event</th>
                            <th>Target Entity</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAuditLogs as $log)
                            <tr>
                                <td><small class="text-secondary">{{ $log->created_at->format('M d, Y g:i A') }}</small></td>
                                <td>
                                    <span class="badge @if($log->event == 'login') bg-info @elseif($log->event == 'created') bg-success @elseif($log->event == 'updated') bg-warning text-dark @else bg-secondary @endif">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td><small class="font-monospace text-dark">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</small></td>
                                <td><small class="font-monospace text-muted">{{ $log->ip_address }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No activity logs recorded for this user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

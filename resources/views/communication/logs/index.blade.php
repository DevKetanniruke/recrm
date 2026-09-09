@extends('layouts.app')

@section('title', 'Outbound Communication Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Outbound Communication Audit Logs</h3>
            <p class="text-muted small mb-0">Track delivery status, provider references, and failure reasons across all communication channels</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('communication.logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search recipient or subject..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                        <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="in_app" {{ request('channel') === 'in_app' ? 'selected' : '' }}>In-App</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Sent" {{ request('status') === 'Sent' ? 'selected' : '' }}>Sent</option>
                        <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="Failed" {{ request('status') === 'Failed' ? 'selected' : '' }}>Failed</option>
                        <option value="OptedOut" {{ request('status') === 'OptedOut' ? 'selected' : '' }}>OptedOut</option>
                        <option value="Queued" {{ request('status') === 'Queued' ? 'selected' : '' }}>Queued</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Filter</button>
                    <a href="{{ route('communication.logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Time</th>
                            <th>Channel</th>
                            <th>Recipient</th>
                            <th>Subject / Body Preview</th>
                            <th>Provider & Reference</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td><small class="fw-bold">{{ $log->created_at->format('M d, H:i:s') }}</small></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $log->channel }}</span>
                                </td>
                                <td><span class="font-monospace fw-bold">{{ $log->recipient }}</span></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;">
                                        {{ $log->subject ?: Str::limit($log->message_body, 40) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark">{{ $log->provider_name ?? 'N/A' }}</div>
                                    <small class="text-muted font-monospace">{{ $log->provider_reference ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($log->is_transactional)
                                        <span class="badge bg-dark">Transactional</span>
                                    @else
                                        <span class="badge bg-secondary">Marketing</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status === 'Sent' || $log->status === 'Delivered')
                                        <span class="badge bg-success">{{ $log->status }}</span>
                                    @elseif($log->status === 'OptedOut')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-slash-circle"></i> Opted Out</span>
                                    @elseif($log->status === 'Failed')
                                        <span class="badge bg-danger" title="{{ $log->failure_reason }}">Failed</span>
                                    @else
                                        <span class="badge bg-secondary">Queued</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No communication logs found matching criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

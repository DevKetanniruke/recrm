@extends('layouts.app')

@section('title', 'Marketing Campaigns Portal')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Marketing Campaigns Portal</h3>
            <p class="text-muted small mb-0">Schedule and dispatch targeted multi-channel outreach campaigns</p>
        </div>
        <a href="{{ route('communication.campaigns.create') }}" class="btn btn-primary">
            <i class="bi bi-megaphone"></i> Create Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Campaign Name</th>
                            <th>Channel</th>
                            <th>Template</th>
                            <th>Target Project</th>
                            <th>Status</th>
                            <th>Metrics (Sent / Failed)</th>
                            <th>Scheduled Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $camp)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $camp->name }}</div>
                                    <small class="text-muted">Created {{ $camp->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $camp->channel }}</span>
                                </td>
                                <td>{{ $camp->template?->name ?? 'N/A' }}</td>
                                <td>{{ $camp->project?->name ?? 'All Projects' }}</td>
                                <td>
                                    @if($camp->status === 'Completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($camp->status === 'Running')
                                        <span class="badge bg-info text-dark">Running...</span>
                                    @elseif($camp->status === 'Scheduled')
                                        <span class="badge bg-primary">Scheduled</span>
                                    @elseif($camp->status === 'Cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $camp->sent_count }} / {{ $camp->failed_count }}</div>
                                    <small class="text-muted">Total: {{ $camp->total_recipients }}</small>
                                </td>
                                <td>{{ $camp->scheduled_at ? $camp->scheduled_at->format('M d, Y H:i') : 'Immediate' }}</td>
                                <td class="text-end">
                                    @if(in_array($camp->status, ['Draft', 'Scheduled']))
                                        <form action="{{ route('communication.campaigns.launch', $camp) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success me-1">
                                                <i class="bi bi-play-fill"></i> Launch Now
                                            </button>
                                        </form>
                                        <form action="{{ route('communication.campaigns.cancel', $camp) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No marketing campaigns found. Click "Create Campaign" to initiate one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection

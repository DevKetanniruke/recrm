@extends('layouts.app')

@section('title', 'Customer Opt-Out Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Customer Communication Preferences & Opt-Out Registry</h3>
            <p class="text-muted small mb-0">Manage customer marketing channel opt-ins and opt-outs. Transactional notifications remain active.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#optOutModal">
            <i class="bi bi-person-slash"></i> Record Opt-Out Preference
        </button>
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
                            <th>Recipient (Email / Phone)</th>
                            <th>Channel</th>
                            <th>Marketing Status</th>
                            <th>Reason</th>
                            <th>Updated Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preferences as $pref)
                            <tr>
                                <td><span class="fw-bold text-dark font-monospace">{{ $pref->recipient }}</span></td>
                                <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $pref->channel }}</span></td>
                                <td>
                                    @if($pref->opt_in_marketing)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Opted In</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-slash-circle"></i> Opted Out</span>
                                    @endif
                                </td>
                                <td>{{ $pref->opt_out_reason ?: 'N/A' }}</td>
                                <td>{{ $pref->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No customer opt-out preferences recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $preferences->links() }}
        </div>
    </div>
</div>

<!-- Modal: Record Preference -->
<div class="modal fade" id="optOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear text-primary me-2"></i> Record Opt-Out Preference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('communication.optouts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Recipient Email / Phone</label>
                        <input type="text" name="recipient" class="form-control" placeholder="john.doe@example.com or 9123456789" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Channel</label>
                        <select name="channel" class="form-select" required>
                            <option value="all">All Channels</option>
                            <option value="email">Email Only</option>
                            <option value="sms">SMS Only</option>
                            <option value="whatsapp">WhatsApp Only</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Marketing Preference</label>
                        <select name="opt_in_marketing" class="form-select" required>
                            <option value="0">Opt Out (Block Marketing Messages)</option>
                            <option value="1">Opt In (Allow Marketing Messages)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Requested via email link"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Preference</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

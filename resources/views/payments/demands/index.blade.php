@extends('layouts.app')

@section('title', 'Payment Demand Notices - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Payment Demand Notices</h3>
            <p class="text-muted small mb-0">Issue payment request letters, track demand statuses, and automate overdue reminders.</p>
        </div>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createDemandModal" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-file-earmark-plus me-1"></i> Issue Demand Notice
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('demands.index') }}" method="GET" class="row g-2">
                <div class="col-md-8 col-lg-9">
                    <select name="status" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All Demand Statuses --</option>
                        <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent / Pending</option>
                        <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-3 d-flex">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill me-1">Filter</button>
                    <a href="{{ route('demands.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Demands Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Demand #</th>
                        <th>Booking & Customer</th>
                        <th>Demand Amount</th>
                        <th>Penalty</th>
                        <th>Demand Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($demands as $demand)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $demand->demand_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $demand->customer?->full_name ?: $demand->booking?->customer?->full_name }}</div>
                                <div class="small text-muted">Booking #{{ $demand->booking?->booking_number }}</div>
                            </td>
                            <td class="fw-bold text-dark">₹{{ number_format($demand->demand_amount, 2) }}</td>
                            <td class="small text-danger">₹{{ number_format($demand->penalty_amount, 2) }}</td>
                            <td class="small text-muted">{{ $demand->demand_date ? $demand->demand_date->format('d M Y') : 'N/A' }}</td>
                            <td class="small text-muted fw-semibold">{{ $demand->due_date ? $demand->due_date->format('d M Y') : 'N/A' }}</td>
                            <td>
                                @if($demand->status == 'Overdue')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">Overdue</span>
                                @elseif($demand->status == 'Paid')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Paid</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1">{{ $demand->status }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('demands.pdf', $demand->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="bi bi-printer me-1"></i> Print PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No payment demand notices generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($demands->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $demands->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal to Issue Demand Notice -->
<div class="modal fade" id="createDemandModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('demands.store') }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus text-primary me-2"></i> Issue Payment Demand Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Target Booking <span class="text-danger">*</span></label>
                    <select name="booking_id" class="form-select rounded-pill" required>
                        <option value="">-- Select Booking --</option>
                        @foreach($bookings as $bkg)
                            <option value="{{ $bkg->id }}">Booking #{{ $bkg->booking_number }} - {{ $bkg->customer?->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Demand Date <span class="text-danger">*</span></label>
                        <input type="date" name="demand_date" class="form-control rounded-pill" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control rounded-pill" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Demand Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="demand_amount" class="form-control rounded-pill" placeholder="Amount" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Penalty (If any)</label>
                        <input type="number" step="0.01" name="penalty_amount" class="form-control rounded-pill" value="0.00">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Demand Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Issue Demand</button>
            </div>
        </form>
    </div>
</div>
@endsection

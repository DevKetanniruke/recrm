@extends('layouts.app')

@section('title', 'Commissions & Payouts Ledger')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Commissions & Payouts Ledger</h3>
            <p class="text-muted small mb-0">Approve calculated brokerages and record payout transactions</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Booking Ref</th>
                            <th>Channel Partner</th>
                            <th>Agreement Value</th>
                            <th>Rate</th>
                            <th>Calculated</th>
                            <th>Approved Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $comm)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $comm->booking?->booking_number }}</div>
                                    <small class="text-muted">{{ $comm->booking?->customer?->first_name }} {{ $comm->booking?->customer?->last_name }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $comm->channelPartner?->company_name }}</div>
                                    <small class="text-muted font-monospace">{{ $comm->channelPartner?->partner_code }}</small>
                                </td>
                                <td>₹{{ number_format($comm->agreement_value, 2) }}</td>
                                <td>{{ $comm->commission_percentage }}%</td>
                                <td>₹{{ number_format($comm->calculated_commission_amount, 2) }}</td>
                                <td class="fw-bold text-dark">₹{{ number_format($comm->approved_commission_amount, 2) }}</td>
                                <td class="text-success fw-bold">₹{{ number_format($comm->paid_amount, 2) }}</td>
                                <td class="text-danger fw-bold">₹{{ number_format($comm->balance_amount, 2) }}</td>
                                <td>
                                    @if($comm->status === 'Paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($comm->status === 'Approved')
                                        <span class="badge bg-primary">Approved</span>
                                    @elseif($comm->status === 'Payable')
                                        <span class="badge bg-info text-dark">Payable</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $comm->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($comm->status === 'Pending')
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $comm->id }}">
                                            Approve
                                        </button>
                                    @endif

                                    @if(in_array($comm->status, ['Approved', 'Payable']) && $comm->balance_amount > 0)
                                        <button class="btn btn-sm btn-primary ms-1" data-bs-toggle="modal" data-bs-target="#payoutModal{{ $comm->id }}">
                                            Record Payout
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal: Approve -->
                            <div class="modal fade" id="approveModal{{ $comm->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Approve Commission</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('brokers.commissions.approve', $comm) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Calculated Amount</label>
                                                    <input type="text" class="form-control" value="₹{{ number_format($comm->calculated_commission_amount, 2) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Approved Amount (₹)</label>
                                                    <input type="number" step="0.01" name="approved_amount" class="form-control" value="{{ $comm->calculated_commission_amount }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success">Approve Commission</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal: Payout -->
                            <div class="modal fade" id="payoutModal{{ $comm->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Record Payout to {{ $comm->channelPartner?->company_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('brokers.payouts.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="commission_id" value="{{ $comm->id }}">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Outstanding Balance</label>
                                                    <input type="text" class="form-control" value="₹{{ number_format($comm->balance_amount, 2) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Payout Amount (₹)</label>
                                                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ $comm->balance_amount }}" max="{{ $comm->balance_amount }}" required>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Payment Date</label>
                                                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Payment Mode</label>
                                                        <select name="payment_mode" class="form-select" required>
                                                            <option value="NEFT">NEFT</option>
                                                            <option value="RTGS">RTGS</option>
                                                            <option value="Cheque">Cheque</option>
                                                            <option value="UPI">UPI</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Reference / UTR Number</label>
                                                    <input type="text" name="payment_reference" class="form-control" placeholder="e.g. UTR9988776655">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Process Payout</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    No commission records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $commissions->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Payment Collection Ledger - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Financial Collection Ledger</h3>
            <p class="text-muted small mb-0">Record collection receipts, auto-allocate milestones, and manage payment reversals.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('financial-reports.index') }}" class="btn btn-outline-dark rounded-pill px-3">
                <i class="bi bi-graph-up-arrow me-1"></i> Financial Reports
            </a>
            <a href="{{ route('demands.index') }}" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-file-earmark-text me-1"></i> Demand Notices
            </a>
            <a href="{{ route('payments.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-plus-circle me-1"></i> Record New Payment
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('payments.index') }}" method="GET" class="row g-2">
                <div class="col-md-5 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Search payment #, receipt #, UTR / cheque #..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="payment_mode" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All Payment Modes --</option>
                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="Bank Transfer" {{ request('payment_mode') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer (NEFT/RTGS)</option>
                        <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI / Online</option>
                        <option value="Card" {{ request('payment_mode') == 'Card' ? 'selected' : '' }}>Credit/Debit Card</option>
                        <option value="Online Gateway" {{ request('payment_mode') == 'Online Gateway' ? 'selected' : '' }}>Online Gateway</option>
                    </select>
                </div>
                <div class="col-md-2 col-lg-2">
                    <select name="is_reversed" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="0" {{ request('is_reversed') == '0' ? 'selected' : '' }}>Active Receipts</option>
                        <option value="1" {{ request('is_reversed') == '1' ? 'selected' : '' }}>Reversed / Refunded</option>
                    </select>
                </div>
                <div class="col-md-2 col-lg-2 d-flex">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill me-1">Filter</button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Receipt & Payment #</th>
                        <th>Booking & Customer</th>
                        <th>Payment Mode</th>
                        <th>Reference / Cheque #</th>
                        <th>Amount Paid</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($payments as $payment)
                        <tr class="{{ $payment->is_reversed ? 'bg-light text-muted' : '' }}">
                            <td class="ps-4 fw-bold text-dark">
                                <a href="{{ route('payments.show', $payment->id) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $payment->receipt_number ?: $payment->payment_number }}
                                </a>
                                <div class="small text-muted font-monospace">{{ $payment->payment_number }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $payment->booking?->customer?->full_name ?: $payment->customer?->full_name }}</div>
                                <div class="small text-muted">
                                    Booking #{{ $payment->booking?->booking_number }} (Unit #{{ $payment->booking?->unit?->unit_number }})
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $payment->payment_mode ?: $payment->payment_method }}</span>
                            </td>
                            <td class="small font-monospace">
                                {{ $payment->transaction_reference ?: ($payment->bank_cheque_number ?: 'N/A') }}
                            </td>
                            <td>
                                <div class="fw-bold fs-6 {{ $payment->is_reversed ? 'text-decoration-line-through text-muted' : 'text-success' }}">
                                    ₹{{ number_format($payment->amount_paid, 2) }}
                                </div>
                            </td>
                            <td class="small text-muted">
                                {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td>
                                @if($payment->is_reversed)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Reversed / Refunded</span>
                                @else
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Verified</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    Details
                                </a>
                                <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2" title="Printable Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No payment collection transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

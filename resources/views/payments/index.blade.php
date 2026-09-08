@extends('layouts.app')

@section('title', 'Payment Collections')
@section('page-title', 'Financial Payment Receipts & Collections')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('payments.index') }}" method="GET" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search receipt number, transaction reference...">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search me-1"></i> Filter Receipts</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt Ref</th>
                    <th>Booking & Customer</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            <a href="{{ route('payments.receipt', $p->id) }}" class="fw-bold text-dark text-decoration-none" target="_blank">
                                {{ $p->receipt_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $p->booking->customer->full_name ?? 'N/A' }}</div>
                            <small class="text-secondary">Booking #{{ $p->booking->booking_number ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $p->payment_date->format('M d, Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $p->payment_method }}</span></td>
                        <td class="fw-bold text-success">${{ number_format($p->amount_paid, 2) }}</td>
                        <td><span class="badge bg-success">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('payments.receipt', $p->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                Print Receipt <i class="bi bi-printer ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">No payment receipts recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $payments->links() }}
</div>
@endsection

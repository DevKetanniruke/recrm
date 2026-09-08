@extends('layouts.app')

@section('title', 'Unit Bookings')
@section('page-title', 'Unit Bookings & Agreements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="brand-font mb-0">Property Unit Bookings</h5>
    @if(auth()->user()->isSalesAgent() || auth()->user()->isProjectManager())
        <a href="{{ route('bookings.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Create New Unit Booking
        </a>
    @endif
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Booking Ref</th>
                    <th>Customer Name</th>
                    <th>Unit & Project</th>
                    <th>Agreed Price</th>
                    <th>Paid Deposit</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr>
                        <td>
                            <a href="{{ route('bookings.show', $b->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $b->booking_number }}
                            </a>
                            <small class="d-block text-secondary">{{ $b->booking_date->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $b->customer->full_name ?? 'N/A' }}</div>
                            <small class="text-secondary"><i class="bi bi-telephone"></i> {{ $b->customer->phone ?? '' }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">Unit #{{ $b->unit->unit_number ?? 'N/A' }} ({{ $b->unit->unit_type ?? '' }})</div>
                            <small class="text-secondary">{{ $b->unit->floor->wing->building->project->name ?? '' }}</small>
                        </td>
                        <td class="fw-semibold">${{ number_format($b->total_amount, 2) }}</td>
                        <td class="text-success fw-bold">${{ number_format($b->totalPaid(), 2) }}</td>
                        <td>
                            <span class="badge @if($b->status == 'Confirmed') bg-success @elseif($b->status == 'Cancelled') bg-danger @else bg-primary @endif">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('bookings.show', $b->id) }}" class="btn btn-sm btn-light border">
                                Manage Booking <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            No unit bookings found. <a href="{{ route('bookings.create') }}">Create first unit booking</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endsection

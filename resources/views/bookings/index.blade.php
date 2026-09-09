@extends('layouts.app')

@section('title', 'Bookings Management - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Unit Bookings Pipeline</h3>
            <p class="text-muted small mb-0">Transactional unit bookings, customer assignments, and agreement contracts.</p>
        </div>
        <div>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-journal-plus me-1"></i> Create Unit Booking
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('bookings.index') }}" method="GET" class="row g-2">
                <div class="col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Search booking #, customer name, mobile, unit #..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select name="status" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">-- All Booking Statuses --</option>
                        <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Agreement Signed" {{ request('status') == 'Agreement Signed' ? 'selected' : '' }}>Agreement Signed</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2 col-lg-2 d-flex">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill me-1">Filter</button>
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-4">Booking #</th>
                        <th>Customer</th>
                        <th>Project & Unit</th>
                        <th>Agreed Price</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $booking->booking_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $booking->customer?->full_name }}</div>
                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $booking->customer?->primary_mobile }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">Unit #{{ $booking->unit?->unit_number }}</div>
                                <div class="small text-muted">{{ $booking->unit?->project?->name ?: $booking->project?->name }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">₹{{ number_format($booking->agreed_price, 2) }}</div>
                                <div class="small text-muted">Total: ₹{{ number_format($booking->total_amount, 2) }}</div>
                            </td>
                            <td class="small text-muted">
                                {{ $booking->booking_date ? $booking->booking_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td>
                                @if($booking->status == 'Confirmed')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Confirmed</span>
                                @elseif($booking->status == 'Agreement Signed')
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1"><i class="bi bi-file-earmark-check-fill me-1"></i> Agreement Signed</span>
                                @elseif($booking->status == 'Cancelled')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> Cancelled</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">{{ $booking->status }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('bookings.pdf', $booking->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2" title="Printable Confirmation">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No booking records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

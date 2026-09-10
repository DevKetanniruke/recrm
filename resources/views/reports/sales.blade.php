<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Bookings</small>
            <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($reportData['totalBookings']) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Sales Value</small>
            <h3 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($reportData['totalSalesValue'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white border rounded-3 text-center shadow-sm">
            <small class="text-secondary fw-semibold text-uppercase" style="font-size:0.65rem;">Total Discounts Conceded</small>
            <h3 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($reportData['totalDiscounts'], 2) }}</h3>
        </div>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Booking Code</th>
                <th>Customer</th>
                <th>Project</th>
                <th>Building / Wing / Floor / Unit</th>
                <th>Unit Type</th>
                <th>Agreement Value (₹)</th>
                <th>Discount (₹)</th>
                <th>Booking Date</th>
                <th>Sales Executive</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['bookings'] as $booking)
                <tr>
                    <td class="fw-bold text-primary">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-decoration-none">{{ $booking->booking_code }}</a>
                    </td>
                    <td>{{ $booking->customer?->full_name ?? 'N/A' }}</td>
                    <td>{{ $booking->project?->name ?? 'N/A' }}</td>
                    <td>
                        {{ $booking->unit?->building?->name ?? '' }} / 
                        {{ $booking->unit?->wing?->name ?? '' }} / 
                        Fl {{ $booking->unit?->floor?->floor_number ?? '' }} / 
                        <strong>#{{ $booking->unit?->unit_number ?? 'N/A' }}</strong>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $booking->unit?->unitType?->name ?? 'N/A' }}</span></td>
                    <td class="fw-bold text-success">₹{{ number_format($booking->agreement_value, 2) }}</td>
                    <td class="text-danger">₹{{ number_format($booking->discount_amount, 2) }}</td>
                    <td>{{ $booking->created_at->format('Y-m-d') }}</td>
                    <td>{{ $booking->salesAgent?->name ?? 'N/A' }}</td>
                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ $booking->status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-secondary">No booking sales data found matching query parameters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($reportData['bookings'], 'links'))
    <div class="mt-3">
        {{ $reportData['bookings']->links() }}
    </div>
@endif

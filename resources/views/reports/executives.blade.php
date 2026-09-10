<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Executive Name</th>
                <th>Role</th>
                <th>Leads Assigned</th>
                <th>Activities Logged</th>
                <th>Follow-ups</th>
                <th>Site Visits</th>
                <th>Offers Made</th>
                <th>Bookings Won</th>
                <th>Sales Value (₹)</th>
                <th>Lead-to-Booking %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['executives'] as $item)
                @php $u = $item['user']; @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $u->name }}</div>
                        <small class="text-secondary">{{ $u->email }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $u->role) }}</span></td>
                    <td><span class="badge bg-secondary bg-opacity-10 text-dark">{{ number_format($item['leadsAssigned']) }}</span></td>
                    <td>{{ number_format($item['activitiesCount']) }}</td>
                    <td>{{ number_format($item['followupsCount']) }}</td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($item['visitsCount']) }}</span></td>
                    <td>{{ number_format($item['offersCount']) }}</td>
                    <td><span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ number_format($item['bookingsCount']) }}</span></td>
                    <td class="fw-bold text-success">₹{{ number_format($item['salesValue'], 2) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-primary">{{ $item['conversionRate'] }}%</span>
                            <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $item['conversionRate']) }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-secondary">No executive performance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

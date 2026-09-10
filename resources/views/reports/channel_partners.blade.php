<div class="table-responsive bg-white rounded-3 border shadow-sm">
    <table class="table align-middle mb-0" style="font-size:0.875rem;">
        <thead class="table-light">
            <tr>
                <th>Partner Code</th>
                <th>Company Name</th>
                <th>Contact Person</th>
                <th>Mobile</th>
                <th>Leads</th>
                <th>Visits</th>
                <th>Bookings</th>
                <th>Sales Value (₹)</th>
                <th>Total Commissions (₹)</th>
                <th>Paid (₹)</th>
                <th>Outstanding (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['partners'] as $item)
                @php $p = $item['partner']; @endphp
                <tr>
                    <td class="fw-bold text-primary">{{ $p->partner_code }}</td>
                    <td>
                        <a href="{{ route('brokers.partners.show', $p->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $p->company_name }}</a>
                    </td>
                    <td>{{ $p->contact_person }}</td>
                    <td>{{ $p->mobile }}</td>
                    <td><span class="badge bg-secondary bg-opacity-10 text-dark">{{ number_format($item['leadsCount']) }}</span></td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($item['visitsCount']) }}</span></td>
                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ number_format($item['bookingsCount']) }}</span></td>
                    <td class="fw-bold text-success">₹{{ number_format($item['salesValue'], 2) }}</td>
                    <td class="fw-bold text-dark">₹{{ number_format($item['totalCommissions'], 2) }}</td>
                    <td class="text-success">₹{{ number_format($item['paidCommissions'], 2) }}</td>
                    <td class="text-danger fw-bold">₹{{ number_format($item['outstandingCommissions'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-4 text-secondary">No channel partner performance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

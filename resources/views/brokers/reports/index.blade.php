@extends('layouts.app')

@section('title', 'Broker Performance Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Broker & Channel Partner Performance Analytics</h3>
            <p class="text-muted small mb-0">Comprehensive tracking of leads, site visits, bookings, revenue, and commission liabilities</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('brokers.reports.index') }}" class="row g-3">
                <div class="col-md-6">
                    <select name="channel_partner_id" class="form-select">
                        <option value="">All Channel Partners</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}" {{ request('channel_partner_id') == $p->id ? 'selected' : '' }}>{{ $p->company_name }} ({{ $p->partner_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-graph-up"></i> Generate Performance Report</button>
                    <a href="{{ route('brokers.reports.index') }}" class="btn btn-outline-secondary">Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Channel Partner Agency</th>
                            <th>Leads Attributed</th>
                            <th>Visits Driven</th>
                            <th>Bookings Closed</th>
                            <th>Booking Revenue</th>
                            <th>Commission Earned</th>
                            <th>Paid</th>
                            <th>Outstanding Liability</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $row)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row['partner']->company_name }}</div>
                                    <small class="text-muted font-monospace">{{ $row['partner']->partner_code }}</small>
                                </td>
                                <td><span class="badge bg-secondary fs-6">{{ $row['leads_count'] }}</span></td>
                                <td><span class="badge bg-info text-dark fs-6">{{ $row['visits_count'] }}</span></td>
                                <td><span class="badge bg-success fs-6">{{ $row['bookings_count'] }}</span></td>
                                <td class="fw-bold text-dark">₹{{ number_format($row['booking_value'], 2) }}</td>
                                <td class="fw-bold text-primary">₹{{ number_format($row['commission_earned'], 2) }}</td>
                                <td class="text-success fw-bold">₹{{ number_format($row['commission_paid'], 2) }}</td>
                                <td class="text-danger fw-bold">₹{{ number_format($row['commission_outstanding'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No channel partner performance data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

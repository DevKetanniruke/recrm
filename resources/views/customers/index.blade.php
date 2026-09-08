@extends('layouts.app')

@section('title', 'Buyers & KYC')
@section('page-title', 'Customer & Buyer KYC Profiles')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('customers.index') }}" method="GET" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search customer by name, phone, email, PAN...">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search me-1"></i> Search Customers</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>PAN / Tax ID</th>
                    <th>KYC Status</th>
                    <th>Active Bookings</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $cust)
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $cust->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $cust->full_name }}
                            </a>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-telephone text-secondary"></i> {{ $cust->phone }}</div>
                            <div class="small text-secondary">{{ $cust->email ?? 'N/A' }}</div>
                        </td>
                        <td><span class="font-monospace small">{{ $cust->pan_number ?? 'Not Provided' }}</span></td>
                        <td>
                            <span class="badge @if($cust->kyc_status == 'Verified') bg-success @elseif($cust->kyc_status == 'Rejected') bg-danger @else bg-warning text-dark @endif">
                                {{ $cust->kyc_status }}
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $cust->bookings->count() }} Bookings</span></td>
                        <td class="text-end">
                            <a href="{{ route('customers.show', $cust->id) }}" class="btn btn-sm btn-light border">
                                View Profile <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">No customer profiles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $customers->links() }}
</div>
@endsection

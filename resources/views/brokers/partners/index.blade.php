@extends('layouts.app')

@section('title', 'Channel Partner Registry')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Channel Partner (Broker) Registry</h3>
            <p class="text-muted small mb-0">Manage broker agency onboarding, contacts, and status compliance</p>
        </div>
        <a href="{{ route('brokers.partners.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Onboard Channel Partner
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('brokers.partners.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by Partner Code, Agency Name, Contact Person..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Suspended" {{ request('status') === 'Suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="Blacklisted" {{ request('status') === 'Blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Search</button>
                    <a href="{{ route('brokers.partners.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Partner Code</th>
                            <th>Agency Name</th>
                            <th>Primary Contact</th>
                            <th>RERA & Tax IDs</th>
                            <th>Status</th>
                            <th>Onboarded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td><span class="badge bg-primary-subtle text-primary font-monospace fs-6">{{ $partner->partner_code }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $partner->company_name }}</div>
                                    <small class="text-muted">{{ $partner->city ?: 'Location Not Specified' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $partner->contact_person }}</div>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> {{ $partner->mobile }}</small>
                                </td>
                                <td>
                                    <small class="d-block text-dark"><strong>RERA:</strong> {{ $partner->rera_registration_number ?: 'N/A' }}</small>
                                    <small class="text-muted"><strong>GST:</strong> {{ $partner->gst_number ?: 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($partner->status === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($partner->status === 'Inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @elseif($partner->status === 'Suspended')
                                        <span class="badge bg-warning text-dark">Suspended</span>
                                    @else
                                        <span class="badge bg-danger">Blacklisted</span>
                                    @endif
                                </td>
                                <td>{{ $partner->onboarding_date ? $partner->onboarding_date->format('M d, Y') : '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('brokers.partners.show', $partner) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Profile
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No channel partners found matching criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $partners->links() }}
        </div>
    </div>
</div>
@endsection

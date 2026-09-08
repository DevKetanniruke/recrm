@extends('layouts.app')

@section('title', 'Company: ' . $company->name)
@section('page-title', 'Corporate Profile: ' . $company->name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 text-center">
                <div class="bg-primary text-white rounded p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width:70px; height:70px;">
                    <i class="bi bi-building fs-1"></i>
                </div>
                <h4 class="brand-font text-dark mb-1">{{ $company->name }}</h4>
                <p class="text-secondary small mb-3">{{ $company->legal_name ?? $company->name }}</p>

                <div class="border-top pt-3 text-secondary text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>GST Number:</span>
                        <strong class="text-dark font-monospace">{{ $company->gst_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>PAN Number:</span>
                        <strong class="text-dark font-monospace">{{ $company->pan_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>RERA License:</span>
                        <strong class="text-dark font-monospace">{{ $company->tax_id_rera ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Timezone:</span>
                        <strong class="text-dark">{{ $company->default_timezone }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Currency:</span>
                        <strong class="text-dark">{{ $company->currency_code }}</strong>
                    </div>
                </div>

                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-primary w-100 mt-3 fw-semibold">
                    <i class="bi bi-pencil me-1"></i> Edit Corporate Details
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-people me-2 text-primary"></i> Company Assigned Users</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($company->users as $u)
                            <tr>
                                <td>
                                    <a href="{{ route('users.show', $u->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $u->name }}
                                    </a>
                                    <small class="d-block text-secondary">{{ $u->email }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', strtoupper($u->role)) }}</span></td>
                                <td><span class="badge bg-success">{{ $u->status }}</span></td>
                                <td><small class="text-secondary">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-secondary">No users assigned to this company.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

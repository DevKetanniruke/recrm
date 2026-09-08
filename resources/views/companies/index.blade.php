@extends('layouts.app')

@section('title', 'Company Profiles')
@section('page-title', 'Multi-Tenant Company Profiles & Corporate Settings')

@section('content')
<div class="row g-4">
    @forelse($companies as $company)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded p-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                            <i class="bi bi-building fs-3"></i>
                        </div>
                        <div>
                            <h5 class="brand-font text-dark mb-0">{{ $company->name }}</h5>
                            <small class="text-secondary">{{ $company->legal_name ?? $company->name }}</small>
                        </div>
                    </div>

                    <div class="border-top pt-3 text-secondary small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Email:</span>
                            <strong class="text-dark">{{ $company->email ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phone:</span>
                            <strong class="text-dark">{{ $company->phone ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>RERA Reg:</span>
                            <strong class="text-dark">{{ $company->tax_id_rera ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Active Users:</span>
                            <span class="badge bg-light text-dark border">{{ $company->users_count }} Users</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pb-3 pt-0">
                    <a href="{{ route('companies.show', $company->id) }}" class="btn btn-outline-primary w-100 btn-sm fw-semibold">
                        View Corporate Settings <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-building-x fs-1 text-secondary"></i>
            <h5 class="mt-2 text-dark">No Company Profiles Found</h5>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $companies->links() }}
</div>
@endsection

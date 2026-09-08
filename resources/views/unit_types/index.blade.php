@extends('layouts.app')

@section('title', 'Configurable Unit Types')
@section('page-title', 'Configurable Unit Types & Template Catalog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="brand-font mb-0">Property Unit Types</h5>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createUnitTypeModal">
        <i class="bi bi-plus-lg me-1"></i> Add Unit Type Template
    </button>
</div>

<div class="row g-4">
    @forelse($unitTypes as $ut)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1">{{ $ut->category }}</span>
                        <span class="badge bg-light text-dark border font-monospace">{{ $ut->code ?? 'UT-' . $ut->id }}</span>
                    </div>

                    <h5 class="brand-font text-dark mb-1">{{ $ut->name }}</h5>
                    <p class="text-secondary small mb-3">Default Carpet Area: <strong>{{ number_format($ut->default_carpet_area, 2) }} Sq. Ft.</strong></p>

                    <div class="border-top pt-3 text-secondary small d-flex justify-content-between align-items-center">
                        <span>Assigned Units Count:</span>
                        <span class="badge bg-dark">{{ $ut->units_count }} Units</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-sliders2 fs-1 text-secondary"></i>
            <h5 class="mt-2 text-dark">No Unit Types Configured</h5>
            <p class="text-secondary">Create unit type templates (e.g., 2 BHK Premium, Villa Suite) to standardize inventory creation.</p>
        </div>
    @endforelse
</div>

<!-- Modal: Create Unit Type -->
<div class="modal fade" id="createUnitTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('unit-types.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title brand-font">Add Unit Type Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Template Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. 2 BHK Executive Suite" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Type Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="1 BHK">1 BHK</option>
                            <option value="2 BHK" selected>2 BHK</option>
                            <option value="3 BHK">3 BHK</option>
                            <option value="Studio">Studio</option>
                            <option value="Shop">Shop / Retail</option>
                            <option value="Office">Office Commercial</option>
                            <option value="Villa">Villa / Townhouse</option>
                            <option value="Plot">Plot / Land</option>
                            <option value="Custom">Custom Template</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Type Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. 2BHK-EXEC">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Default Carpet Area (Sq. Ft.) *</label>
                        <input type="number" step="0.01" name="default_carpet_area" class="form-control" value="1000" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

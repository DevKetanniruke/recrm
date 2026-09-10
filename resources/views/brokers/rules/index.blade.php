@extends('layouts.app')

@section('title', 'Commission Schemes & Calculation Rules')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Commission Schemes & Calculation Rules</h3>
            <p class="text-muted small mb-0">Define percentage, fixed amount, slab-based, and project-specific broker commission schemes</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRuleModal">
            <i class="bi bi-plus-lg me-1"></i> Add Commission Scheme
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Scheme Name</th>
                            <th>Calculation Type</th>
                            <th>Target Project / Unit Type</th>
                            <th>Commission Rate / Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($structures as $rule)
                            <tr>
                                <td><span class="fw-bold text-dark">{{ $rule->name }}</span></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info text-uppercase font-monospace">{{ $rule->calculation_type }}</span>
                                </td>
                                <td>
                                    {{ $rule->project?->name ?? 'All Projects' }} 
                                    @if($rule->unitType) <span class="badge bg-secondary ms-1">{{ $rule->unitType->name }}</span> @endif
                                </td>
                                <td>
                                    @if($rule->calculation_type === 'percentage')
                                        <span class="fw-bold text-success fs-6">{{ $rule->rate }}%</span>
                                    @elseif($rule->calculation_type === 'fixed_amount')
                                        <span class="fw-bold text-success fs-6">₹{{ number_format($rule->fixed_amount, 2) }}</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Slab / Custom</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No commission schemes created yet. Click "Add Commission Scheme" to define rules.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Rule -->
<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Commission Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('brokers.rules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Scheme Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Standard 2.5% Residential Brokerage" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Calculation Type</label>
                        <select name="calculation_type" class="form-select" required>
                            <option value="percentage">Percentage Rate (%)</option>
                            <option value="fixed_amount">Fixed Amount (₹)</option>
                            <option value="slab_based">Slab Based (Volume Tiered)</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Percentage Rate (%)</label>
                            <input type="number" step="0.01" name="rate" class="form-control" value="2.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fixed Amount (₹)</label>
                            <input type="number" step="0.01" name="fixed_amount" class="form-control" value="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Applicable Project (Optional)</label>
                        <select name="project_id" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

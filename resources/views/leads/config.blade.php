@extends('layouts.app')

@section('title', 'Lead Sources & Statuses Config')
@section('page-title', 'Lead Dynamic Configuration')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Configurable Lead Pipeline Rules</h4>
            <p class="text-muted small mb-0">Customize dynamic Lead Sources and Lead Status stages for your real estate business.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Pipeline
        </a>
    </div>

    <div class="row g-4">
        <!-- Lead Sources Management -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-funnel-fill text-primary me-2"></i> Configurable Lead Sources</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSourceModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Source
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Source Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sources as $src)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $src->name }}</td>
                                        <td>
                                            @if($src->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('lead-config.sources.destroy', $src->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this source?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted">No custom sources configured.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lead Statuses Management -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-steps text-primary me-2"></i> Configurable Lead Pipeline Statuses</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStatusModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Status Stage
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Sort</th>
                                    <th>Stage Name</th>
                                    <th>Color</th>
                                    <th>Type</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statuses as $st)
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">{{ $st->sort_order }}</span></td>
                                        <td class="fw-bold text-dark">{{ $st->name }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $st->color_code }}; color: #fff;">{{ $st->color_code }}</span>
                                        </td>
                                        <td>
                                            @if($st->is_won)
                                                <span class="badge bg-success">Won</span>
                                            @elseif($st->is_lost)
                                                <span class="badge bg-danger">Lost</span>
                                            @else
                                                <span class="badge bg-secondary">Pipeline</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('lead-config.statuses.destroy', $st->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this status stage?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No custom statuses configured.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Source -->
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('lead-config.sources.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Lead Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small fw-bold">Source Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Billboard / Newspaper" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Source</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Status -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('lead-config.statuses.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Status Stage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Stage Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Token Received" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Color Code</label>
                        <input type="color" name="color_code" class="form-control form-control-color w-100" value="#0d6efd">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control form-control-sm" value="10">
                    </div>
                    <div class="col-md-6 d-flex align-items-center gap-3 pt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_won" value="1" id="isWonCheck">
                            <label class="form-check-label small" for="isWonCheck">Is Won Deal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_lost" value="1" id="isLostCheck">
                            <label class="form-check-label small" for="isLostCheck">Is Lost Deal</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Status Stage</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

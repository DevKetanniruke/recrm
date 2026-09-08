@extends('layouts.app')

@section('title', 'Lead Management Pipeline')
@section('page-title', 'Lead Pipeline & CRM Engine')

@section('content')
<div class="container-fluid">
    <!-- Action Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i> Lead Pipeline</h4>
            <p class="text-muted small mb-0">Track, organize, and assign real estate buyer leads across your sales team.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="btn-group me-2" role="group">
                <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'list'])) }}" class="btn btn-outline-primary btn-sm {{ $viewMode === 'list' ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i> List
                </a>
                <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" class="btn btn-outline-primary btn-sm {{ $viewMode === 'kanban' ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Kanban
                </a>
            </div>

            <a href="{{ route('leads.duplicates') }}" class="btn btn-outline-warning btn-sm shadow-sm me-1">
                <i class="bi bi-intersect"></i> Duplicate Check
            </a>
            <a href="{{ route('leads.import.form') }}" class="btn btn-outline-secondary btn-sm shadow-sm me-1">
                <i class="bi bi-file-earmark-arrow-up"></i> Import
            </a>
            <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-outline-success btn-sm shadow-sm me-1">
                <i class="bi bi-file-earmark-arrow-down"></i> Export
            </a>

            @if(auth()->user()->hasPermissionTo('leads.create'))
                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Lead
                </a>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('leads.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search name, mobile, email..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->name }}" {{ request('status') == $st->name ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="Hot" {{ request('priority') == 'Hot' ? 'selected' : '' }}>🔥 Hot</option>
                        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src->name }}" {{ request('source') == $src->name ? 'selected' : '' }}>{{ $src->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter"></i></button>
                    <a href="{{ route('leads.index', ['view' => $viewMode]) }}" class="btn btn-sm btn-light border" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Content Views: LIST vs KANBAN -->
    @if($viewMode === 'list')
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Lead #</th>
                            <th>Customer Name</th>
                            <th>Contact Info</th>
                            <th>Project & Unit</th>
                            <th>Source</th>
                            <th>Budget</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $lead->lead_number ?? ('LD-' . $lead->id) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $lead->full_name }}</div>
                                    @if($lead->city) <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $lead->city }}</small> @endif
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-secondary me-1"></i> {{ $lead->mobile }}</div>
                                    @if($lead->email)
                                        <small class="text-muted"><i class="bi bi-envelope text-secondary me-1"></i> {{ $lead->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->project)
                                        <span class="badge bg-light text-dark border"><i class="bi bi-building me-1"></i> {{ $lead->project->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($lead->unit_type)
                                        <div><small class="text-muted">{{ $lead->unit_type }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">{{ $lead->source }}</span>
                                </td>
                                <td>
                                    @if($lead->minimum_budget || $lead->maximum_budget)
                                        <small class="fw-semibold text-dark">
                                            ₹{{ number_format($lead->minimum_budget / 100000, 1) }}L - ₹{{ number_format($lead->maximum_budget / 100000, 1) }}L
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $prioColor = match($lead->priority) {
                                            'Hot' => 'danger',
                                            'High' => 'warning text-dark',
                                            'Medium' => 'info text-dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $prioColor }}">
                                        @if($lead->priority === 'Hot') 🔥 @endif {{ $lead->priority }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusObj = $statuses->firstWhere('name', $lead->status);
                                        $bgColor = $statusObj ? $statusObj->color_code : '#6c757d';
                                    @endphp
                                    <span class="badge" style="background-color: {{ $bgColor }}; color: #fff;">
                                        {{ $lead->status }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-dark fw-semibold">
                                        <i class="bi bi-person-circle me-1 text-secondary"></i> {{ $lead->assignedTo?->name ?? 'Unassigned' }}
                                    </small>
                                    @if($lead->assignedTeam)
                                        <div><small class="text-muted"><i class="bi bi-people me-1"></i> Team: {{ $lead->assignedTeam->name }}</small></div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="View Timeline">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    No leads found matching criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leads->hasPages())
                <div class="card-footer bg-white py-2 border-0">
                    {{ $leads->withQueryString()->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- KANBAN BOARD VIEW -->
        <div class="d-flex flex-row flex-nowrap overflow-auto gap-3 pb-3" style="min-height: 70vh;">
            @foreach($statuses as $st)
                @php
                    $boardLeads = $leadsByStatus[$st->name] ?? collect();
                @endphp
                <div class="card border-0 shadow-sm flex-shrink-0" style="width: 320px; background-color: #f8f9fa;">
                    <div class="card-header border-0 py-3 px-3 d-flex justify-content-between align-items-center" style="border-top: 4px solid {{ $st->color_code }} !important; background-color: #fff;">
                        <h6 class="mb-0 fw-bold text-dark">{{ $st->name }}</h6>
                        <span class="badge rounded-pill bg-secondary bg-opacity-25 text-dark">{{ $boardLeads->count() }}</span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-2 overflow-auto" style="max-height: 75vh;">
                        @forelse($boardLeads as $l)
                            <div class="card border-0 shadow-sm bg-white p-3 rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <a href="{{ route('leads.show', $l->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $l->full_name }}
                                    </a>
                                    <span class="badge bg-{{ $l->priority === 'Hot' ? 'danger' : ($l->priority === 'High' ? 'warning text-dark' : 'light text-dark border') }} style="font-size:0.7rem;">
                                        {{ $l->priority }}
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">
                                    <div><i class="bi bi-telephone text-secondary me-1"></i> {{ $l->mobile }}</div>
                                    @if($l->project)
                                        <div><i class="bi bi-building text-secondary me-1"></i> {{ $l->project->name }}</div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light">
                                    <small class="text-secondary"><i class="bi bi-person me-1"></i> {{ $l->assignedTo?->name ?? 'Unassigned' }}</small>
                                    <a href="{{ route('leads.show', $l->id) }}" class="btn btn-sm btn-light border py-0 px-2" style="font-size:0.75rem;">
                                        View <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No leads in {{ $st->name }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

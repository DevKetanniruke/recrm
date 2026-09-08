@extends('layouts.app')

@section('title', 'Duplicate Lead Checker & Merge Tool')
@section('page-title', 'Duplicate Lead Review')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold brand-font text-dark"><i class="bi bi-intersect text-warning me-2"></i> Duplicate Lead Review & Consolidation</h4>
            <p class="text-muted small mb-0">Identify duplicate leads with matching mobile numbers or emails and consolidate timeline data into a primary record.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Pipeline
        </a>
    </div>

    @if(empty($duplicateGroups))
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No Duplicate Leads Detected!</h5>
                <p class="text-muted small mb-0">Your CRM database is clean. All active leads have unique contact numbers.</p>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($duplicateGroups as $groupIndex => $group)
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning bg-opacity-10 py-3 border-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Duplicate Cluster #{{ $groupIndex + 1 }} — Mobile: {{ $group->first()->mobile }} ({{ $group->count() }} Leads Found)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <form action="{{ route('leads.merge') }}" method="POST">
                                @csrf
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered align-middle mb-0" style="font-size:0.875rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 120px;">Primary Record</th>
                                                <th>Lead Number</th>
                                                <th>Customer Name</th>
                                                <th>Contact & Email</th>
                                                <th>Project & Source</th>
                                                <th>Created Date</th>
                                                <th>Assigned To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group as $index => $lead)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="radio" name="primary_lead_id" value="{{ $lead->id }}" id="prim_{{ $lead->id }}" {{ $index === 0 ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-primary">{{ $lead->lead_number }}</td>
                                                    <td class="fw-semibold text-dark">{{ $lead->full_name }}</td>
                                                    <td>
                                                        <div><i class="bi bi-telephone text-secondary me-1"></i> {{ $lead->mobile }}</div>
                                                        <small class="text-muted">{{ $lead->email ?? '—' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">{{ $lead->project?->name ?? 'N/A' }}</span>
                                                        <small class="text-muted ms-1">({{ $lead->source }})</small>
                                                    </td>
                                                    <td class="small text-muted">{{ $lead->created_at->format('Y-m-d H:i') }}</td>
                                                    <td class="small text-dark fw-semibold">{{ $lead->assignedTo?->name ?? 'Unassigned' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @foreach($group as $lead)
                                    <input type="hidden" name="secondary_lead_ids[]" value="{{ $lead->id }}">
                                @endforeach

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning btn-sm text-dark px-4 shadow-sm" onclick="return confirm('Are you sure you want to merge these secondary leads into the selected primary record?')">
                                        <i class="bi bi-intersect me-1"></i> Merge Duplicate Cluster into Selected Primary
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

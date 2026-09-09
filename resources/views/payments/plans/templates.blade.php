@extends('layouts.app')

@section('title', 'Payment Plan Templates - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3" x-data="{
    milestones: [
        { milestone_name: 'Token & Booking Advance', milestone_code: 'BOOKING', percentage: 10, trigger_days: 0 },
        { milestone_name: 'Agreement Execution & Registration', milestone_code: 'AGREEMENT', percentage: 10, trigger_days: 30 },
        { milestone_name: 'Completion of Plinth / Substructure', milestone_code: 'PLINTH', percentage: 20, trigger_days: 90 },
        { milestone_name: 'Completion of Roof Slab', milestone_code: 'SLAB', percentage: 30, trigger_days: 180 },
        { milestone_name: 'Possession & Handover', milestone_code: 'POSSESSION', percentage: 30, trigger_days: 365 }
    ]
}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Configurable Payment Plan Templates</h3>
            <p class="text-muted small mb-0">Define multi-structured milestone templates (Construction Linked, Down Payment, Time-Bound) for unit bookings.</p>
        </div>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createTemplateModal" class="btn btn-primary shadow-sm rounded-pill px-4">
                <i class="bi bi-plus-circle me-1"></i> Create Plan Template
            </button>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row g-4 mb-4">
        @forelse($templates as $tpl)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $tpl->name }}</h5>
                                <span class="badge bg-light text-secondary border font-monospace">{{ $tpl->code ?: 'TPL-' . $tpl->id }}</span>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span>
                        </div>
                        <p class="text-muted small mb-3">{{ $tpl->description ?: 'No description provided.' }}</p>

                        <h6 class="fw-semibold text-dark small mb-2"><i class="bi bi-list-task me-1"></i> Milestone Breakdown:</h6>
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            @foreach(($tpl->milestones_json ?? []) as $m)
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <span>{{ $m['milestone_name'] ?? $m['name'] }}</span>
                                    <span class="fw-bold text-primary">{{ $m['percentage'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-diagram-3 fs-1 text-secondary opacity-50 d-block mb-2"></i>
                    No payment plan templates configured yet. Click "Create Plan Template" to set up milestone structures.
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal to Create Payment Plan Template -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('payment-plans.templates.store') }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3 text-primary me-2"></i> New Payment Plan Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill" placeholder="e.g. 10:20:30:40 Construction Linked Plan" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code / Tag</label>
                        <input type="text" name="code" class="form-control rounded-pill" placeholder="CLP-10-90">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief plan overview..."></textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2">Milestone Schedule Structure</h6>
                <template x-for="(ms, idx) in milestones" :key="idx">
                    <div class="row g-2 align-items-center bg-light p-2 rounded mb-2 border">
                        <div class="col-md-4">
                            <input type="text" :name="'milestones[' + idx + '][milestone_name]'" x-model="ms.milestone_name" class="form-control form-control-sm" placeholder="Milestone Name" required>
                        </div>
                        <div class="col-md-3">
                            <select :name="'milestones[' + idx + '][milestone_code]'" x-model="ms.milestone_code" class="form-select form-select-sm">
                                <option value="BOOKING">Booking Advance</option>
                                <option value="AGREEMENT">Agreement Execution</option>
                                <option value="PLINTH">Plinth / Substructure</option>
                                <option value="SLAB">Roof Slab</option>
                                <option value="BRICKWORK">Brickwork & Plaster</option>
                                <option value="FLOORING">Flooring & Tiling</option>
                                <option value="POSSESSION">Possession & Handover</option>
                                <option value="OTHER">Other Milestone</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" :name="'milestones[' + idx + '][percentage]'" x-model="ms.percentage" class="form-control form-control-sm" placeholder="%" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" :name="'milestones[' + idx + '][trigger_days]'" x-model="ms.trigger_days" class="form-control form-control-sm" placeholder="Days">
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" @click="milestones.splice(idx, 1)" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="milestones.push({ milestone_name: '', milestone_code: 'OTHER', percentage: 0, trigger_days: 30 })" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-1">
                    <i class="bi bi-plus-lg me-1"></i> Add Milestone Row
                </button>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Template</button>
            </div>
        </form>
    </div>
</div>
@endsection

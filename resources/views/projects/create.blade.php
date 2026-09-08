@extends('layouts.app')

@section('title', 'Create Project')
@section('page-title', 'Create Development Project (V0.2 Specs)')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-secondary">Project Name *</label>
                    <input type="text" name="project_name" class="form-control" placeholder="e.g. Skyline Grand Heights" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Project Code</label>
                    <input type="text" name="project_code" class="form-control" placeholder="e.g. SGH-2026">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Project Type *</label>
                    <select name="project_type" class="form-select" required>
                        <option value="Residential">Residential</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Mixed">Mixed Development</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Project Status *</label>
                    <select name="project_status" class="form-select" required>
                        <option value="Planning">Planning</option>
                        <option value="Under Construction" selected>Under Construction</option>
                        <option value="Ready to Possess">Ready to Possess</option>
                        <option value="Completed">Completed</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Assign Project Manager</label>
                    <select name="project_manager_id" class="form-select">
                        <option value="">-- Select Manager --</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->role }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Total Land Area (Sq. Ft.)</label>
                    <input type="number" step="0.01" name="total_land_area" class="form-control" placeholder="250000">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">RERA Registration No.</label>
                    <input type="text" name="RERA_number" class="form-control" placeholder="e.g. RERA-TX-88771">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">RERA Registration Date</label>
                    <input type="date" name="RERA_registration_date" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Expected Completion Date</label>
                    <input type="date" name="expected_completion" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">City</label>
                    <input type="text" name="city" class="form-control" placeholder="Austin">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">State</label>
                    <input type="text" name="state" class="form-control" placeholder="Texas">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Pincode</label>
                    <input type="text" name="pincode" class="form-control" placeholder="78701">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold text-secondary">Full Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Site Address">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-secondary">Project Overview Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Overview of project features, amenities, location..."></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('projects.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Create Project</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Add New Customer Profile - Real Estate CRM')

@section('content')
<div class="container-fluid px-4 py-3" x-data="{ coApplicants: [] }">
    <div class="mb-4">
        <a href="{{ route('customers.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Customer Registry</a>
        <h3 class="fw-bold text-dark mb-0">Create New Customer Profile</h3>
    </div>

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        
        <!-- Primary Customer Info Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-primary me-2"></i> Primary Applicant Details</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Alternate Mobile</label>
                        <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">PAN Card Number</label>
                        <input type="text" name="PAN" class="form-control text-uppercase" placeholder="ABCDE1234F" value="{{ old('PAN') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Occupation</label>
                        <input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Employer / Organization</label>
                        <input type="text" name="company_or_employer" class="form-control" value="{{ old('company_or_employer') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Permanent Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Co-Applicants Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-people text-info me-2"></i> Co-Applicants (Optional)</h5>
                <button type="button" @click="coApplicants.push({ customer_name: '', relationship: 'Spouse', mobile: '', email: '', ownership_percentage: 0, pan_number: '' })" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Co-Applicant
                </button>
            </div>
            <div class="card-body p-4">
                <template x-for="(co, index) in coApplicants" :key="index">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-secondary" x-text="'Co-Applicant #' + (index + 1)"></h6>
                            <button type="button" @click="coApplicants.splice(index, 1)" class="btn btn-sm btn-outline-danger border-0">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Co-Applicant Full Name</label>
                                <input type="text" :name="'co_applicants[' + index + '][customer_name]'" x-model="co.customer_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Relationship</label>
                                <select :name="'co_applicants[' + index + '][relationship]'" x-model="co.relationship" class="form-select form-select-sm">
                                    <option value="Spouse">Spouse</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Child">Child</option>
                                    <option value="Sibling">Sibling</option>
                                    <option value="Business Partner">Business Partner</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Mobile</label>
                                <input type="text" :name="'co_applicants[' + index + '][mobile]'" x-model="co.mobile" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" :name="'co_applicants[' + index + '][email]'" x-model="co.email" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Ownership %</label>
                                <input type="number" step="0.01" :name="'co_applicants[' + index + '][ownership_percentage]'" x-model="co.ownership_percentage" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="coApplicants.length === 0" class="text-muted small text-center py-3">
                    No co-applicants added. Click "Add Co-Applicant" to include joint owners.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('customers.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-5">Save Customer Profile</button>
        </div>
    </form>
</div>
@endsection

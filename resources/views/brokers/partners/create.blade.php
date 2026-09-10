@extends('layouts.app')

@section('title', 'Onboard Channel Partner')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Onboard Channel Partner (Broker)</h3>
        <p class="text-muted small">Register agency profile, primary contact details, RERA compliance, and tax identifiers</p>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('brokers.partners.store') }}" method="POST">
                        @csrf

                        <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="bi bi-building me-2"></i> Agency & Primary Contact Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Broker Agency / Company Name</label>
                                <input type="text" name="company_name" class="form-control" placeholder="e.g. Apex Realty Consultants Ltd" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Primary Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" placeholder="e.g. Robert Vance" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" placeholder="9876543210" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="robert@apexrealty.com">
                            </div>
                        </div>

                        <h5 class="fw-bold border-bottom pb-2 mb-3 mt-4 text-primary"><i class="bi bi-card-checklist me-2"></i> Compliance & Tax Registration</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">RERA Registration Number</label>
                                <input type="text" name="rera_registration_number" class="form-control" placeholder="A518000998877">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">GST Number</label>
                                <input type="text" name="gst_number" class="form-control" placeholder="27AAACA12341Z1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">PAN Card Number</label>
                                <input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F">
                            </div>
                        </div>

                        <h5 class="fw-bold border-bottom pb-2 mb-3 mt-4 text-primary"><i class="bi bi-geo-alt me-2"></i> Office Location & Status</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Street Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Suite 402, Financial Tower">
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">City</label>
                                <input type="text" name="city" class="form-control" placeholder="Metropolis">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">State</label>
                                <input type="text" name="state" class="form-control" placeholder="State">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Pincode</label>
                                <input type="text" name="pincode" class="form-control" placeholder="400001">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Onboard Partner</button>
                            <a href="{{ route('brokers.partners.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Company: ' . $company->name)
@section('page-title', 'Edit Corporate Settings: ' . $company->name)

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Legal Business Name</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Website URL</label>
                    <input type="url" name="website" value="{{ old('website', $company->website) }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">GST Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $company->gst_number) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">PAN Number</label>
                    <input type="text" name="pan_number" value="{{ old('pan_number', $company->pan_number) }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">RERA Registration / License</label>
                    <input type="text" name="tax_id_rera" value="{{ old('tax_id_rera', $company->tax_id_rera) }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">City</label>
                    <input type="text" name="city" value="{{ old('city', $company->city) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">State</label>
                    <input type="text" name="state" value="{{ old('state', $company->state) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Pincode / Zip</label>
                    <input type="text" name="pincode" value="{{ old('pincode', $company->pincode) }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Default Timezone</label>
                    <select name="default_timezone" class="form-select">
                        <option value="America/Chicago" {{ $company->default_timezone == 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST)</option>
                        <option value="America/New_York" {{ $company->default_timezone == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        <option value="UTC" {{ $company->default_timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Currency Code</label>
                    <select name="currency_code" class="form-select">
                        <option value="USD" {{ $company->currency_code == 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ $company->currency_code == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ $company->currency_code == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                    </select>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('companies.show', $company->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Update Corporate Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

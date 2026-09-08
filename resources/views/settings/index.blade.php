@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Preferences & Configuration')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('settings.store') }}" method="POST">
            @csrf
            
            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-sliders me-1"></i> General Application Settings</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Application Name</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'PropFlow Real Estate CRM' }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Pagination Page Limit</label>
                    <input type="number" name="pagination_limit" value="{{ $settings['pagination_limit'] ?? '15' }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Default Date Format</label>
                    <select name="date_format" class="form-select">
                        <option value="M d, Y" {{ ($settings['date_format'] ?? '') == 'M d, Y' ? 'selected' : '' }}>M d, Y (e.g. Sep 08, 2026)</option>
                        <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>Y-m-d (e.g. 2026-09-08)</option>
                        <option value="d/m/Y" {{ ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>d/m/Y (e.g. 08/09/2026)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Default Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '$' }}" class="form-control">
                </div>
            </div>

            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-bell me-1"></i> Notification Preferences</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="notify_lead_assigned" value="1" id="notifyLead" {{ ($settings['notify_lead_assigned'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="notifyLead">Email Notification on Lead Assignment</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="notify_payment_overdue" value="1" id="notifyPay" {{ ($settings['notify_payment_overdue'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="notifyPay">Daily Payment Milestone Overdue Digest</label>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top text-end">
                <button type="submit" class="btn btn-primary fw-semibold px-4">Save Preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection

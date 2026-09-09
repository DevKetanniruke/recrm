@extends('layouts.app')

@section('title', isset($template) ? 'Edit Communication Template' : 'Create Communication Template')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">{{ isset($template) ? 'Edit Communication Template (v'.$template->version.')' : 'Create Communication Template' }}</h3>
        <p class="text-muted small">Configure dynamic multi-channel template with placeholder variables</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <form action="{{ isset($template) ? route('communication.templates.update', $template) : route('communication.templates.store') }}" method="POST">
                        @csrf
                        @if(isset($template))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">Template Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}" placeholder="e.g. Site Visit Confirmation Notice" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Channel</label>
                                <select name="channel" class="form-select" required>
                                    <option value="email" {{ (old('channel', $template->channel ?? '') === 'email') ? 'selected' : '' }}>Email</option>
                                    <option value="sms" {{ (old('channel', $template->channel ?? '') === 'sms') ? 'selected' : '' }}>SMS</option>
                                    <option value="whatsapp" {{ (old('channel', $template->channel ?? '') === 'whatsapp') ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="in_app" {{ (old('channel', $template->channel ?? '') === 'in_app') ? 'selected' : '' }}>In-App Notification</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active" {{ (old('status', $template->status ?? '') === 'Active') ? 'selected' : '' }}>Active</option>
                                    <option value="Draft" {{ (old('status', $template->status ?? '') === 'Draft') ? 'selected' : '' }}>Draft</option>
                                    <option value="Archived" {{ (old('status', $template->status ?? '') === 'Archived') ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject Line (Email / In-App)</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject ?? '') }}" placeholder="e.g. Hello {{ '{{customer_name}}' }}, your site visit is confirmed!">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Body</label>
                            <textarea name="body" class="form-control" rows="6" required placeholder="Dear {{ '{{customer_name}}' }}, thank you for booking unit {{ '{{unit_number}}' }} in {{ '{{project_name}}' }}...">{{ old('body', $template->body ?? '') }}</textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="is_transactional" value="1" id="txCheck" {{ old('is_transactional', $template->is_transactional ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="txCheck">
                                Transactional Message (Bypasses marketing opt-outs)
                            </label>
                            <div class="form-text">Check for critical notices like booking confirmations, payment receipts, or demand notices.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ isset($template) ? 'Update Template' : 'Save Template' }}</button>
                            <a href="{{ route('communication.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-code-slash text-primary me-2"></i> Available Placeholders</h5>
                    <p class="small text-muted mb-3">Copy and paste these variable tokens into your subject or body:</p>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{customer_name}}' }}</code> <span>Customer / Lead Name</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{project_name}}' }}</code> <span>Project Name</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{unit_number}}' }}</code> <span>Unit Identifier</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{booking_number}}' }}</code> <span>Booking Number</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{payment_amount}}' }}</code> <span>Amount Paid / Due</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{due_date}}' }}</code> <span>Payment / Visit Date</span></li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between"><code>{{ '{{sales_executive}}' }}</code> <span>Assigned Agent Name</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

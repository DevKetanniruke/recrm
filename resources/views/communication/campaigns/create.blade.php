@extends('layouts.app')

@section('title', 'Create Marketing Campaign')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Create Marketing Campaign</h3>
        <p class="text-muted small">Configure audience targeting, communication template, and schedule</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('communication.campaigns.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Campaign Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Festival Launch Promo Email" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Channel</label>
                                <select name="channel" class="form-select" required>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="in_app">In-App</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Communication Template</label>
                                <select name="communication_template_id" class="form-select" required>
                                    <option value="">-- Select Active Template --</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ strtoupper($tpl->channel) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Target Project (Optional)</label>
                                <select name="project_id" class="form-select">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Scheduled Execution Date (Optional)</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control">
                                <div class="form-text">Leave blank to keep as draft or launch manually.</div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">Audience Segmentation Filters</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label small">Lead Status</label>
                                        <input type="text" name="lead_status" class="form-control form-control-sm" placeholder="e.g. Qualified, Contacted">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Customer Status</label>
                                        <input type="text" name="customer_status" class="form-control form-control-sm" placeholder="e.g. Active">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Campaign</button>
                            <a href="{{ route('communication.campaigns.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Communication Templates Engine')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Communication Templates Engine</h3>
            <p class="text-muted small mb-0">Manage multi-channel dynamic templates with placehoder variable hydration</p>
        </div>
        <a href="{{ route('communication.templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Create Template
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Template Name</th>
                            <th>Channel</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th>Subject / Preview</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $tpl)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $tpl->name }}</div>
                                    <small class="text-muted">Updated {{ $tpl->updated_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($tpl->channel === 'email')
                                        <span class="badge bg-primary-subtle text-primary"><i class="bi bi-envelope"></i> Email</span>
                                    @elseif($tpl->channel === 'sms')
                                        <span class="badge bg-info-subtle text-info"><i class="bi bi-chat-text"></i> SMS</span>
                                    @elseif($tpl->channel === 'whatsapp')
                                        <span class="badge bg-success-subtle text-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning"><i class="bi bi-bell"></i> In-App</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tpl->is_transactional)
                                        <span class="badge bg-dark">Transactional</span>
                                    @else
                                        <span class="badge bg-secondary">Marketing</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">v{{ $tpl->version }}</span></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;">
                                        {{ $tpl->subject ?: Str::limit($tpl->body, 40) }}
                                    </div>
                                </td>
                                <td>
                                    @if($tpl->status === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($tpl->status === 'Draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @else
                                        <span class="badge bg-secondary">Archived</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('communication.templates.edit', $tpl) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No communication templates found. Click "Create Template" to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $templates->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Roles & Permission Matrix')
@section('page-title', 'Configurable Roles & Permission Mapping Matrix')

@section('content')
<div class="row g-4 mb-4" x-data="{ activeRoleId: {{ $roles->first()->id ?? 0 }} }">
    <!-- Left Column: Role Selector & Create Role -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 brand-font fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i> Defined System Roles</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div class="list-group list-group-flush p-2">
                @foreach($roles as $r)
                    <button type="button" 
                            class="list-group-item list-group-item-action border-0 rounded-3 d-flex justify-content-between align-items-center mb-1"
                            :class="{ 'bg-primary text-white fw-bold': activeRoleId === {{ $r->id }} }"
                            @click="activeRoleId = {{ $r->id }}">
                        <div>
                            <div>{{ $r->name }}</div>
                            <small :class="activeRoleId === {{ $r->id }} ? 'text-white-50' : 'text-secondary'" style="font-size:0.75rem;">
                                {{ $r->users->count() }} Users Assigned
                            </small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $r->permissions->count() }} Perms</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Permission Matrix Editor -->
    <div class="col-lg-8">
        @foreach($roles as $r)
            <div class="card border-0 shadow-sm rounded-4 mb-4" x-show="activeRoleId === {{ $r->id }}">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h6 class="mb-0 brand-font fw-bold text-dark">Permissions Matrix: {{ $r->name }}</h6>
                        <small class="text-secondary">{{ $r->description ?? 'Configure module action permissions' }}</small>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('roles.update-permissions', $r->id) }}" method="POST">
                        @csrf
                        
                        @foreach($permissionsByModule as $module => $perms)
                            <div class="mb-4">
                                <h6 class="text-uppercase text-secondary fw-bold small border-bottom pb-1 mb-2">
                                    <i class="bi bi-folder2-open me-1"></i> Module: {{ strtoupper($module) }}
                                </h6>
                                <div class="row g-2">
                                    @foreach($perms as $perm)
                                        <div class="col-md-6">
                                            <div class="form-check p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                                <div>
                                                    <input class="form-check-input ms-0 me-2" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $perm->id }}" 
                                                           id="perm_{{ $r->id }}_{{ $perm->id }}"
                                                           {{ $r->permissions->contains($perm->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold text-dark" for="perm_{{ $r->id }}_{{ $perm->id }}">
                                                        {{ $perm->name }}
                                                    </label>
                                                </div>
                                                <span class="badge bg-white text-secondary border font-monospace" style="font-size:0.68rem;">{{ $perm->slug }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="pt-3 border-top text-end">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                Save Permission Matrix for {{ $r->name }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal: Create Role -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title brand-font">Create New Configurable Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Role Title *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sales Manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Responsibilities and scope..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

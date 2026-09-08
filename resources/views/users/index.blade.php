@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management & Authorization')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search user by name, email, mobile...">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" {{ request('role') == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Filter</button>
                @if(auth()->user()->hasPermissionTo('users.create'))
                    <a href="{{ route('users.create') }}" class="btn btn-primary text-nowrap"><i class="bi bi-person-plus-fill me-1"></i> Add User</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User Details</th>
                    <th>Role</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px;">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('users.show', $user->id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $user->name }}
                                    </a>
                                    <small class="d-block text-secondary">{{ $user->email }} | {{ $user->mobile ?? 'No Mobile' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', strtoupper($user->role)) }}</span>
                        </td>
                        <td>{{ $user->company->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge @if($user->status == 'Active') bg-success @elseif($user->status == 'Suspended') bg-danger @else bg-secondary @endif">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td>
                            <small class="text-secondary">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </small>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Manage
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-menu-item dropdown-item" href="{{ route('users.show', $user->id) }}"><i class="bi bi-eye me-2"></i> View Profile & Logs</a></li>
                                    @if(auth()->user()->hasPermissionTo('users.edit'))
                                        <li><a class="dropdown-menu-item dropdown-item" href="{{ route('users.edit', $user->id) }}"><i class="bi bi-pencil me-2"></i> Edit Account</a></li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">No users found matching filter criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection

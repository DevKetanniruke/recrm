@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)
@section('page-title', 'Edit User Account: ' . $user->name)

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Mobile Number</label>
                    <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Assigned Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">Default Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ $user->company_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">System Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}" {{ $user->role == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Account Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Active" {{ $user->status == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $user->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="Suspended" {{ $user->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Reset Password (Optional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Leave blank to keep unchanged">
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Update User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

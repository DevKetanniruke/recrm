@extends('layouts.app')

@section('title', 'Add New User')
@section('page-title', 'Create User Account')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. John Smith" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="john@company.com" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control" placeholder="+1 (555) 019-2831">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Assigned Company</label>
                    <select name="company_id" class="form-select">
                        <option value="">Default Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ auth()->user()->company_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">System Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Account Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-secondary">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*">
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Create User Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

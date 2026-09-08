@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile & Account Settings')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-800">
    <div class="card-body p-4">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-person-gear me-1"></i> Personal Account Details</h6>

            <div class="row g-3 mb-4">
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
                    <label class="form-label fw-semibold text-secondary">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*">
                </div>
            </div>

            <h6 class="brand-font fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-shield-lock me-1"></i> Change Password</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Required if changing password">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="form-control">
                </div>
            </div>

            <div class="pt-3 border-top text-end">
                <button type="submit" class="btn btn-primary fw-semibold px-4">Update Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection

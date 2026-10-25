@extends('layouts.app', [
    'active' => 'users',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Add User'],
    ],
])

@section('title', 'Add User')
@section('heading', 'Add New User')
@section('subheading', 'Create a KFHI CMS account and assign the appropriate role.')

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('users.index') }}">Back to User Management</a>
@endsection

@section('content')
<form class="k-card k-stack" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="k-alert k-alert-info">
        <strong>Account information</strong><br>
        Create an individual account for each KFHI staff member. The assigned role determines which features and information the user can access.
    </div>

    <div class="k-form-row">
        <div class="k-field">
            <label for="profile_photo">Profile picture</label>
            <input class="k-input" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg,image/webp">
            <p class="k-help">JPG, JPEG, PNG or WEBP. Maximum size: 2MB.</p>
            @error('profile_photo')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="name">Full name <span class="req">*</span></label>
            <input class="k-input" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
            @error('name')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="username">Username <span class="req">*</span></label>
            <input class="k-input" type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Enter username" required>
            @error('username')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="email">Email address <span class="req">*</span></label>
            <input class="k-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
            @error('email')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="phone">Phone number</label>
            <input class="k-input" type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Enter phone number">
            @error('phone')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="role">Role <span class="req">*</span></label>
            <select class="k-select" id="role" name="role" required>
                <option value="">Select role</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                <option value="child_officer" @selected(old('role') === 'child_officer')>Child Officer</option>
                <option value="field_officer" @selected(old('role') === 'field_officer')>Field Officer</option>
                <option value="enumerator" @selected(old('role') === 'enumerator')>Enumerator</option>
            </select>
            @error('role')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="status">Status <span class="req">*</span></label>
            <select class="k-select" id="status" name="status" required>
                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
            </select>
            @error('status')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="password">Password <span class="req">*</span></label>
            <input class="k-input" type="password" id="password" name="password" placeholder="Enter password" required>
            @error('password')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="password_confirmation">Confirm password <span class="req">*</span></label>
            <input class="k-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
        </div>
    </div>

    <div class="k-page-actions">
        <a class="k-btn k-btn-secondary" href="{{ route('users.index') }}">Cancel</a>
        <button class="k-btn k-btn-primary" type="submit">Create User</button>
    </div>
</form>
@endsection

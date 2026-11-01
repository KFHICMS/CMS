@extends('layouts.app', [
    'active' => 'users',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Edit User'],
    ],
])

@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('subheading', 'Update this user’s account information and access role.')

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('users.index') }}">Back to User Management</a>
@endsection

@section('content')
<form class="k-card k-stack" action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="k-alert k-alert-info">
        <strong>Account information</strong><br>
        Leave the password fields empty if you do not want to change the current password.
    </div>

    <div class="k-field">
        <label for="profile_photo">Profile picture</label>
        <div class="child">
            @if ($user->profile_photo)
                <img src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }}">
            @else
                <span class="avatar" aria-hidden="true">{{ mb_substr($user->name ?? 'U', 0, 1) }}</span>
            @endif
            <div>
                <input class="k-input" type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg,image/webp">
                <p class="k-help">Select a new profile picture to replace the current one. JPG, JPEG, PNG or WEBP. Maximum size: 2MB.</p>
                @error('profile_photo')<p class="k-error-text">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="k-form-row">
        <div class="k-field">
            <label for="name">Full name <span class="req">*</span></label>
            <input class="k-input" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
            @error('name')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="username">Username <span class="req">*</span></label>
            <input class="k-input" type="text" id="username" name="username" value="{{ old('username', $user->username) }}" placeholder="Enter username" required>
            @error('username')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="email">Email address <span class="req">*</span></label>
            <input class="k-input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Enter email address" required>
            @error('email')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="phone">Phone number</label>
            <input class="k-input" type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Enter phone number">
            @error('phone')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="role">Role <span class="req">*</span></label>
            <select class="k-select" id="role" name="role" required>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                <option value="child_officer" @selected(old('role', $user->role) === 'child_officer')>Child Officer</option>
                <option value="field_officer" @selected(old('role', $user->role) === 'field_officer')>Field Officer</option>
                <option value="enumerator" @selected(old('role', $user->role) === 'enumerator')>Enumerator</option>
            </select>
            @error('role')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="status">Status <span class="req">*</span></label>
            <select class="k-select" id="status" name="status" required>
                <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
            </select>
            @error('status')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="password">New password</label>
            <input class="k-input" type="password" id="password" name="password" placeholder="Enter new password">
            <p class="k-help">Leave empty to keep the current password.</p>
            @error('password')<p class="k-error-text">{{ $message }}</p>@enderror
        </div>

        <div class="k-field">
            <label for="password_confirmation">Confirm new password</label>
            <input class="k-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
        </div>
    </div>

    <div class="k-page-actions">
        <a class="k-btn k-btn-secondary" href="{{ route('users.index') }}">Cancel</a>
        <button class="k-btn k-btn-primary" type="submit">Update User</button>
    </div>
</form>
@endsection

@extends('layouts.app')

@section('title', 'My Profile')

@section('breadcrumbs')
<span class="text-gray-900 font-medium">Profile</span>
@endsection

@section('content')
<div class="max-w-2xl">
<div class="page-header">
    <h1 class="page-title">Profile Settings</h1>
</div>

<form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
@csrf
@method('PUT')

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold">Personal Information</h3></div>
    <div class="card-body space-y-4">
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-16 h-16 rounded-full">
            <div>
                <p class="font-medium text-gray-900">{{ $user->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-xs text-gray-400">{{ $user->getRoleNames()->join(', ') }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-input" required>
            </div>
        </div>
        <div>
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="+234...">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold">Change Password</h3></div>
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-input" autocomplete="current-password">
            @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-input" autocomplete="new-password">
            @error('new_password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="new_password_confirmation" class="form-input" autocomplete="new-password">
        </div>
    </div>
</div>

<div class="flex justify-end">
    <button type="submit" class="btn-primary">Save Changes</button>
</div>

</form>
</div>
@endsection

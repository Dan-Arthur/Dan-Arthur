@extends('layouts.app')

@section('title', 'Edit ' . $user->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600">Users</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('users.show', $user) }}" class="hover:text-blue-600">{{ $user->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit User — {{ $user->name }}</h1>
    </div>
</div>

<form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name"
                        value="{{ old('first_name', $user->first_name) }}"
                        class="form-input @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name"
                        value="{{ old('last_name', $user->last_name) }}"
                        class="form-input @error('last_name') border-red-500 @enderror" required>
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email"
                        value="{{ old('email', $user->email) }}"
                        class="form-input @error('email') border-red-500 @enderror" required>
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        class="form-input" placeholder="+234…">
                </div>
            </div>
            @if($campuses->count() > 1)
            <div class="mt-4">
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">All / Main Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}"
                        @selected(old('campus_id', $user->campus_id) == $campus->id)>
                        {{ $campus->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Change Password
            </h3>
            <p class="text-xs text-gray-400 mb-4">Leave blank to keep the current password.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ show: false }">
                <div class="relative">
                    <label class="form-label">New Password</label>
                    <input :type="show ? 'text' : 'password'" name="password"
                        class="form-input pr-10 @error('password') border-red-500 @enderror"
                        placeholder="Min. 8 chars">
                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-8 text-gray-400 hover:text-gray-600">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirm Password</label>
                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                        class="form-input" placeholder="Repeat new password">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Role & Status
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" class="form-select @error('role') border-red-500 @enderror" required>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                            @selected(old('role', $user->roles->first()?->name) === $role->name)>
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Account Status</label>
                    <select name="status" class="form-select">
                        <option value="active"    @selected(old('status', $user->status) === 'active')>Active</option>
                        <option value="inactive"  @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                        <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                    </select>
                    @if($user->id === auth()->id())
                    <p class="text-xs text-amber-500 mt-1">You cannot deactivate your own account.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Account Details</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Created</span>
                    <span class="text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Login</span>
                    <span class="text-gray-900 dark:text-white">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">Save Changes</button>
            <a href="{{ route('users.show', $user) }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

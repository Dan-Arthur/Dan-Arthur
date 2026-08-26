@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600">Users</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Add User</span>
        </div>
        <h1 class="page-title">Add New User</h1>
    </div>
</div>

<form method="POST" action="{{ route('users.store') }}" class="space-y-6">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Personal & Account --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                        class="form-input @error('first_name') border-red-500 @enderror"
                        placeholder="Jane" required>
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                        class="form-input @error('last_name') border-red-500 @enderror"
                        placeholder="Doe" required>
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-input @error('email') border-red-500 @enderror"
                        placeholder="jane.doe@school.edu" required>
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="form-input" placeholder="+234…">
                </div>
            </div>
            @if($campuses->count() > 1)
            <div class="mt-4">
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">All / Main Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                        {{ $campus->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Login Credentials
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ show: false }">
                <div class="relative">
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input :type="show ? 'text' : 'password'" name="password"
                        class="form-input pr-10 @error('password') border-red-500 @enderror"
                        placeholder="Min. 8 chars, letters + numbers" required>
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
                    <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                        class="form-input" placeholder="Repeat password" required>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Minimum 8 characters with at least one letter and one number. The user can change their password after logging in.
            </p>
        </div>
    </div>

    {{-- RIGHT: Role & Status --}}
    <div class="space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Role & Access
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Assign Role <span class="text-red-500">*</span></label>
                    <select name="role" class="form-select @error('role') border-red-500 @enderror" required>
                        <option value="">Select a role…</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Account Status</label>
                    <select name="status" class="form-select">
                        <option value="active"   @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Inactive users cannot log in.</p>
                </div>
            </div>
        </div>

        {{-- Role descriptions --}}
        <div class="card p-5 text-xs space-y-3 text-gray-600 dark:text-gray-400">
            <p class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">Role Reference</p>
            @foreach([
                'school-admin'  => 'Full school access, all modules',
                'principal'     => 'Academic oversight, reports, announcements',
                'vice-principal'=> 'Attendance, discipline, class oversight',
                'teacher'       => 'Classes, attendance, marks, results',
                'accountant'    => 'Fees, invoices, payments, reports',
                'hr-officer'    => 'Staff records, leave management',
                'librarian'     => 'Books, loans, library reports',
                'receptionist'  => 'Admissions, students, guardians',
                'parent'        => 'Child results, fees, announcements',
                'student'       => 'Results, timetable, announcements',
            ] as $r => $desc)
            <div class="flex gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300 w-28 flex-shrink-0">
                    {{ ucwords(str_replace('-', ' ', $r)) }}
                </span>
                <span>{{ $desc }}</span>
            </div>
            @endforeach
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create User
            </button>
            <a href="{{ route('users.index') }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

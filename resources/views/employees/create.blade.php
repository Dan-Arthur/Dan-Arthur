@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Add Employee</h1>
        <p class="page-subtitle">Create a new staff record</p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('employees.store') }}" class="max-w-3xl space-y-6">
    @csrf

    {{-- Personal --}}
    <div class="card">
        <h2 class="card-title mb-4">Personal Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Title</label>
                <select name="title" class="form-select">
                    <option value="">—</option>
                    @foreach (\App\Models\Employee::TITLES as $t)
                        <option value="{{ $t }}" {{ old('title') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">—</option>
                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">First Name <span class="required">*</span></label>
                <input type="text" name="first_name" class="form-input" value="{{ old('first_name') }}" required>
                @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Last Name <span class="required">*</span></label>
                <input type="text" name="last_name" class="form-input" value="{{ old('last_name') }}" required>
                @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Other Names</label>
                <input type="text" name="other_names" class="form-input" value="{{ old('other_names') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-input" value="{{ old('date_of_birth') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Nationality</label>
                <input type="text" name="nationality" class="form-input" value="{{ old('nationality') }}">
            </div>
            <div class="form-group">
                <label class="form-label">National ID / Passport</label>
                <input type="text" name="national_id" class="form-input" value="{{ old('national_id') }}">
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="card">
        <h2 class="card-title mb-4">Contact Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Alt. Phone</label>
                <input type="text" name="alt_phone" class="form-input" value="{{ old('alt_phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}">
            </div>
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Employment --}}
    <div class="card">
        <h2 class="card-title mb-4">Employment</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Position</label>
                <select name="position_id" class="form-select">
                    <option value="">— Select Position —</option>
                    @foreach ($positions as $pos)
                        <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                            {{ $pos->title }} ({{ \App\Models\Position::TYPES[$pos->type] ?? $pos->type }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">— Select Department —</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">— Select Campus —</option>
                    @foreach ($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Employment Type</label>
                <select name="employment_type" class="form-select">
                    @foreach (\App\Models\Employee::EMPLOYMENT_TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('employment_type', 'full_time') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Joining Date</label>
                <input type="date" name="joining_date" class="form-input" value="{{ old('joining_date') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Link to System User</label>
                <select name="user_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-input" value="{{ old('qualification') }}"
                       placeholder="e.g. B.Ed, M.Sc">
            </div>
            <div class="form-group">
                <label class="form-label">Specialisation</label>
                <input type="text" name="specialisation" class="form-input" value="{{ old('specialisation') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Years of Experience</label>
                <input type="number" name="years_experience" class="form-input" value="{{ old('years_experience') }}" min="0">
            </div>
        </div>
    </div>

    {{-- Payroll --}}
    <div class="card">
        <h2 class="card-title mb-4">Payroll & Bank Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Basic Salary</label>
                <input type="number" name="basic_salary" class="form-input" value="{{ old('basic_salary') }}" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="{{ old('bank_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Account Number</label>
                <input type="text" name="bank_account" class="form-input" value="{{ old('bank_account') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Sort Code</label>
                <input type="text" name="bank_sort_code" class="form-input" value="{{ old('bank_sort_code') }}">
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Create Employee</button>
        <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

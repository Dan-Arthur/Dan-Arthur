@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Employee</h1>
        <p class="page-subtitle">{{ $employee->full_name }}</p>
    </div>
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('employees.update', $employee) }}" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    {{-- Personal --}}
    <div class="card">
        <h2 class="card-title mb-4">Personal Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Title</label>
                <select name="title" class="form-select">
                    <option value="">—</option>
                    @foreach (\App\Models\Employee::TITLES as $t)
                        <option value="{{ $t }}" {{ old('title', $employee->title) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">—</option>
                    <option value="male"   {{ old('gender', $employee->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other"  {{ old('gender', $employee->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">First Name <span class="required">*</span></label>
                <input type="text" name="first_name" class="form-input" value="{{ old('first_name', $employee->first_name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name <span class="required">*</span></label>
                <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $employee->last_name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Other Names</label>
                <input type="text" name="other_names" class="form-input" value="{{ old('other_names', $employee->other_names) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-input"
                       value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Nationality</label>
                <input type="text" name="nationality" class="form-input" value="{{ old('nationality', $employee->nationality) }}">
            </div>
            <div class="form-group">
                <label class="form-label">National ID / Passport</label>
                <input type="text" name="national_id" class="form-input" value="{{ old('national_id', $employee->national_id) }}">
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="card">
        <h2 class="card-title mb-4">Contact Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone', $employee->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Alt. Phone</label>
                <input type="text" name="alt_phone" class="form-input" value="{{ old('alt_phone', $employee->alt_phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $employee->email) }}">
            </div>
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-textarea" rows="2">{{ old('address', $employee->address) }}</textarea>
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
                        <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>
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
                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">— Select Campus —</option>
                    @foreach ($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ old('campus_id', $employee->campus_id) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Employment Type</label>
                <select name="employment_type" class="form-select">
                    @foreach (\App\Models\Employee::EMPLOYMENT_TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('employment_type', $employee->employment_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    @foreach (\App\Models\Employee::STATUSES as $key => $meta)
                        <option value="{{ $key }}" {{ old('status', $employee->status) === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Joining Date</label>
                <input type="date" name="joining_date" class="form-input"
                       value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Exit Date</label>
                <input type="date" name="exit_date" class="form-input"
                       value="{{ old('exit_date', $employee->exit_date?->format('Y-m-d')) }}">
            </div>
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Exit Reason</label>
                <textarea name="exit_reason" class="form-textarea" rows="2">{{ old('exit_reason', $employee->exit_reason) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Link to System User</label>
                <select name="user_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-input" value="{{ old('qualification', $employee->qualification) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Specialisation</label>
                <input type="text" name="specialisation" class="form-input" value="{{ old('specialisation', $employee->specialisation) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Years of Experience</label>
                <input type="number" name="years_experience" class="form-input"
                       value="{{ old('years_experience', $employee->years_experience) }}" min="0">
            </div>
        </div>
    </div>

    {{-- Payroll --}}
    <div class="card">
        <h2 class="card-title mb-4">Payroll & Bank Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Basic Salary</label>
                <input type="number" name="basic_salary" class="form-input"
                       value="{{ old('basic_salary', $employee->basic_salary) }}" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="{{ old('bank_name', $employee->bank_name) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Account Number</label>
                <input type="text" name="bank_account" class="form-input" value="{{ old('bank_account', $employee->bank_account) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Sort Code</label>
                <input type="text" name="bank_sort_code" class="form-input" value="{{ old('bank_sort_code', $employee->bank_sort_code) }}">
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost">Cancel</a>
        @can('delete staff')
        <form method="POST" action="{{ route('employees.destroy', $employee) }}"
              class="ml-auto" onsubmit="return confirm('Delete this employee record?')">
            @csrf @method('DELETE')
            <button class="btn btn-ghost text-danger">Delete Record</button>
        </form>
        @endcan
    </div>
</form>
@endsection

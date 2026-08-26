@extends('layouts.app')

@section('title', 'Add Student')

@section('breadcrumbs')
<a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<a href="{{ route('students.index') }}" class="text-gray-500 hover:text-gray-700">Students</a>
<span class="text-gray-400 mx-1">/</span>
<span class="text-gray-900 font-medium">Add Student</span>
@endsection

@section('content')
<form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" class="space-y-6">
@csrf

<div class="page-header">
    <div>
        <h1 class="page-title">Add New Student</h1>
        <p class="page-subtitle">Fill in the student's information below</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('students.index') }}" class="btn-secondary btn-sm">Cancel</a>
        <button type="submit" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save Student
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left column: Photo & Quick info --}}
    <div class="space-y-4">

        {{-- Photo upload --}}
        <div class="card card-body text-center" x-data="{ preview: null }">
            <div class="relative inline-block">
                <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto overflow-hidden">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover">
                    <div x-show="!preview" class="w-full h-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
            </div>
            <label class="block mt-3">
                <span class="btn-secondary btn-sm cursor-pointer">Upload Photo</span>
                <input type="file" name="photo" accept="image/*" class="hidden"
                    @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }">
            </label>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 2MB</p>
        </div>

        {{-- Status & Class --}}
        <div class="card card-body space-y-4">
            <div>
                <label class="form-label">Enrollment Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Current Class</label>
                <select name="current_class_id" class="form-select">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('current_class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}{{ $class->section ? ' (' . $class->section . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Admission Date</label>
                <input type="date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">House / Team</label>
                <input type="text" name="house" value="{{ old('house') }}" class="form-input" placeholder="e.g. Red House">
            </div>
        </div>
    </div>

    {{-- Right columns: Main form --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Personal Information --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required class="form-input @error('first_name') border-red-500 @enderror" placeholder="First name">
                        @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input @error('last_name') border-red-500 @enderror" placeholder="Last name">
                        @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Other Names</label>
                        <input type="text" name="other_names" value="{{ old('other_names') }}" class="form-input" placeholder="Middle name">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" class="form-select @error('gender') border-red-500 @enderror" required>
                            <option value="">Select</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', 'Nigerian') }}" class="form-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Religion</label>
                        <select name="religion" class="form-select">
                            <option value="">Select</option>
                            <option value="Christianity" {{ old('religion') === 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Other" {{ old('religion') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">Unknown</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Genotype</label>
                        <select name="genotype" class="form-select">
                            <option value="">Unknown</option>
                            @foreach(['AA','AS','SS','AC','SC'] as $gt)
                            <option value="{{ $gt }}" {{ old('genotype') === $gt ? 'selected' : '' }}>{{ $gt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-gray-900">Contact Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+234...">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="student@email.com">
                    </div>
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input" placeholder="Home address">{{ old('address') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- Previous Education --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-gray-900">Previous Education</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Previous School</label>
                        <input type="text" name="previous_school" value="{{ old('previous_school') }}" class="form-input" placeholder="Previous school name">
                    </div>
                    <div>
                        <label class="form-label">Previous Class</label>
                        <input type="text" name="previous_class" value="{{ old('previous_class') }}" class="form-input" placeholder="e.g. JSS 2">
                    </div>
                </div>
            </div>
        </div>

        {{-- Save button --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('students.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Student
            </button>
        </div>
    </div>
</div>

</form>
@endsection

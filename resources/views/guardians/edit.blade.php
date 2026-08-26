@extends('layouts.app')

@section('title', 'Edit ' . $guardian->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('guardians.index') }}" class="hover:text-blue-600">Guardians</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('guardians.show', $guardian) }}" class="hover:text-blue-600">{{ $guardian->full_name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit Guardian</h1>
    </div>
</div>

<form method="POST" action="{{ route('guardians.update', $guardian) }}" class="space-y-6">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Personal Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Title</label>
                    <select name="title" class="form-select">
                        <option value="">—</option>
                        @foreach(['Mr','Mrs','Ms','Dr','Prof','Engr','Chief','Alhaji','Alhaja'] as $t)
                        <option value="{{ $t }}" @selected(old('title', $guardian->title) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $guardian->first_name) }}"
                        class="form-input @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $guardian->last_name) }}"
                        class="form-input @error('last_name') border-red-500 @enderror" required>
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Other Names</label>
                    <input type="text" name="other_names" value="{{ old('other_names', $guardian->other_names) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="male"   @selected(old('gender', $guardian->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $guardian->gender) === 'female')>Female</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Relationship</label>
                    <select name="relationship" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Step-Father','Step-Mother','Sibling','Other'] as $rel)
                        <option value="{{ $rel }}" @selected(old('relationship', $guardian->relationship) === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $guardian->nationality) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Phone <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $guardian->phone) }}"
                        class="form-input @error('phone') border-red-500 @enderror" required>
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Alt. Phone</label>
                    <input type="tel" name="alt_phone" value="{{ old('alt_phone', $guardian->alt_phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $guardian->email) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Occupation</label>
                    <input type="text" name="occupation" value="{{ old('occupation', $guardian->occupation) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Employer / Business</label>
                    <input type="text" name="employer" value="{{ old('employer', $guardian->employer) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div class="sm:col-span-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input resize-none">{{ old('address', $guardian->address) }}</textarea>
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city', $guardian->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="{{ old('state', $guardian->state) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">National ID No.</label>
                    <input type="text" name="national_id" value="{{ old('national_id', $guardian->national_id) }}" class="form-input">
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Contact Preferences
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="is_primary_contact" value="0">
                    <input type="checkbox" name="is_primary_contact" value="1"
                        class="mt-0.5 rounded border-gray-300 text-blue-600"
                        @checked(old('is_primary_contact', $guardian->is_primary_contact))>
                    <div>
                        <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Primary Contact</p>
                        <p class="text-xs text-gray-500">Main point of contact for school communications</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="is_emergency_contact" value="0">
                    <input type="checkbox" name="is_emergency_contact" value="1"
                        class="mt-0.5 rounded border-gray-300 text-blue-600"
                        @checked(old('is_emergency_contact', $guardian->is_emergency_contact))>
                    <div>
                        <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Emergency Contact</p>
                        <p class="text-xs text-gray-500">Can be contacted in case of an emergency</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Settings</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active"   @selected(old('status', $guardian->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $guardian->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Portal Access</p>
                    <span class="badge {{ $guardian->portal_access ? 'badge-success' : 'badge-gray' }}">
                        {{ $guardian->portal_access ? 'Enabled' : 'Disabled' }}
                    </span>
                    <p class="text-xs text-gray-400 mt-1">Toggle from the profile page.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">Save Changes</button>
            <a href="{{ route('guardians.show', $guardian) }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

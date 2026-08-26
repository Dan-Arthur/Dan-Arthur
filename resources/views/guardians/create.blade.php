@extends('layouts.app')

@section('title', 'Add Guardian')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('guardians.index') }}" class="hover:text-blue-600">Guardians</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Add Guardian</span>
        </div>
        <h1 class="page-title">Add Guardian</h1>
    </div>
</div>

<form method="POST" action="{{ route('guardians.store') }}" class="space-y-6">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Personal Info --}}
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
                        <option value="{{ $t }}" @selected(old('title') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                        class="form-input @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                        class="form-input @error('last_name') border-red-500 @enderror" required>
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Other Names</label>
                    <input type="text" name="other_names" value="{{ old('other_names') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="male"   @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Relationship to Child</label>
                    <select name="relationship" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Step-Father','Step-Mother','Sibling','Other'] as $rel)
                        <option value="{{ $rel }}" @selected(old('relationship') === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', 'Nigerian') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Phone <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="form-input @error('phone') border-red-500 @enderror" required placeholder="+234…">
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Alt. Phone</label>
                    <input type="tel" name="alt_phone" value="{{ old('alt_phone') }}" class="form-input" placeholder="+234…">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Occupation</label>
                    <input type="text" name="occupation" value="{{ old('occupation') }}" class="form-input" placeholder="e.g. Banker, Teacher">
                </div>
                <div>
                    <label class="form-label">Employer / Business</label>
                    <input type="text" name="employer" value="{{ old('employer') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div class="sm:col-span-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input resize-none">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="{{ old('state') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">National ID No.</label>
                    <input type="text" name="national_id" value="{{ old('national_id') }}" class="form-input" placeholder="NIN / Passport No.">
                </div>
            </div>
        </div>

        {{-- Contact Preferences --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Contact Preferences
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="is_primary_contact" value="0">
                    <input type="checkbox" name="is_primary_contact" value="1"
                        class="mt-0.5 rounded border-gray-300 text-blue-600"
                        @checked(old('is_primary_contact', true))>
                    <div>
                        <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Primary Contact</p>
                        <p class="text-xs text-gray-500">Main point of contact for school communications</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="is_emergency_contact" value="0">
                    <input type="checkbox" name="is_emergency_contact" value="1"
                        class="mt-0.5 rounded border-gray-300 text-blue-600"
                        @checked(old('is_emergency_contact', false))>
                    <div>
                        <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Emergency Contact</p>
                        <p class="text-xs text-gray-500">Can be contacted in case of an emergency</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Link to Student --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                Link to Student
            </h3>
            <p class="text-xs text-gray-500 mb-4">Optionally link this guardian to a student immediately.</p>

            <div class="space-y-4">
                <div>
                    <label class="form-label">Select Student</label>
                    <select name="link_student_id" class="form-select">
                        <option value="">— No student (link later) —</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}"
                            @selected(old('link_student_id', $linkStudent?->id) == $student->id)>
                            {{ $student->full_name }} ({{ $student->student_number }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div x-data="{ show: {{ $linkStudent ? 'true' : 'false' }} }"
                     x-show="document.querySelector('[name=link_student_id]')?.value !== ''"
                     class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="form-label">Relationship (to this student)</label>
                        <select name="pivot_relationship" class="form-select">
                            <option value="">Same as above</option>
                            @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Step-Father','Step-Mother','Sibling','Other'] as $rel)
                            <option value="{{ $rel }}" @selected(old('pivot_relationship') === $rel)>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2 pt-1">
                        @foreach([
                            ['pivot_is_primary','Primary guardian for this student'],
                            ['pivot_is_emergency','Emergency contact for this student'],
                            ['pivot_can_pickup','Authorised to pick up student'],
                            ['pivot_receives_reports','Receives report cards'],
                            ['pivot_receives_invoices','Receives fee invoices'],
                        ] as [$name, $label])
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1"
                                class="rounded border-gray-300 text-blue-600"
                                @checked(old($name, in_array($name, ['pivot_can_pickup','pivot_receives_reports'])))>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Settings --}}
    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Settings
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 mb-2">Portal access can be enabled after saving.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Guardian
            </button>
            <a href="{{ route('guardians.index') }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

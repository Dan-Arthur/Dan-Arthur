@extends('layouts.app')

@section('title', 'New Application')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('admissions.index') }}" class="hover:text-blue-600">Admissions</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>New Application</span>
        </div>
        <h1 class="page-title">New Admission Application</h1>
    </div>
</div>

<form method="POST" action="{{ route('admissions.store') }}" class="space-y-6">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Applicant Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Personal Info --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Applicant Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                    <label class="form-label">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" class="form-select @error('gender') border-red-500 @enderror" required>
                        <option value="">Select gender</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    @error('gender')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                        class="form-input" max="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', 'Nigerian') }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Religion</label>
                    <select name="religion" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Christianity','Islam','Traditional','Other'] as $rel)
                        <option value="{{ $rel }}" @selected(old('religion') === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+234...">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input"
                        placeholder="applicant@email.com">
                </div>
                <div>
                    <label class="form-label">Previous School</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school') }}"
                        class="form-input" placeholder="Last school attended">
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label">Home Address</label>
                <textarea name="address" rows="2" class="form-input resize-none"
                    placeholder="Street address, city, state">{{ old('address') }}</textarea>
            </div>
        </div>

        {{-- Guardian Info --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Parent / Guardian Information
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                        class="form-input" placeholder="Guardian full name">
                </div>
                <div>
                    <label class="form-label">Relationship</label>
                    <select name="guardian_relation" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Other'] as $rel)
                        <option value="{{ $rel }}" @selected(old('guardian_relation') === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}"
                        class="form-input" placeholder="+234...">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" class="form-input">
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label">Guardian Address (if different)</label>
                <textarea name="guardian_address" rows="2" class="form-input resize-none">{{ old('guardian_address') }}</textarea>
            </div>
        </div>

        {{-- Notes --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Additional Notes
            </h3>
            <textarea name="notes" rows="3" class="form-input resize-none"
                placeholder="Any special notes, medical conditions, or considerations…">{{ old('notes') }}</textarea>
        </div>
    </div>

    {{-- RIGHT: Application Settings --}}
    <div class="space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Application Settings
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="form-label">Academic Year <span class="text-red-500">*</span></label>
                    <select name="academic_year_id" class="form-select @error('academic_year_id') border-red-500 @enderror" required>
                        <option value="">Select year</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            @selected(old('academic_year_id', $currentYear?->id) == $year->id)>
                            {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Class Applying For</label>
                    <select name="applied_class_id" class="form-select">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('applied_class_id') == $class->id)>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Or type free text below if class isn't listed</p>
                </div>

                <div>
                    <label class="form-label">Class Description (free text)</label>
                    <input type="text" name="applying_for_class" value="{{ old('applying_for_class') }}"
                        class="form-input" placeholder="e.g. JSS 1, Primary 4…">
                </div>

                @if($campuses->count() > 1)
                <div>
                    <label class="form-label">Campus</label>
                    <select name="campus_id" class="form-select">
                        <option value="">Select campus</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                            {{ $campus->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="form-label">Application Date</label>
                    <input type="date" name="application_date"
                        value="{{ old('application_date', date('Y-m-d')) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Initial Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" @selected(old('status') === 'draft')>Draft (save privately)</option>
                        <option value="submitted" @selected(old('status') === 'submitted')>Submitted (ready for review)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Application
            </button>
            <a href="{{ route('admissions.index') }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

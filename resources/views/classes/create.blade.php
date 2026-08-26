@extends('layouts.app')

@section('title', 'New Class')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('classes.index') }}" class="hover:text-blue-600">Classes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>New Class</span>
        </div>
        <h1 class="page-title">Create New Class</h1>
    </div>
</div>

<form method="POST" action="{{ route('classes.store') }}" class="space-y-6">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Class Details --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Class Identity
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-input @error('name') border-red-500 @enderror"
                        placeholder="e.g. JSS 1, Form 3, Year 7" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">The display name used everywhere in the system.</p>
                </div>
                <div>
                    <label class="form-label">Class Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="form-input font-mono @error('code') border-red-500 @enderror"
                        placeholder="JSS1" required>
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Level / Year <span class="text-red-500">*</span></label>
                    <input type="number" name="level" value="{{ old('level', 1) }}"
                        min="1" max="20" class="form-input @error('level') border-red-500 @enderror" required>
                    @error('level')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Used for ordering. JSS 1 = 1, JSS 2 = 2…</p>
                </div>
                <div>
                    <label class="form-label">Section / Arm</label>
                    <input type="text" name="section" value="{{ old('section') }}"
                        class="form-input" placeholder="A, B, Gold, Blue…">
                    <p class="text-xs text-gray-400 mt-1">Stream or division within the class.</p>
                </div>
                <div>
                    <label class="form-label">Programme / Track</label>
                    <input type="text" name="programme" value="{{ old('programme') }}"
                        class="form-input" placeholder="Science, Arts, Commercial…"
                        list="programme-suggestions">
                    <datalist id="programme-suggestions">
                        @foreach(['Science','Arts','Commercial','Technical','General','Vocational'] as $p)
                        <option value="{{ $p }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Capacity & Location
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Student Capacity <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" value="{{ old('capacity', 40) }}"
                        min="1" max="200" class="form-input @error('capacity') border-red-500 @enderror" required>
                    @error('capacity')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Classroom / Room</label>
                    <input type="text" name="room" value="{{ old('room') }}"
                        class="form-input" placeholder="Block A, Room 12…">
                </div>
                @if($campuses->count() > 1)
                <div>
                    <label class="form-label">Campus</label>
                    <select name="campus_id" class="form-select">
                        <option value="">Main Campus</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                            {{ $campus->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @if($departments->count() > 0)
            <div class="mt-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">— No department —</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Class Teacher
            </h3>
            @if($teachers->count() > 0)
            <div>
                <label class="form-label">Assign Class Teacher</label>
                <select name="class_teacher_id" class="form-select">
                    <option value="">— Unassigned —</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected(old('class_teacher_id') == $teacher->id)>
                        {{ $teacher->name }}
                        @if($teacher->email) ({{ $teacher->email }}) @endif
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Only teachers and staff are listed. Add staff in User Management first.</p>
            </div>
            @else
            <p class="text-sm text-gray-500">No teachers found. <a href="{{ route('users.create') }}" class="text-blue-600 hover:underline">Create a staff account</a> first.</p>
            @endif
        </div>
    </div>

    {{-- RIGHT: Settings --}}
    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">
                Settings
            </h3>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    class="mt-0.5 rounded border-gray-300 text-blue-600"
                    @checked(old('is_active', true))>
                <div>
                    <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Active</p>
                    <p class="text-xs text-gray-500">Inactive classes are hidden from enrolment forms and timetables.</p>
                </div>
            </label>
        </div>

        <div class="card p-5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
            <h4 class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider mb-2">
                Tip: Naming Convention
            </h4>
            <div class="text-xs text-blue-600 dark:text-blue-400 space-y-1">
                <p><strong>JSS 1 A</strong> → Name: JSS 1, Code: JSS1, Section: A, Level: 1</p>
                <p><strong>SS 2 Science</strong> → Name: SS 2, Code: SS2, Section: Sci, Programme: Science, Level: 5</p>
                <p><strong>Primary 4</strong> → Name: Primary 4, Code: PRI4, Level: 4</p>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create Class
            </button>
            <a href="{{ route('classes.index') }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

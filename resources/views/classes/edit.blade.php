@extends('layouts.app')

@section('title', 'Edit ' . $class->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('classes.index') }}" class="hover:text-blue-600">Classes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('classes.show', $class) }}" class="hover:text-blue-600">{{ $class->full_name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit Class — {{ $class->full_name }}</h1>
    </div>
</div>

<form method="POST" action="{{ route('classes.update', $class) }}" class="space-y-6">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-5">
                Class Identity
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Class Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $class->name) }}"
                        class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Class Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $class->code) }}"
                        class="form-input font-mono @error('code') border-red-500 @enderror" required>
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="form-label">Level / Year <span class="text-red-500">*</span></label>
                    <input type="number" name="level" value="{{ old('level', $class->level) }}"
                        min="1" max="20" class="form-input @error('level') border-red-500 @enderror" required>
                    @error('level')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Section / Arm</label>
                    <input type="text" name="section" value="{{ old('section', $class->section) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Programme / Track</label>
                    <input type="text" name="programme" value="{{ old('programme', $class->programme) }}"
                        class="form-input" list="programme-suggestions">
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
                    <input type="number" name="capacity" value="{{ old('capacity', $class->capacity) }}"
                        min="1" max="200" class="form-input @error('capacity') border-red-500 @enderror" required>
                    @error('capacity')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Classroom / Room</label>
                    <input type="text" name="room" value="{{ old('room', $class->room) }}" class="form-input">
                </div>
                @if($campuses->count() > 1)
                <div>
                    <label class="form-label">Campus</label>
                    <select name="campus_id" class="form-select">
                        <option value="">Main Campus</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}"
                            @selected(old('campus_id', $class->campus_id) == $campus->id)>
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
                    <option value="{{ $dept->id }}"
                        @selected(old('department_id', $class->department_id) == $dept->id)>
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
            <div>
                <label class="form-label">Assign Class Teacher</label>
                <select name="class_teacher_id" class="form-select">
                    <option value="">— Unassigned —</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                        @selected(old('class_teacher_id', $class->class_teacher_id) == $teacher->id)>
                        {{ $teacher->name }}
                        @if($teacher->email) ({{ $teacher->email }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Settings</h3>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    class="mt-0.5 rounded border-gray-300 text-blue-600"
                    @checked(old('is_active', $class->is_active))>
                <div>
                    <p class="font-medium text-sm text-gray-700 dark:text-gray-300">Active</p>
                    <p class="text-xs text-gray-500">Inactive classes are hidden from enrolment and timetable forms.</p>
                </div>
            </label>
        </div>

        {{-- Quick stats --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Current Status</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Enrolled</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $class->students()->count() }} students
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Capacity</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $class->capacity }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit" class="btn-primary justify-center py-3">Save Changes</button>
            <a href="{{ route('classes.show', $class) }}" class="btn-secondary justify-center">Cancel</a>
        </div>
    </div>

</div>
</form>
@endsection

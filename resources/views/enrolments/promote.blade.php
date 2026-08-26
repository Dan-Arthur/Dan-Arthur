@extends('layouts.app')

@section('title', 'Promote Class')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('enrolments.index') }}" class="hover:text-blue-600">Enrolments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Promote Class</span>
        </div>
        <h1 class="page-title">Promote / Carry Forward</h1>
        <p class="page-subtitle">Move a class's active students to a new class and academic year</p>
    </div>
</div>

{{-- Step 1: Choose source --}}
<div class="card p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
        Step 1 — Select Source Class
    </h3>
    <form method="GET" action="{{ route('enrolments.promote') }}" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <label class="form-label text-xs">Academic Year (Source)</label>
            <select name="source_year_id" class="form-select" required>
                <option value="">— Select year —</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(request('source_year_id') == $year->id)>
                    {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[200px]">
            <label class="form-label text-xs">Class (Source)</label>
            <select name="source_class_id" class="form-select" required>
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(request('source_class_id') == $class->id)>
                    {{ $class->full_name }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Load Students</button>
    </form>
</div>

@if($sourceClass && $sourceYear)

<form method="POST" action="{{ route('enrolments.promote.store') }}">
@csrf

<input type="hidden" name="source_class_id" value="{{ $sourceClass->id }}">
<input type="hidden" name="source_year_id" value="{{ $sourceYear->id }}">

{{-- Student preview --}}
<div class="card overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                Step 2 — Students to Promote
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $previewEnrolments->count() }} active student(s) in
                <strong>{{ $sourceClass->full_name }}</strong> — <strong>{{ $sourceYear->name }}</strong>
            </p>
        </div>
        @if($previewEnrolments->isNotEmpty())
        <label class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" id="select-all" checked class="rounded"
                onchange="document.querySelectorAll('.promote-cb').forEach(cb => cb.checked = this.checked)">
            Select All
        </label>
        @endif
    </div>

    @if($previewEnrolments->isEmpty())
    <div class="p-10 text-center text-gray-400 text-sm">
        No active enrolments found in {{ $sourceClass->full_name }} for {{ $sourceYear->name }}.
    </div>
    @else
    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[400px] overflow-y-auto">
        @foreach($previewEnrolments as $enrolment)
        <label class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
            <input type="checkbox" name="student_ids[]" value="{{ $enrolment->student_id }}"
                class="promote-cb rounded flex-shrink-0" checked>
            <img src="{{ $enrolment->student->photo_url }}"
                class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ $enrolment->student->full_name }}
                </p>
                @if($enrolment->student->student_number)
                <p class="text-xs text-gray-400 font-mono">{{ $enrolment->student->student_number }}</p>
                @endif
            </div>
            @if($enrolment->roll_number)
            <span class="text-xs text-gray-400 font-mono flex-shrink-0">Roll: {{ $enrolment->roll_number }}</span>
            @endif
        </label>
        @endforeach
    </div>
    @endif
</div>

@if($previewEnrolments->isNotEmpty())
{{-- Step 3: Target destination --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Step 3 — Select Target</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Target Academic Year <span class="text-red-500">*</span></label>
            <select name="target_year_id" class="form-select @error('target_year_id') border-red-500 @enderror" required>
                <option value="">— Select year —</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(old('target_year_id') == $year->id)>
                    {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
            @error('target_year_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Target Class <span class="text-red-500">*</span></label>
            <select name="target_class_id" class="form-select @error('target_class_id') border-red-500 @enderror" required>
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('target_class_id') == $class->id)>
                    {{ $class->full_name }}
                </option>
                @endforeach
            </select>
            @error('target_class_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">New Enrolled Date <span class="text-red-500">*</span></label>
            <input type="date" name="enrolled_date"
                value="{{ old('enrolled_date', now()->toDateString()) }}"
                class="form-input @error('enrolled_date') border-red-500 @enderror" required>
            @error('enrolled_date')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="text-xs text-gray-400 mt-3">
        The source enrolments will be marked as <em>promoted</em>. New active enrolments will be created in the target class. Students already enrolled in the target class will be skipped.
    </p>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary"
        onclick="if(!document.querySelector('.promote-cb:checked')) { alert('Select at least one student.'); return false; }
                 return confirm('Promote selected students to the new class? This cannot be undone.')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        Promote Students
    </button>
    <a href="{{ route('enrolments.index') }}" class="btn-secondary">Cancel</a>
</div>
@endif

</form>
@endif
@endsection

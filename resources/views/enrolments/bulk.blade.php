@extends('layouts.app')

@section('title', 'Bulk Enrol Students')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('enrolments.index') }}" class="hover:text-blue-600">Enrolments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Bulk Enrol</span>
        </div>
        <h1 class="page-title">Bulk Enrol Students</h1>
        <p class="page-subtitle">Select a class and academic year to enrol multiple students at once</p>
    </div>
</div>

{{-- Step 1: Pick year + class --}}
<div class="card p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
        Step 1 — Select Destination
    </h3>
    <form method="GET" action="{{ route('enrolments.bulk') }}" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <label class="form-label text-xs">Academic Year <span class="text-red-500">*</span></label>
            <select name="year_id" class="form-select" required>
                <option value="">— Select year —</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}"
                    @selected(request('year_id', $currentYear?->id) == $year->id)>
                    {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[200px]">
            <label class="form-label text-xs">Class <span class="text-red-500">*</span></label>
            <select name="class_id" class="form-select" required>
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                    {{ $class->full_name }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Load Students</button>
    </form>
</div>

@if($selectedClass && $selectedYear)

{{-- Step 2: Student checklist --}}
<form method="POST" action="{{ route('enrolments.bulk.store') }}" id="bulk-form">
@csrf

<input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
<input type="hidden" name="academic_year_id" value="{{ $selectedYear->id }}">

<div class="card overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                Step 2 — Select Students
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $unenrolledStudents->count() }} active student(s) not yet enrolled in
                <strong>{{ $selectedClass->full_name }}</strong> for <strong>{{ $selectedYear->name }}</strong>
            </p>
        </div>
        @if($unenrolledStudents->isNotEmpty())
        <div class="flex items-center gap-3" x-data="{ allChecked: true }">
            <label class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" id="select-all" checked class="rounded"
                    @change="allChecked = $event.target.checked; document.querySelectorAll('.student-cb').forEach(cb => cb.checked = allChecked)">
                Select All
            </label>
        </div>
        @endif
    </div>

    @if($unenrolledStudents->isEmpty())
    <div class="p-10 text-center text-gray-400 text-sm">
        All active students are already enrolled in {{ $selectedClass->full_name }} for {{ $selectedYear->name }}.
    </div>
    @else
    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[480px] overflow-y-auto">
        @foreach($unenrolledStudents as $student)
        <label class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                class="student-cb rounded flex-shrink-0" checked>
            <img src="{{ $student->photo_url }}"
                class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ $student->full_name }}
                </p>
                @if($student->student_number)
                <p class="text-xs text-gray-400 font-mono">{{ $student->student_number }}</p>
                @endif
            </div>
        </label>
        @endforeach
    </div>
    @endif
</div>

@if($unenrolledStudents->isNotEmpty())
{{-- Step 3: Options --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Step 3 — Enrolment Options</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Term <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
            <select name="term_id" class="form-select">
                <option value="">— Full Year —</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>
                    {{ $term->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Enrolled Date <span class="text-red-500">*</span></label>
            <input type="date" name="enrolled_date"
                value="{{ old('enrolled_date', now()->toDateString()) }}"
                class="form-input" required>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary"
        onclick="if(!document.querySelector('.student-cb:checked')) { alert('Select at least one student.'); return false; }">
        Enrol Selected Students
    </button>
    <a href="{{ route('enrolments.index') }}" class="btn-secondary">Cancel</a>
</div>
@endif

</form>
@endif
@endsection

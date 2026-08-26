@extends('layouts.app')

@section('title', 'Enrol Student')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('enrolments.index') }}" class="hover:text-blue-600">Enrolments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Enrol Student</span>
        </div>
        <h1 class="page-title">Enrol Student</h1>
    </div>
</div>

<form method="POST" action="{{ route('enrolments.store') }}" class="max-w-2xl space-y-6">
@csrf

<div class="card p-6 space-y-5">

    {{-- Student --}}
    @if($preStudent)
    <input type="hidden" name="student_id" value="{{ $preStudent->id }}">
    <div>
        <label class="form-label">Student</label>
        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <img src="{{ $preStudent->photo_url }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" alt="">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ $preStudent->full_name }}</p>
                <p class="text-xs text-gray-400">{{ $preStudent->student_number }}</p>
            </div>
            <a href="{{ route('enrolments.create') }}" class="ml-auto text-xs text-blue-600 hover:underline">Change</a>
        </div>
    </div>
    @else
    <div>
        <label class="form-label">Student <span class="text-red-500">*</span></label>
        <select name="student_id" class="form-select @error('student_id') border-red-500 @enderror" required>
            <option value="">— Select student —</option>
            @foreach(\App\Models\Student::where('school_id', auth()->user()->school_id)
                ->where('status', 'active')
                ->orderBy('last_name')->orderBy('first_name')->get() as $student)
            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                {{ $student->full_name }}
                @if($student->student_number) ({{ $student->student_number }}) @endif
            </option>
            @endforeach
        </select>
        @error('student_id')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    @endif

    {{-- Academic Year --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Academic Year <span class="text-red-500">*</span></label>
            <select name="academic_year_id" id="year-select"
                class="form-select @error('academic_year_id') border-red-500 @enderror" required>
                <option value="">— Select year —</option>
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
            <label class="form-label">Term <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
            <select name="term_id" class="form-select @error('term_id') border-red-500 @enderror">
                <option value="">— None / Full Year —</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                    {{ $term->name }}
                </option>
                @endforeach
            </select>
            @error('term_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Class --}}
    <div>
        <label class="form-label">Class <span class="text-red-500">*</span></label>
        <select name="class_id" class="form-select @error('class_id') border-red-500 @enderror" required>
            <option value="">— Select class —</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}"
                @selected(old('class_id', $preClass?->id) == $class->id)>
                {{ $class->full_name }}
                @if($class->programme) — {{ $class->programme }} @endif
            </option>
            @endforeach
        </select>
        @error('class_id')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    {{-- Roll number + date + status --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Roll Number <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
            <input type="text" name="roll_number" value="{{ old('roll_number') }}"
                class="form-input @error('roll_number') border-red-500 @enderror"
                placeholder="e.g. 01">
            @error('roll_number')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Enrolled Date <span class="text-red-500">*</span></label>
            <input type="date" name="enrolled_date"
                value="{{ old('enrolled_date', now()->toDateString()) }}"
                class="form-input @error('enrolled_date') border-red-500 @enderror" required>
            @error('enrolled_date')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Status <span class="text-red-500">*</span></label>
            <select name="status" class="form-select @error('status') border-red-500 @enderror" required>
                @foreach(\App\Models\Enrolment::STATUSES as $key => $info)
                <option value="{{ $key }}" @selected(old('status', 'active') === $key)>
                    {{ $info['label'] }}
                </option>
                @endforeach
            </select>
            @error('status')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary">Enrol Student</button>
    <a href="{{ route('enrolments.index') }}" class="btn-secondary">Cancel</a>
</div>

</form>
@endsection

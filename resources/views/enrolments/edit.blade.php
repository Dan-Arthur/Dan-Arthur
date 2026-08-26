@extends('layouts.app')

@section('title', 'Edit Enrolment — ' . $enrolment->student->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('enrolments.index') }}" class="hover:text-blue-600">Enrolments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('enrolments.show', $enrolment) }}" class="hover:text-blue-600">
                {{ $enrolment->student->full_name }}
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit Enrolment</h1>
    </div>
</div>

<div class="max-w-2xl space-y-6">

    {{-- Read-only identity block --}}
    <div class="card p-5">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Enrolment Identity</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs">Student</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">
                    {{ $enrolment->student->full_name }}
                </p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Class</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">
                    {{ $enrolment->schoolClass->full_name ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Academic Year</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">
                    {{ $enrolment->academicYear->name ?? '—' }}
                </p>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Student, class, and academic year cannot be changed after enrolment.</p>
    </div>

    <form method="POST" action="{{ route('enrolments.update', $enrolment) }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="card p-6 space-y-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
            Enrolment Details
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Term --}}
            <div>
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="">— Full Year / None —</option>
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}"
                        @selected(old('term_id', $enrolment->term_id) == $term->id)>
                        {{ $term->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Roll number --}}
            <div>
                <label class="form-label">Roll Number</label>
                <input type="text" name="roll_number"
                    value="{{ old('roll_number', $enrolment->roll_number) }}"
                    class="form-input @error('roll_number') border-red-500 @enderror"
                    placeholder="Optional">
                @error('roll_number')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Enrolled date --}}
            <div>
                <label class="form-label">Enrolled Date <span class="text-red-500">*</span></label>
                <input type="date" name="enrolled_date"
                    value="{{ old('enrolled_date', $enrolment->enrolled_date?->toDateString()) }}"
                    class="form-input @error('enrolled_date') border-red-500 @enderror" required>
                @error('enrolled_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-select @error('status') border-red-500 @enderror" required>
                    @foreach(\App\Models\Enrolment::STATUSES as $key => $info)
                    <option value="{{ $key }}" @selected(old('status', $enrolment->status) === $key)>
                        {{ $info['label'] }}
                    </option>
                    @endforeach
                </select>
                @error('status')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Exit date --}}
            <div>
                <label class="form-label">Exit Date</label>
                <input type="date" name="exit_date"
                    value="{{ old('exit_date', $enrolment->exit_date?->toDateString()) }}"
                    class="form-input @error('exit_date') border-red-500 @enderror">
                @error('exit_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Promoted --}}
            <div class="flex items-start gap-2 pt-6">
                <input type="hidden" name="is_promoted" value="0">
                <input type="checkbox" name="is_promoted" id="is_promoted" value="1"
                    @checked(old('is_promoted', $enrolment->is_promoted))
                    class="mt-0.5 rounded">
                <label for="is_promoted" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    Promoted to next class
                </label>
            </div>
        </div>

        {{-- Exit reason --}}
        <div>
            <label class="form-label">Exit Reason</label>
            <textarea name="exit_reason" rows="2"
                class="form-input resize-none @error('exit_reason') border-red-500 @enderror"
                placeholder="Reason for withdrawal or transfer (if applicable)…">{{ old('exit_reason', $enrolment->exit_reason) }}</textarea>
            @error('exit_reason')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary">Save Changes</button>
        <a href="{{ route('enrolments.show', $enrolment) }}" class="btn-secondary">Cancel</a>
    </div>

    </form>
</div>
@endsection

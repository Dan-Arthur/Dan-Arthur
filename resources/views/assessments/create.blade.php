@extends('layouts.app')

@section('title', 'New Assessment')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('assessments.index') }}" class="hover:text-blue-600">Assessments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>New</span>
        </div>
        <h1 class="page-title">New Assessment</h1>
    </div>
</div>

<form method="POST" action="{{ route('assessments.store') }}" class="max-w-3xl space-y-6"
    x-data="{ yearId: '{{ $currentYear?->id }}' }">
@csrf

<div class="card p-6 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}"
                class="form-input @error('title') border-red-500 @enderror"
                placeholder="e.g. Term 1 Mathematics Exam" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Class <span class="text-red-500">*</span></label>
            <select name="class_id" class="form-select @error('class_id') border-red-500 @enderror" required>
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </select>
            @error('class_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Subject <span class="text-red-500">*</span></label>
            <select name="subject_id" class="form-select @error('subject_id') border-red-500 @enderror" required>
                <option value="">— Select subject —</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            @error('subject_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Academic Year <span class="text-red-500">*</span></label>
            <select name="academic_year_id" class="form-select" required x-model="yearId">
                @foreach($years as $year)
                <option value="{{ $year->id }}" @selected(old('academic_year_id', $currentYear?->id) == $year->id)>
                    {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Term <span class="text-red-500">*</span></label>
            <select name="term_id" class="form-select @error('term_id') border-red-500 @enderror" required>
                <option value="">— Select term —</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
            @error('term_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Type <span class="text-red-500">*</span></label>
            <select name="type" class="form-select" required>
                @foreach(\App\Models\Assessment::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Teacher <span class="text-red-500">*</span></label>
            <select name="teacher_id" class="form-select">
                <option value="">— Assign to self —</option>
                @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Max Score <span class="text-red-500">*</span></label>
            <input type="number" name="max_score" value="{{ old('max_score', 100) }}"
                step="0.5" min="1" max="1000" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Weight (%)</label>
            <input type="number" name="weight" value="{{ old('weight') }}"
                step="0.5" min="0" max="100" class="form-input" placeholder="e.g. 30">
            <p class="text-xs text-gray-400 mt-1">Percentage weight in final term score.</p>
        </div>

        <div>
            <label class="form-label">Assessment Date</label>
            <input type="date" name="assessment_date" value="{{ old('assessment_date') }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Submission Deadline</label>
            <input type="date" name="submission_deadline" value="{{ old('submission_deadline') }}" class="form-input">
        </div>

        <div class="sm:col-span-2">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-input" placeholder="Optional notes or instructions…">{{ old('description') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary">Create Assessment</button>
    <a href="{{ route('assessments.index') }}" class="btn-secondary">Cancel</a>
</div>
</form>
@endsection

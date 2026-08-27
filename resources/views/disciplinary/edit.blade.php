@extends('layouts.app')
@section('title', 'Edit Record')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Disciplinary Record</h1>
        <p class="page-subtitle">{{ $disciplinary->student->full_name }}</p>
    </div>
    <a href="{{ route('disciplinary.show', $disciplinary) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('disciplinary.update', $disciplinary) }}" class="max-w-2xl space-y-5">
    @csrf @method('PUT')

    {{-- Student (read-only on edit) --}}
    <div class="card">
        <p class="text-xs text-gray-500 mb-1">Student</p>
        <p class="font-semibold text-gray-900 dark:text-white">{{ $disciplinary->student->full_name }}</p>
        <p class="text-xs text-gray-400">{{ $disciplinary->student->student_number ?? $disciplinary->student->admission_number }}</p>
    </div>

    <div class="card space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Incident Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select" required>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}"
                            {{ old('academic_year_id', $disciplinary->academic_year_id) == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="">— All Terms —</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}"
                            {{ old('term_id', $disciplinary->term_id) == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Category <span class="required">*</span></label>
                <select name="category" class="form-select" required>
                    @foreach (\App\Models\DisciplinaryRecord::CATEGORIES as $key => $label)
                        <option value="{{ $key }}"
                            {{ old('category', $disciplinary->category) === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Severity <span class="required">*</span></label>
                <select name="severity" class="form-select" required>
                    @foreach (\App\Models\DisciplinaryRecord::SEVERITIES as $key => $s)
                        <option value="{{ $key }}"
                            {{ old('severity', $disciplinary->severity) === $key ? 'selected' : '' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('severity')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Incident Date <span class="required">*</span></label>
                <input type="date" name="incident_date"
                    value="{{ old('incident_date', $disciplinary->incident_date->toDateString()) }}"
                    class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location"
                    value="{{ old('location', $disciplinary->location) }}"
                    class="form-input" placeholder="e.g. Classroom 4B">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description <span class="required">*</span></label>
            <textarea name="description" rows="4" class="form-textarea" required>{{ old('description', $disciplinary->description) }}</textarea>
            @error('description')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="card space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Action & Follow-up</h2>

        <div class="form-group">
            <label class="form-label">Action Taken</label>
            <textarea name="action_taken" rows="3" class="form-textarea">{{ old('action_taken', $disciplinary->action_taken) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Follow-up Date</label>
                <input type="date" name="follow_up_date"
                    value="{{ old('follow_up_date', $disciplinary->follow_up_date?->toDateString()) }}"
                    class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach (\App\Models\DisciplinaryRecord::STATUSES as $key => $s)
                        <option value="{{ $key }}"
                            {{ old('status', $disciplinary->status) === $key ? 'selected' : '' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Follow-up Notes</label>
            <textarea name="follow_up_notes" rows="2" class="form-textarea">{{ old('follow_up_notes', $disciplinary->follow_up_notes) }}</textarea>
        </div>

        <div class="form-group">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="parent_notified" value="1" class="rounded"
                    {{ old('parent_notified', $disciplinary->parent_notified) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Parent / Guardian notified</span>
            </label>
            @if ($disciplinary->parent_notified && $disciplinary->parent_notified_at)
                <p class="text-xs text-gray-400 ml-6">First notified {{ $disciplinary->parent_notified_at->format('d M Y H:i') }}</p>
            @endif
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('disciplinary.show', $disciplinary) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

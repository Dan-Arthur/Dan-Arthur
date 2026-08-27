@extends('layouts.app')

@section('title', 'Edit Exam')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Exam</h1>
        <p class="page-subtitle">{{ $exam->title }}</p>
    </div>
    <a href="{{ route('exams.index') }}" class="btn btn-ghost">Back</a>
</div>

<div class="card max-w-2xl"
     x-data="{
         yearId: '{{ $exam->academic_year_id }}',
         terms: @json($terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name])),
         async loadTerms() {
             if (!this.yearId) { this.terms = []; return; }
             const res = await fetch(`/exams/terms?year_id=${this.yearId}`);
             this.terms = await res.json();
         }
     }">
    <form method="POST" action="{{ route('exams.update', $exam) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select" required
                        x-model="yearId" @change="loadTerms()">
                    @foreach ($years as $yr)
                        <option value="{{ $yr->id }}" {{ $yr->id == $exam->academic_year_id ? 'selected' : '' }}>{{ $yr->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="">— All Terms —</option>
                    <template x-for="t in terms" :key="t.id">
                        <option :value="t.id" :selected="t.id == {{ $exam->term_id ?? 'null' }}" x-text="t.name"></option>
                    </template>
                </select>
                @error('term_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-full form-group">
                <label class="form-label">Exam Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $exam->title) }}" required>
                @error('title') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Class</label>
                <select name="school_class_id" class="form-select">
                    <option value="">— All Classes —</option>
                    @foreach ($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('school_class_id', $exam->school_class_id) == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                    @endforeach
                </select>
                @error('school_class_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">— All Subjects —</option>
                    @foreach ($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ old('subject_id', $exam->subject_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
                @error('subject_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Exam Date <span class="required">*</span></label>
                <input type="date" name="exam_date" class="form-input"
                       value="{{ old('exam_date', $exam->exam_date->format('Y-m-d')) }}" required>
                @error('exam_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-input"
                       value="{{ old('start_time', $exam->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $exam->start_time)->format('H:i') : '') }}">
                @error('start_time') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Duration (minutes)</label>
                <input type="number" name="duration_minutes" class="form-input"
                       value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1" max="480">
                @error('duration_minutes') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach (\App\Models\Exam::STATUSES as $key => $s)
                        <option value="{{ $key }}" {{ old('status', $exam->status) === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
                    @endforeach
                </select>
                @error('status') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Venue</label>
                <input type="text" name="venue" class="form-input"
                       value="{{ old('venue', $exam->venue) }}" placeholder="e.g. Main Hall">
                @error('venue') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Invigilator</label>
                <input type="text" name="invigilator" class="form-input"
                       value="{{ old('invigilator', $exam->invigilator) }}">
                @error('invigilator') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-full form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="3">{{ old('notes', $exam->notes) }}</textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('exams.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'New Disciplinary Record')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Disciplinary Record</h1>
        <p class="page-subtitle">Log a conduct incident</p>
    </div>
    <a href="{{ route('disciplinary.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('disciplinary.store') }}"
      x-data="disciplinaryForm()"
      class="max-w-2xl space-y-5">
    @csrf

    {{-- Student --}}
    <div class="card space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Student</h2>

        <div class="form-group relative">
            <label class="form-label">Student <span class="required">*</span></label>
            <input type="text" class="form-input" placeholder="Type name or student number…"
                   x-model="query" @input.debounce.350ms="search()" autocomplete="off"
                   value="{{ $student ? $student->full_name . ' (' . ($student->student_number ?: $student->admission_number) . ')' : '' }}">
            <input type="hidden" name="student_id" x-model="studentId"
                   value="{{ $student?->id }}">
            <div x-show="results.length" x-cloak
                 class="absolute z-20 w-full bg-surface border border-border rounded-lg shadow-lg mt-1 py-1 max-h-48 overflow-y-auto">
                <template x-for="s in results" :key="s.id">
                    <button type="button" class="w-full text-left px-3 py-2 hover:bg-surface-hover text-sm"
                            @click="pick(s)" x-text="s.label"></button>
                </template>
            </div>
            @error('student_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select"
                        @change="loadTerms($event.target.value)" required>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}"
                            {{ old('academic_year_id', $currentYear?->id) == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Term</label>
                <select name="term_id" class="form-select">
                    <option value="">— All Terms —</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Incident details --}}
    <div class="card space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Incident Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Category <span class="required">*</span></label>
                <select name="category" class="form-select" required>
                    <option value="">— Select —</option>
                    @foreach (\App\Models\DisciplinaryRecord::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Severity <span class="required">*</span></label>
                <select name="severity" class="form-select" required>
                    @foreach (\App\Models\DisciplinaryRecord::SEVERITIES as $key => $s)
                        <option value="{{ $key }}" {{ old('severity', 'minor') === $key ? 'selected' : '' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('severity')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Incident Date <span class="required">*</span></label>
                <input type="date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}"
                    class="form-input" required>
                @error('incident_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                    class="form-input" placeholder="e.g. Classroom 4B, School Compound">
                @error('location')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description <span class="required">*</span></label>
            <textarea name="description" rows="4" class="form-textarea"
                placeholder="Describe what happened in detail…" required>{{ old('description') }}</textarea>
            @error('description')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Action & Follow-up --}}
    <div class="card space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white">Action Taken & Follow-up</h2>

        <div class="form-group">
            <label class="form-label">Action Taken</label>
            <textarea name="action_taken" rows="3" class="form-textarea"
                placeholder="Describe the disciplinary action…">{{ old('action_taken') }}</textarea>
            @error('action_taken')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Follow-up Date</label>
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="form-input">
                @error('follow_up_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach (\App\Models\DisciplinaryRecord::STATUSES as $key => $s)
                        <option value="{{ $key }}" {{ old('status', 'open') === $key ? 'selected' : '' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('status')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Follow-up Notes</label>
            <textarea name="follow_up_notes" rows="2" class="form-textarea"
                placeholder="Any follow-up observations…">{{ old('follow_up_notes') }}</textarea>
        </div>

        <div class="form-group">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="parent_notified" value="1" class="rounded"
                    {{ old('parent_notified') ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Parent / Guardian notified
                </span>
            </label>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Record</button>
        <a href="{{ route('disciplinary.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
function disciplinaryForm() {
    return {
        query: '',
        results: [],
        studentId: '{{ $student?->id ?? '' }}',

        async search() {
            if (this.query.length < 2) { this.results = []; return; }
            const r = await fetch('/disciplinary/search-students?q=' + encodeURIComponent(this.query));
            this.results = await r.json();
        },

        pick(s) {
            this.studentId = s.id;
            this.query = s.label;
            this.results = [];
        },

        async loadTerms(yearId) {
            if (!yearId) return;
            const r = await fetch('/disciplinary/terms?year_id=' + yearId);
            const terms = await r.json();
            const sel = document.querySelector('select[name="term_id"]');
            sel.innerHTML = '<option value="">— All Terms —</option>';
            terms.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                sel.appendChild(opt);
            });
        },
    };
}
</script>
@endpush
@endsection

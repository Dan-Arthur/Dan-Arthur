@extends('layouts.app')
@section('title', 'Assign Students — ' . $scholarship->name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $scholarship->name }}</h1>
        <p class="page-subtitle">
            <span class="badge badge-gray text-xs">{{ $scholarship->type_label }}</span>
            <span class="font-semibold ml-1">{{ $scholarship->value_display }}</span>
            — assign students for a specific academic year
        </p>
    </div>
    <a href="{{ route('scholarships.index') }}" class="btn btn-ghost">Back</a>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

{{-- Year filter --}}
<form method="GET" class="flex items-center gap-3 mb-6">
    <label class="form-label mb-0 whitespace-nowrap">Academic Year:</label>
    <select name="year_id" class="form-select w-48" onchange="this.form.submit()">
        @foreach ($years as $year)
            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                {{ $year->name }}
            </option>
        @endforeach
    </select>
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Assign form --}}
    <div class="card space-y-4"
         x-data="{
             query: '',
             results: [],
             studentId: '',
             studentLabel: '',
             async search(q) {
                 if (q.length < 2) { this.results = []; return; }
                 const r = await fetch('/scholarships/search-students?q=' + encodeURIComponent(q));
                 this.results = await r.json();
             },
             pick(item) {
                 this.studentId    = item.id;
                 this.studentLabel = item.label;
                 this.query        = item.label;
                 this.results      = [];
             }
         }">
        <h2 class="font-semibold text-gray-900 dark:text-white">Assign a Student</h2>

        <form method="POST" action="{{ route('scholarships.assign', $scholarship) }}">
            @csrf

            <div class="form-group mb-3">
                <label class="form-label">Academic Year <span class="required">*</span></label>
                <select name="academic_year_id" class="form-select" required>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3 relative">
                <label class="form-label">Student <span class="required">*</span></label>
                <input type="text" class="form-input" placeholder="Type name or admission no…"
                       x-model="query" @input.debounce.350ms="search($event.target.value)"
                       autocomplete="off">
                <input type="hidden" name="student_id" x-model="studentId" required>
                <div x-show="results.length" x-cloak class="absolute z-20 w-full bg-surface border border-border rounded-lg shadow-lg mt-1 py-1 max-h-48 overflow-y-auto">
                    <template x-for="item in results" :key="item.id">
                        <button type="button" class="w-full text-left px-3 py-2 hover:bg-surface-hover text-sm"
                                @click="pick(item)" x-text="item.label"></button>
                    </template>
                </div>
                @error('student_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-textarea" placeholder="Optional…">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full">Assign Scholarship</button>
        </form>
    </div>

    {{-- Assignments list --}}
    <div class="lg:col-span-2">
        @if ($assignments->isEmpty())
            <div class="card p-8 text-center text-gray-400 text-sm">
                No students assigned for this year yet.
            </div>
        @else
        <div class="card overflow-hidden">
            <table class="w-full text-sm data-table">
                <thead>
                    <tr>
                        <th class="text-left">Student</th>
                        <th class="text-left">Year</th>
                        <th class="text-left">Assigned By</th>
                        <th class="text-left">Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignments as $assign)
                    <tr>
                        <td>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $assign->student->full_name ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $assign->student->admission_number ?? '' }}
                            </p>
                        </td>
                        <td class="text-gray-600 dark:text-gray-300">{{ $assign->academicYear->name ?? '—' }}</td>
                        <td class="text-gray-600 dark:text-gray-300">{{ $assign->assignedBy->name ?? '—' }}</td>
                        <td class="text-gray-500 text-xs max-w-xs truncate">{{ $assign->notes ?? '—' }}</td>
                        <td class="text-right">
                            <form method="POST"
                                  action="{{ route('scholarships.revoke', [$scholarship, $assign]) }}"
                                  onsubmit="return confirm('Remove this scholarship assignment?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-ghost text-red-600">Revoke</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

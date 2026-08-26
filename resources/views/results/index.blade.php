@extends('layouts.app')

@section('title', 'Results')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Results</h1>
        <p class="page-subtitle">Term result sheets and report cards</p>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('results.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Academic Year</label>
            <select name="year_id" class="form-select text-sm" onchange="this.form.submit()">
                @foreach($years as $year)
                <option value="{{ $year->id }}" @selected($selectedYearId == $year->id)>
                    {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        @if($terms->isNotEmpty())
        <div>
            <label class="form-label text-xs">Term</label>
            <select name="term_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">— Select term —</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}" @selected($selectedTermId == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label text-xs">Class</label>
            <select name="class_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(\App\Models\Result::STATUSES as $key => $info)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">View</button>
    </form>
</div>

{{-- Generate + bulk actions --}}
@if($selectedClassId && $selectedTermId)
<div class="flex flex-wrap gap-3 mb-4">
    @can('enter results')
    <form method="POST" action="{{ route('results.generate') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
        <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
        <button type="submit" class="btn-secondary"
            onclick="return confirm('Generate/recalculate results from marks?')">
            Generate Results from Marks
        </button>
    </form>
    @endcan

    @can('approve results')
    <form method="POST" action="{{ route('results.bulk-approve') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
        <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
        <button type="submit" class="btn-secondary"
            onclick="return confirm('Approve all draft results?')">
            Approve All Draft Results
        </button>
    </form>
    @endcan

    @can('publish results')
    <form method="POST" action="{{ route('results.bulk-publish') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
        <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
        <button type="submit" class="btn-primary"
            onclick="return confirm('Publish all approved/draft results?')">
            Publish All
        </button>
    </form>
    @endcan
</div>
@endif

{{-- Results table --}}
@if($results->isNotEmpty())
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            {{ $results->count() }} Result(s)
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">Pos</th>
                    <th>Student</th>
                    <th class="text-center">Subjects</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Average</th>
                    <th class="text-center">Grade</th>
                    <th>Status</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                <tr>
                    <td class="text-center font-medium text-gray-500">{{ $result->position ?? '—' }}</td>
                    <td>
                        <a href="{{ route('results.show', $result) }}"
                            class="font-medium text-gray-900 dark:text-white hover:text-blue-600 text-sm">
                            {{ $result->student->full_name ?? '—' }}
                        </a>
                    </td>
                    <td class="text-center text-gray-500 text-sm">{{ $result->subjects_offered ?? '—' }}</td>
                    <td class="text-center font-medium text-gray-900 dark:text-white">{{ $result->total_score ?? '—' }}</td>
                    <td class="text-center font-medium {{ ($result->average_score ?? 0) >= 50 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $result->average_score ? number_format($result->average_score, 1) . '%' : '—' }}
                    </td>
                    <td class="text-center font-bold text-gray-900 dark:text-white">{{ $result->overall_grade ?? '—' }}</td>
                    <td><span class="badge {{ $result->status_color }}">{{ $result->status_label }}</span></td>
                    <td>
                        <div class="flex gap-1 justify-end">
                            <a href="{{ route('results.show', $result) }}" class="icon-btn" title="View Report Card">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($selectedClassId && $selectedTermId)
<div class="card p-10 text-center text-gray-400 text-sm">
    No results found. Generate them from the assessment marks using the button above.
</div>
@else
<div class="card p-10 text-center text-gray-400 text-sm">
    Select a class and term to view results.
</div>
@endif
@endsection

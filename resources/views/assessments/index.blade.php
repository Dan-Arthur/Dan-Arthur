@extends('layouts.app')

@section('title', 'Assessments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Assessments</h1>
        <p class="page-subtitle">Manage tests, exams and assignments</p>
    </div>
    @can('create assessments')
    <a href="{{ route('assessments.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Assessment
    </a>
    @endcan
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('assessments.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Year</label>
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
            <select name="term_id" class="form-select text-sm">
                <option value="">All Terms</option>
                @foreach($terms as $term)
                <option value="{{ $term->id }}" @selected(request('term_id') == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label text-xs">Class</label>
            <select name="class_id" class="form-select text-sm">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Type</label>
            <select name="type" class="form-select text-sm">
                <option value="">All Types</option>
                @foreach(\App\Models\Assessment::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm">
                <option value="">All</option>
                @foreach(\App\Models\Assessment::STATUSES as $key => $info)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[160px]">
            <label class="form-label text-xs">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input text-sm" placeholder="Title…">
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Filter</button>
        @if(request()->hasAny(['class_id','term_id','type','status','search']))
        <a href="{{ route('assessments.index', ['year_id' => $selectedYearId]) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="card overflow-hidden">
    @if($assessments->isEmpty())
    <div class="p-12 text-center">
        <p class="text-gray-400 text-sm mb-3">No assessments found.</p>
        @can('create assessments')
        <a href="{{ route('assessments.create') }}" class="btn-primary">Create First Assessment</a>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Class / Subject</th>
                    <th>Type</th>
                    <th>Term</th>
                    <th>Date</th>
                    <th>Max</th>
                    <th>Status</th>
                    <th>Marks</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $assessment)
                <tr>
                    <td>
                        <a href="{{ route('assessments.show', $assessment) }}"
                            class="font-medium text-gray-900 dark:text-white hover:text-blue-600 text-sm">
                            {{ $assessment->title }}
                        </a>
                    </td>
                    <td class="text-sm text-gray-500">
                        <div>{{ $assessment->schoolClass->full_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400">{{ $assessment->subject->name ?? '—' }}</div>
                    </td>
                    <td class="text-sm text-gray-500">{{ $assessment->type_label }}</td>
                    <td class="text-sm text-gray-500">{{ $assessment->term->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $assessment->assessment_date?->format('d M Y') ?? '—' }}</td>
                    <td class="text-sm text-gray-500 text-center">{{ $assessment->max_score }}</td>
                    <td><span class="badge {{ $assessment->status_color }}">{{ $assessment->status_label }}</span></td>
                    <td class="text-sm text-center {{ $assessment->marks_entered ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $assessment->marks_count }}
                        @if($assessment->marks_entered)
                        <svg class="w-3.5 h-3.5 inline-block text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('assessments.show', $assessment) }}" class="icon-btn" title="View">
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
    @if($assessments->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $assessments->links() }}</div>
    @endif
    @endif
</div>
@endsection

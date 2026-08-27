@extends('layouts.app')

@section('title', 'Exam Schedule')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Exam Schedule</h1>
        @if ($currentYear)
            <p class="page-subtitle">{{ $currentYear->name }}</p>
        @endif
    </div>
    @can('manage exams')
    <a href="{{ route('exams.create') }}" class="btn btn-primary">Schedule Exam</a>
    @endcan
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar mb-6" x-data="{ yearId: '{{ request('year_id', $yearId) }}' }">
    <select name="year_id" class="form-select w-44" x-model="yearId" @change="$el.form.submit()">
        @foreach ($years as $yr)
            <option value="{{ $yr->id }}" {{ $yr->id == $yearId ? 'selected' : '' }}>{{ $yr->name }}</option>
        @endforeach
    </select>

    <select name="term_id" class="form-select w-36">
        <option value="">All Terms</option>
        @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
        @endforeach
    </select>

    <select name="class_id" class="form-select w-36">
        <option value="">All Classes</option>
        @foreach ($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
        @endforeach
    </select>

    <select name="status" class="form-select w-36">
        <option value="">All Statuses</option>
        @foreach (\App\Models\Exam::STATUSES as $key => $s)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('exams.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($exams->isEmpty())
    <div class="empty-state">No exams scheduled for the selected filters.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($exams as $exam)
                <tr>
                    <td>
                        <p class="font-medium">{{ $exam->title }}</p>
                        @if ($exam->term)
                            <p class="text-xs text-muted">{{ $exam->term->name }}</p>
                        @endif
                    </td>
                    <td>{{ $exam->schoolClass?->name ?? '—' }}</td>
                    <td>{{ $exam->subject?->name ?? 'All Subjects' }}</td>
                    <td class="whitespace-nowrap font-medium">{{ $exam->exam_date->format('d M Y') }}</td>
                    <td class="whitespace-nowrap font-mono text-sm">
                        @if ($exam->start_time)
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $exam->start_time)->format('H:i') }}
                            @if ($exam->end_time) – {{ $exam->end_time }} @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-sm">{{ $exam->duration_minutes ? $exam->duration_minutes . ' min' : '—' }}</td>
                    <td class="text-sm">{{ $exam->venue ?? '—' }}</td>
                    <td><span class="{{ $exam->status_color }}">{{ $exam->status_label }}</span></td>
                    <td class="table-actions">
                        @can('manage exams')
                        <a href="{{ route('exams.edit', $exam) }}" class="action-link">Edit</a>
                        <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-link text-red-500">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $exams->links() }}</div>
@endif
@endsection

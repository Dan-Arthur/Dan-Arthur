@extends('layouts.app')
@section('title', 'Disciplinary Records')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Disciplinary Records</h1>
        <p class="page-subtitle">Track and manage student conduct incidents</p>
    </div>
    @can('create disciplinary records')
    <a href="{{ route('disciplinary.create') }}" class="btn btn-primary">+ New Record</a>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<form method="GET" class="card p-4 mb-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <div>
        <label class="form-label text-xs">Year</label>
        <select name="year_id" class="form-select text-sm" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year->id }}" {{ request('year_id', $currentYear?->id) == $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label text-xs">Severity</label>
        <select name="severity" class="form-select text-sm">
            <option value="">All</option>
            @foreach (\App\Models\DisciplinaryRecord::SEVERITIES as $key => $s)
                <option value="{{ $key }}" {{ request('severity') === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label text-xs">Status</label>
        <select name="status" class="form-select text-sm">
            <option value="">All</option>
            @foreach (\App\Models\DisciplinaryRecord::STATUSES as $key => $s)
                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label text-xs">Category</label>
        <select name="category" class="form-select text-sm">
            <option value="">All</option>
            @foreach (\App\Models\DisciplinaryRecord::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="form-label text-xs">Student</label>
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input text-sm flex-1" placeholder="Name or ID…">
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            @if (request()->hasAny(['severity','status','category','search','year_id']))
                <a href="{{ route('disciplinary.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </div>
    </div>
</form>

@if ($records->isEmpty())
    <div class="card p-10 text-center text-gray-400 text-sm">No records found for the selected filters.</div>
@else
<div class="card overflow-hidden">
    <table class="w-full text-sm data-table">
        <thead>
            <tr>
                <th class="text-left">Student</th>
                <th class="text-left">Incident</th>
                <th class="text-left">Category</th>
                <th class="text-center">Severity</th>
                <th class="text-center">Status</th>
                <th class="text-center">Parent Notified</th>
                <th class="text-left">Reported By</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $record->student->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $record->student->student_number ?? $record->student->admission_number ?? '' }}</p>
                </td>
                <td>
                    <p class="font-medium">{{ $record->incident_date->format('d M Y') }}</p>
                    @if ($record->location)
                        <p class="text-xs text-gray-400">{{ $record->location }}</p>
                    @endif
                </td>
                <td class="text-gray-700 dark:text-gray-300">{{ $record->category_label }}</td>
                <td class="text-center">
                    <span class="badge {{ $record->severity_color }}">{{ $record->severity_label }}</span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $record->status_color }}">{{ $record->status_label }}</span>
                </td>
                <td class="text-center">
                    @if ($record->parent_notified)
                        <span class="badge badge-green text-xs">Yes</span>
                    @else
                        <span class="badge badge-gray text-xs">No</span>
                    @endif
                </td>
                <td class="text-gray-500 text-xs">{{ $record->reportedBy->name ?? '—' }}</td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('disciplinary.show', $record) }}" class="btn btn-xs btn-ghost">View</a>
                    @can('edit disciplinary records')
                    <a href="{{ route('disciplinary.edit', $record) }}" class="btn btn-xs btn-ghost">Edit</a>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $records->links() }}</div>
@endif
@endsection

@extends('layouts.app')
@section('title', 'Disciplinary History — ' . $student->full_name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $student->full_name }}</h1>
        <p class="page-subtitle">Full disciplinary history</p>
    </div>
    <div class="flex gap-2">
        @can('create disciplinary records')
        <a href="{{ route('disciplinary.create', ['student_id' => $student->id]) }}" class="btn btn-primary">+ New Record</a>
        @endcan
        <a href="{{ route('students.show', $student) }}" class="btn btn-ghost">Student Profile</a>
        <a href="{{ route('disciplinary.index') }}" class="btn btn-ghost">All Records</a>
    </div>
</div>

{{-- Summary strip --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @php
        $total    = $records->count();
        $open     = $records->where('status', 'open')->count();
        $major    = $records->where('severity', 'major')->count();
        $resolved = $records->where('status', 'resolved')->count();
    @endphp
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Incidents</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-red-600">{{ $open }}</p>
        <p class="text-xs text-gray-500 mt-1">Open</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-orange-600">{{ $major }}</p>
        <p class="text-xs text-gray-500 mt-1">Major</p>
    </div>
    <div class="card text-center py-4">
        <p class="text-2xl font-bold text-green-600">{{ $resolved }}</p>
        <p class="text-xs text-gray-500 mt-1">Resolved</p>
    </div>
</div>

@if ($records->isEmpty())
    <div class="card p-10 text-center text-gray-400 text-sm">No disciplinary records for this student.</div>
@else
<div class="card overflow-hidden">
    <table class="w-full text-sm data-table">
        <thead>
            <tr>
                <th class="text-left">Date</th>
                <th class="text-left">Category</th>
                <th class="text-center">Severity</th>
                <th class="text-left">Action Taken</th>
                <th class="text-center">Status</th>
                <th class="text-center">Parent</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td>
                    <p class="font-medium">{{ $record->incident_date->format('d M Y') }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $record->academicYear->name ?? '' }}
                        @if ($record->term) &bull; {{ $record->term->name }} @endif
                    </p>
                </td>
                <td>
                    <p class="font-medium">{{ $record->category_label }}</p>
                    @if ($record->location)
                        <p class="text-xs text-gray-400">{{ $record->location }}</p>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $record->severity_color }}">{{ $record->severity_label }}</span>
                </td>
                <td class="max-w-xs">
                    <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                        {{ $record->action_taken ?? '—' }}
                    </p>
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
                <td class="text-right">
                    <a href="{{ route('disciplinary.show', $record) }}" class="btn btn-xs btn-ghost">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

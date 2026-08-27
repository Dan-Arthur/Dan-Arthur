@extends('layouts.app')
@section('title', 'Incident — ' . $disciplinary->student->full_name ?? 'Record')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Disciplinary Record</h1>
        <p class="page-subtitle">{{ $disciplinary->student->full_name }} &bull; {{ $disciplinary->incident_date->format('d M Y') }}</p>
    </div>
    <div class="flex gap-2">
        @can('edit disciplinary records')
        <a href="{{ route('disciplinary.edit', $disciplinary) }}" class="btn btn-ghost">Edit</a>
        @endcan
        <a href="{{ route('disciplinary.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main details --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="card space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="font-semibold text-gray-900 dark:text-white">Incident Details</h2>
                <div class="flex gap-2">
                    <span class="badge {{ $disciplinary->severity_color }}">{{ $disciplinary->severity_label }}</span>
                    <span class="badge {{ $disciplinary->status_color }}">{{ $disciplinary->status_label }}</span>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Category</dt>
                    <dd class="font-medium mt-0.5">{{ $disciplinary->category_label }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Incident Date</dt>
                    <dd class="font-medium mt-0.5">{{ $disciplinary->incident_date->format('d M Y') }}</dd>
                </div>
                @if ($disciplinary->location)
                <div>
                    <dt class="text-gray-500">Location</dt>
                    <dd class="font-medium mt-0.5">{{ $disciplinary->location }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-gray-500">Academic Year</dt>
                    <dd class="font-medium mt-0.5">
                        {{ $disciplinary->academicYear->name ?? '—' }}
                        @if ($disciplinary->term) &bull; {{ $disciplinary->term->name }} @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Reported By</dt>
                    <dd class="font-medium mt-0.5">{{ $disciplinary->reportedBy->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Recorded At</dt>
                    <dd class="font-medium mt-0.5">{{ $disciplinary->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>

            <div class="pt-3 border-t border-border">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Description</p>
                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $disciplinary->description }}</p>
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-white">Action & Follow-up</h2>

            @if ($disciplinary->action_taken)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Action Taken</p>
                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $disciplinary->action_taken }}</p>
            </div>
            @else
            <p class="text-sm text-gray-400 italic">No action recorded.</p>
            @endif

            @if ($disciplinary->follow_up_date || $disciplinary->follow_up_notes)
            <div class="pt-3 border-t border-border grid grid-cols-2 gap-4">
                @if ($disciplinary->follow_up_date)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Follow-up Date</p>
                    <p class="text-sm font-medium">{{ $disciplinary->follow_up_date->format('d M Y') }}</p>
                </div>
                @endif
                @if ($disciplinary->follow_up_notes)
                <div class="{{ $disciplinary->follow_up_date ? '' : 'col-span-2' }}">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Follow-up Notes</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $disciplinary->follow_up_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">

        <div class="card">
            <h3 class="font-semibold mb-3 text-sm">Student</h3>
            <p class="font-bold text-gray-900 dark:text-white">{{ $disciplinary->student->full_name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $disciplinary->student->student_number ?? $disciplinary->student->admission_number }}
            </p>
            @if ($disciplinary->student->classroom)
            <p class="text-xs text-gray-500 mt-1">{{ $disciplinary->student->classroom->name }}</p>
            @endif
            <div class="mt-3 space-y-2">
                <a href="{{ route('students.show', $disciplinary->student) }}"
                   class="btn btn-ghost btn-xs w-full text-center block">View Profile</a>
                <a href="{{ route('disciplinary.student-history', $disciplinary->student) }}"
                   class="btn btn-ghost btn-xs w-full text-center block">Full History</a>
            </div>
        </div>

        <div class="card text-sm">
            <h3 class="font-semibold mb-3">Parent Notification</h3>
            @if ($disciplinary->parent_notified)
                <span class="badge badge-green">Notified</span>
                @if ($disciplinary->parent_notified_at)
                    <p class="text-xs text-gray-400 mt-1">{{ $disciplinary->parent_notified_at->format('d M Y H:i') }}</p>
                @endif
            @else
                <span class="badge badge-gray">Not Notified</span>
            @endif
        </div>

        @can('delete disciplinary records')
        <div class="card">
            <form method="POST" action="{{ route('disciplinary.destroy', $disciplinary) }}"
                  onsubmit="return confirm('Permanently delete this record?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-xs text-red-600 w-full">Delete Record</button>
            </form>
        </div>
        @endcan

    </div>
</div>
@endsection

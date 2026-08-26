@extends('layouts.app')

@section('title', 'Enrolment — ' . $enrolment->student->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('enrolments.index') }}" class="hover:text-blue-600">Enrolments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $enrolment->student->full_name }}</span>
        </div>
        <div class="flex items-center gap-3">
            <h1 class="page-title">Enrolment Record</h1>
            <span class="badge {{ $enrolment->status_color }}">{{ $enrolment->status_label }}</span>
            @if($enrolment->is_promoted)
            <span class="badge badge-info">Promoted</span>
            @endif
        </div>
    </div>
    @can('edit students')
    <div class="flex items-center gap-2">
        <a href="{{ route('enrolments.edit', $enrolment) }}" class="btn-primary">Edit Enrolment</a>
        <form method="POST" action="{{ route('enrolments.destroy', $enrolment) }}"
            onsubmit="return confirm('Remove this enrolment record? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Student card --}}
    <div class="space-y-5">
        <div class="card p-6 text-center">
            <img src="{{ $enrolment->student->photo_url }}"
                class="w-20 h-20 rounded-full mx-auto object-cover" alt="">
            <h2 class="font-bold text-gray-900 dark:text-white mt-3 text-lg">
                {{ $enrolment->student->full_name }}
            </h2>
            @if($enrolment->student->student_number)
            <p class="text-sm text-gray-500 font-mono">{{ $enrolment->student->student_number }}</p>
            @endif
            <div class="mt-3">
                <a href="{{ route('students.show', $enrolment->student) }}"
                    class="btn-secondary text-sm">View Student Profile</a>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Enrolment Details</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Academic Year</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $enrolment->academicYear->name ?? '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Class</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        @if($enrolment->schoolClass)
                        <a href="{{ route('classes.show', $enrolment->schoolClass) }}"
                            class="hover:text-blue-600">{{ $enrolment->schoolClass->full_name }}</a>
                        @else—@endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Term</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $enrolment->term->name ?? 'Full Year' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Roll Number</span>
                    <span class="font-mono text-gray-900 dark:text-white">
                        {{ $enrolment->roll_number ?? '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Enrolled</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $enrolment->enrolled_date?->format('d M Y') ?? '—' }}
                    </span>
                </div>
                @if($enrolment->exit_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Exit Date</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $enrolment->exit_date->format('d M Y') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        @if($enrolment->exit_reason)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Exit Reason</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $enrolment->exit_reason }}</p>
        </div>
        @endif
    </div>

    {{-- Right: Actions --}}
    <div class="lg:col-span-2 space-y-5">

        @can('edit students')
        {{-- Withdraw form --}}
        @if($enrolment->status === 'active')
        <div class="card p-5" x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between text-sm font-medium text-red-600 dark:text-red-400">
                <span>Withdraw Student</span>
                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <form method="POST" action="{{ route('enrolments.withdraw', $enrolment) }}">
                    @csrf @method('PATCH')
                    <div class="space-y-3">
                        <div>
                            <label class="form-label text-xs">Exit Date <span class="text-red-500">*</span></label>
                            <input type="date" name="exit_date"
                                value="{{ old('exit_date', now()->toDateString()) }}"
                                class="form-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Reason</label>
                            <textarea name="exit_reason" rows="2"
                                class="form-input text-sm resize-none"
                                placeholder="Optional reason for withdrawal…">{{ old('exit_reason') }}</textarea>
                        </div>
                        <button type="submit" class="btn-danger w-full justify-center text-sm"
                            onclick="return confirm('Withdraw {{ addslashes($enrolment->student->full_name) }} from this enrolment?')">
                            Confirm Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan

        {{-- Other enrolments for this student --}}
        @php
        $otherEnrolments = \App\Models\Enrolment::with(['academicYear','schoolClass'])
            ->where('student_id', $enrolment->student_id)
            ->where('id', '!=', $enrolment->id)
            ->orderByDesc('enrolled_date')
            ->limit(10)
            ->get();
        @endphp
        @if($otherEnrolments->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Other Enrolments (Same Student)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($otherEnrolments as $other)
                        <tr>
                            <td class="text-sm">{{ $other->academicYear->name ?? '—' }}</td>
                            <td class="text-sm">{{ $other->schoolClass->full_name ?? '—' }}</td>
                            <td><span class="badge {{ $other->status_color }}">{{ $other->status_label }}</span></td>
                            <td>
                                <a href="{{ route('enrolments.show', $other) }}"
                                    class="text-xs text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Quick navigation --}}
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Links</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('students.show', $enrolment->student) }}" class="btn-secondary text-sm">
                    Student Profile
                </a>
                @if($enrolment->schoolClass)
                <a href="{{ route('classes.show', $enrolment->schoolClass) }}" class="btn-secondary text-sm">
                    Class: {{ $enrolment->schoolClass->full_name }}
                </a>
                @endif
                <a href="{{ route('enrolments.create', ['student_id' => $enrolment->student_id]) }}" class="btn-secondary text-sm">
                    New Enrolment (Same Student)
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

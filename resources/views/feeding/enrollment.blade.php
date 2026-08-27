@extends('layouts.app')

@section('title', 'Feeding Enrollment')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Feeding Enrollment</h1>
        @if ($currentYear)
            <p class="text-sm text-muted mt-1">{{ $currentYear->name }}</p>
        @endif
    </div>
    <div class="flex gap-3">
        @can('manage feeding')
        @if ($availableStudents->isNotEmpty())
        <button class="btn btn-primary" x-data @click="$dispatch('open-enroll-modal')">Enroll Students</button>
        @endif
        @endcan
        <a href="{{ route('feeding.index') }}" class="btn btn-ghost">Dashboard</a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="card mb-4">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="form-group mb-0">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach (\App\Models\FeedingEnrollment::STATUSES as $k => $s)
                    <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $s['label'] }}</option>
                @endforeach
            </select>
        </div>
        @if (request()->hasAny(['class_id', 'status']))
        <a href="{{ route('feeding.enrollment') }}" class="btn btn-ghost">Clear</a>
        @endif
    </div>
</form>

{{-- Enrolled students table --}}
<div class="card">
    <h2 class="card-title mb-4">
        Enrolled Students
        <span class="badge badge-gray ml-2">{{ $enrollments->count() }}</span>
    </h2>

    @if ($enrollments->isEmpty())
        <p class="text-muted text-sm">No enrolled students found.</p>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission #</th>
                        <th>Class</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        @can('manage feeding')
                        <th></th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                    <tr>
                        <td class="font-medium">{{ $enrollment->student->full_name }}</td>
                        <td class="font-mono text-sm text-muted">{{ $enrollment->student->admission_number ?? '—' }}</td>
                        <td class="text-sm">{{ $enrollment->student->currentClass?->name ?? '—' }}</td>
                        <td class="text-sm text-muted">{{ $enrollment->enrolled_date?->format('d M Y') ?? '—' }}</td>
                        <td><span class="{{ $enrollment->status_color }}">{{ $enrollment->status_label }}</span></td>
                        @can('manage feeding')
                        <td class="text-right">
                            <div x-data="{ open: false }" class="relative inline-block">
                                <button @click="open = !open" class="btn btn-xs btn-ghost">Actions ▾</button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute right-0 mt-1 w-40 bg-surface border border-border rounded-lg shadow-lg z-10 py-1">
                                    @if ($enrollment->status !== 'active')
                                    <form method="POST" action="{{ route('feeding.enrollment.update', $enrollment) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-hover">Activate</button>
                                    </form>
                                    @endif
                                    @if ($enrollment->status !== 'suspended')
                                    <form method="POST" action="{{ route('feeding.enrollment.update', $enrollment) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-hover">Suspend</button>
                                    </form>
                                    @endif
                                    @if ($enrollment->status !== 'withdrawn')
                                    <form method="POST" action="{{ route('feeding.unenroll', $enrollment) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-hover"
                                                onclick="return confirm('Remove {{ addslashes($enrollment->student->full_name) }} from feeding program?')">
                                            Withdraw
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@can('manage feeding')
{{-- Enroll Modal --}}
<div x-data="{
        open: false,
        filter: '',
        get filtered() {
            if (!this.filter) return true;
            return false;
        }
     }"
     x-on:open-enroll-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal" style="max-width: 620px; max-height: 85vh; display: flex; flex-direction: column;">
            <h2 class="modal-title">Enroll Students</h2>
            <p class="text-sm text-muted mb-4">Select students to add to the feeding program for {{ $currentYear?->name ?? 'this year' }}.</p>

            <input type="text" x-model="filter" placeholder="Search by name..."
                   class="form-input mb-4" style="flex-shrink: 0;">

            <form method="POST" action="{{ route('feeding.enroll') }}" style="overflow-y: auto; flex: 1;">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $currentYear?->id }}">

                @if ($availableStudents->isEmpty())
                    <p class="text-muted text-sm">All active students are already enrolled.</p>
                @else
                    @foreach ($availableStudents as $className => $students)
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-2">{{ $className }}</p>
                        <div class="space-y-1">
                            @foreach ($students as $student)
                            <label class="flex items-center gap-3 p-2 rounded hover:bg-hover cursor-pointer"
                                   x-show="!filter || '{{ strtolower($student->full_name) }}'.includes(filter.toLowerCase())">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                       class="rounded border-border">
                                <span class="text-sm">{{ $student->full_name }}</span>
                                <span class="text-xs text-muted ml-auto">{{ $student->admission_number }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif

                <div class="flex gap-3 pt-4 border-t border-border mt-4" style="flex-shrink: 0;">
                    <button type="submit" class="btn btn-primary">Enroll Selected</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

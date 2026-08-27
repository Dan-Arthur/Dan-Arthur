@extends('layouts.app')
@section('title', 'Staff Attendance Report')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff Attendance Report</h1>
        <p class="page-subtitle">Attendance summary by date range</p>
    </div>
    <a href="{{ route('staff-attendance.index') }}" class="btn-ghost">← Take Attendance</a>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('staff-attendance.report') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Department</label>
            <select name="department_id" class="form-select text-sm">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected($selectedDeptId == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-input text-sm">
        </div>
        <div>
            <label class="form-label text-xs">To</label>
            <input type="date" name="to" value="{{ $to }}" max="{{ now()->toDateString() }}" class="form-input text-sm">
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Generate Report</button>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card text-center p-4">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $employees->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">Staff</p>
    </div>
    <div class="card text-center p-4">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalDays }}</p>
        <p class="text-xs text-gray-400 mt-1">Working Days</p>
    </div>
    <div class="card text-center p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ \Carbon\Carbon::parse($from)->format('d M') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Period</p>
    </div>
    <div class="card text-center p-4">
        @php
            $totalExpected = $employees->count() * $totalDays;
            $totalPresent = $rawCounts->map(fn($g) => $g->firstWhere('status','present')?->cnt ?? 0)->sum()
                          + $rawCounts->map(fn($g) => $g->firstWhere('status','late')?->cnt ?? 0)->sum();
            $overallPct = $totalExpected > 0 ? round($totalPresent / $totalExpected * 100) : 0;
        @endphp
        <p class="text-2xl font-bold {{ $overallPct >= 90 ? 'text-green-600' : ($overallPct >= 75 ? 'text-amber-500' : 'text-red-500') }}">
            {{ $overallPct }}%
        </p>
        <p class="text-xs text-gray-400 mt-1">Avg Attendance</p>
    </div>
</div>

{{-- Report table --}}
<div class="card overflow-x-auto">
    @if ($employees->isEmpty())
        <div class="p-10 text-center text-gray-400 text-sm">No active staff found.</div>
    @else
    <table class="w-full text-sm data-table">
        <thead>
            <tr>
                <th class="text-left">Staff Member</th>
                <th class="text-left">Department</th>
                <th class="text-center text-green-700">Present</th>
                <th class="text-center text-amber-600">Late</th>
                <th class="text-center text-blue-600">Half Day</th>
                <th class="text-center text-gray-500">On Leave</th>
                <th class="text-center text-red-600">Absent</th>
                <th class="text-center">Total Days</th>
                <th class="text-center font-semibold">Attendance %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
            @php
                $counts    = $rawCounts[$employee->user_id] ?? collect();
                $byStatus  = $counts->keyBy('status');
                $present   = (int) ($byStatus['present']->cnt  ?? 0);
                $late      = (int) ($byStatus['late']->cnt     ?? 0);
                $halfDay   = (int) ($byStatus['half_day']->cnt ?? 0);
                $onLeave   = (int) ($byStatus['on_leave']->cnt ?? 0);
                $absent    = (int) ($byStatus['absent']->cnt   ?? 0);
                $recorded  = $present + $late + $halfDay + $onLeave + $absent;
                $effective = $present + $late + ($halfDay * 0.5);
                $pct       = $totalDays > 0 ? round($effective / $totalDays * 100) : 0;
            @endphp
            <tr>
                <td>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $employee->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $employee->employee_number }}</p>
                </td>
                <td class="text-gray-500 text-xs">{{ $employee->department?->name ?? '—' }}</td>
                <td class="text-center font-medium {{ $present > 0 ? 'text-green-700' : 'text-gray-300 dark:text-gray-700' }}">
                    {{ $present ?: '—' }}
                </td>
                <td class="text-center {{ $late > 0 ? 'text-amber-600' : 'text-gray-300 dark:text-gray-700' }}">
                    {{ $late ?: '—' }}
                </td>
                <td class="text-center {{ $halfDay > 0 ? 'text-blue-600' : 'text-gray-300 dark:text-gray-700' }}">
                    {{ $halfDay ?: '—' }}
                </td>
                <td class="text-center {{ $onLeave > 0 ? 'text-gray-500' : 'text-gray-300 dark:text-gray-700' }}">
                    {{ $onLeave ?: '—' }}
                </td>
                <td class="text-center {{ $absent > 0 ? 'text-red-600 font-medium' : 'text-gray-300 dark:text-gray-700' }}">
                    {{ $absent ?: '—' }}
                </td>
                <td class="text-center text-gray-500">{{ $recorded }}</td>
                <td class="text-center">
                    <span class="font-semibold {{ $pct >= 90 ? 'text-green-600' : ($pct >= 75 ? 'text-amber-500' : 'text-red-500') }}">
                        {{ $totalDays > 0 ? $pct . '%' : '—' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@if ($totalDays === 0 && $employees->isNotEmpty())
<p class="text-xs text-gray-400 text-center mt-4">
    No attendance records found for this period. Start by recording attendance on the
    <a href="{{ route('staff-attendance.index') }}" class="text-blue-600 hover:underline">daily attendance page</a>.
</p>
@endif
@endsection

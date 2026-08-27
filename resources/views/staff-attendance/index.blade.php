@extends('layouts.app')
@section('title', 'Staff Attendance')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Staff Attendance</h1>
        <p class="page-subtitle">Record daily attendance for all active staff</p>
    </div>
    <a href="{{ route('staff-attendance.report', ['department_id' => $selectedDeptId]) }}"
        class="btn-secondary">Reports</a>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('staff-attendance.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Date</label>
            <input type="date" name="date" value="{{ $selectedDate }}" max="{{ now()->toDateString() }}"
                class="form-input text-sm" onchange="this.form.submit()">
        </div>
        <div>
            <label class="form-label text-xs">Department</label>
            <select name="department_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected($selectedDeptId == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">View</button>
    </form>
</div>

{{-- 7-day mini calendar --}}
@if ($summary->isNotEmpty())
<div class="card p-4 mb-5">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Last 7 Days</h3>
    <div class="flex gap-3 overflow-x-auto pb-1">
        @foreach ($summary as $date => $dayData)
        @php
            $dayData = collect($dayData)->keyBy('status');
            $present  = $dayData['present']->cnt  ?? 0;
            $late     = $dayData['late']->cnt     ?? 0;
            $total    = collect($dayData)->sum('cnt');
            $pct      = $total ? round(($present + $late) / $total * 100) : 0;
            $isSelected = $date === $selectedDate;
        @endphp
        <a href="{{ route('staff-attendance.index', ['date' => $date, 'department_id' => $selectedDeptId]) }}"
            class="flex-shrink-0 text-center rounded-lg border-2 p-2 w-16 transition-colors
                {{ $isSelected ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-100 dark:border-gray-800 hover:border-blue-300' }}">
            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($date)->format('D') }}</div>
            <div class="text-sm font-bold {{ $isSelected ? 'text-blue-600' : 'text-gray-900 dark:text-white' }}">
                {{ \Carbon\Carbon::parse($date)->format('d') }}
            </div>
            <div class="text-xs {{ $pct >= 90 ? 'text-green-600' : ($pct >= 75 ? 'text-amber-600' : 'text-red-600') }} font-medium mt-0.5">
                {{ $pct }}%
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- Attendance table --}}
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                @if ($taken)
                    — <span class="text-green-600 text-xs font-normal">Attendance Recorded</span>
                @else
                    — <span class="text-amber-600 text-xs font-normal">Not Yet Recorded</span>
                @endif
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $employees->count() }} active staff member(s)</p>
        </div>
        @if ($taken)
        <div class="flex gap-3 text-sm flex-wrap">
            <span class="text-green-600 font-medium">{{ $attendance->where('status','present')->count() }} Present</span>
            <span class="text-red-500 font-medium">{{ $attendance->where('status','absent')->count() }} Absent</span>
            @if ($attendance->where('status','late')->count() > 0)
                <span class="text-amber-500 font-medium">{{ $attendance->where('status','late')->count() }} Late</span>
            @endif
            @if ($attendance->where('status','on_leave')->count() > 0)
                <span class="text-gray-500 font-medium">{{ $attendance->where('status','on_leave')->count() }} On Leave</span>
            @endif
        </div>
        @endif
    </div>

    @if ($employees->isEmpty())
        <div class="p-10 text-center text-gray-400 text-sm">
            No active staff found{{ $selectedDeptId ? ' in this department' : '' }}.
        </div>
    @else

    @can('manage staff attendance')
    <form method="POST" action="{{ route('staff-attendance.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $selectedDate }}">
    <input type="hidden" name="department_id" value="{{ $selectedDeptId }}">
    @endcan

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase w-6">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Staff Member</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    @can('manage staff attendance')
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason / Note</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach ($employees as $i => $employee)
                @php $record = $attendance[$employee->user_id] ?? null; @endphp
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $employee->full_name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $employee->employee_number }}
                            @if ($employee->position) · {{ $employee->position->name }} @endif
                            @if ($employee->department) · {{ $employee->department->name }} @endif
                        </p>
                    </td>

                    @can('manage staff attendance')
                    <td class="px-4 py-3">
                        <input type="hidden" name="attendance[{{ $i }}][user_id]" value="{{ $employee->user_id }}">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach (\App\Models\StaffAttendance::STATUSES as $key => $info)
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="attendance[{{ $i }}][status]" value="{{ $key }}"
                                    class="text-blue-600"
                                    @checked(($record?->status ?? 'present') === $key)>
                                <span class="badge {{ $info['color'] }} text-xs">{{ $info['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <input type="time" name="attendance[{{ $i }}][check_in]"
                            value="{{ $record?->check_in ? substr($record->check_in, 0, 5) : '' }}"
                            class="form-input text-xs py-1 w-28">
                    </td>
                    <td class="px-4 py-3">
                        <input type="time" name="attendance[{{ $i }}][check_out]"
                            value="{{ $record?->check_out ? substr($record->check_out, 0, 5) : '' }}"
                            class="form-input text-xs py-1 w-28">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="attendance[{{ $i }}][reason]"
                            value="{{ $record?->reason }}"
                            class="form-input text-xs py-1" placeholder="Optional note">
                    </td>
                    @else
                    <td class="px-4 py-3">
                        @if ($record)
                            <span class="badge {{ $record->status_color }}">{{ $record->status_label }}</span>
                        @else
                            <span class="text-gray-300 dark:text-gray-700 text-xs">—</span>
                        @endif
                    </td>
                    @endcan
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @can('manage staff attendance')
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
        <button type="submit" class="btn-primary">
            {{ $taken ? 'Update Attendance' : 'Save Attendance' }}
        </button>
        @if ($taken)
            <span class="text-xs text-gray-400">Previously saved — submitting will overwrite.</span>
        @endif
    </div>
    </form>
    @endcan

    @endif
</div>
@endsection

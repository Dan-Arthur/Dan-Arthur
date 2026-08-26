@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('attendance.index') }}" class="hover:text-blue-600">Attendance</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Report</span>
        </div>
        <h1 class="page-title">Attendance Report</h1>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('attendance.report') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Academic Year</label>
            <select name="year_id" class="form-select text-sm">
                @foreach($years as $year)
                <option value="{{ $year->id }}" @selected($selectedYearId == $year->id)>
                    {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Class</label>
            <select name="class_id" class="form-select text-sm">
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-input text-sm">
        </div>
        <div>
            <label class="form-label text-xs">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-input text-sm">
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Generate Report</button>
    </form>
</div>

@if($selectedClassId && $report->isNotEmpty())
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </h3>
        <p class="text-xs text-gray-400 mt-0.5">{{ $totalDays }} school day(s) in range</p>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th class="text-center text-green-600">Present</th>
                    <th class="text-center text-red-500">Absent</th>
                    <th class="text-center text-amber-500">Late</th>
                    <th class="text-center text-blue-500">Excused</th>
                    <th class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report as $studentId => $rows)
                @php
                    $rows     = collect($rows)->keyBy('status');
                    $student  = $rows->first()->student;
                    $present  = $rows['present']->cnt ?? 0;
                    $absent   = $rows['absent']->cnt ?? 0;
                    $late     = $rows['late']->cnt ?? 0;
                    $excused  = $rows['excused']->cnt ?? 0;
                    $attended = $present + $late;
                    $pct      = $totalDays > 0 ? round($attended / $totalDays * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="text-gray-400 text-xs">{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('students.show', $studentId) }}"
                            class="font-medium text-gray-900 dark:text-white hover:text-blue-600 text-sm">
                            {{ $student?->full_name ?? 'Unknown' }}
                        </a>
                    </td>
                    <td class="text-center font-medium text-green-600">{{ $present }}</td>
                    <td class="text-center font-medium text-red-500">{{ $absent }}</td>
                    <td class="text-center font-medium text-amber-500">{{ $late }}</td>
                    <td class="text-center font-medium text-blue-500">{{ $excused }}</td>
                    <td class="text-center">
                        <span class="font-semibold {{ $pct >= 90 ? 'text-green-600' : ($pct >= 75 ? 'text-amber-500' : 'text-red-500') }}">
                            {{ $pct }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($selectedClassId)
<div class="card p-10 text-center text-gray-400 text-sm">
    No attendance records found for this class in the selected date range.
</div>
@else
<div class="card p-10 text-center text-gray-400 text-sm">
    Select a class and date range to generate the report.
</div>
@endif
@endsection

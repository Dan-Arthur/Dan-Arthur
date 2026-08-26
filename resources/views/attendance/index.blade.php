@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Attendance</h1>
        <p class="page-subtitle">Record and view student attendance</p>
    </div>
    <a href="{{ route('attendance.report', ['class_id' => $selectedClassId, 'year_id' => $selectedYearId]) }}"
        class="btn-secondary">Reports</a>
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">Date</label>
            <input type="date" name="date" value="{{ $selectedDate }}" max="{{ now()->toDateString() }}"
                class="form-input text-sm" onchange="this.form.submit()">
        </div>
        <div>
            <label class="form-label text-xs">Academic Year</label>
            <select name="year_id" class="form-select text-sm" onchange="this.form.submit()">
                @foreach($years as $year)
                <option value="{{ $year->id }}" @selected($selectedYearId == $year->id)>
                    {{ $year->name }}{{ $year->is_current ? ' (Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Class</label>
            <select name="class_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">— Select class —</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->full_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">View</button>
    </form>
</div>

@if($selectedClassId && $selectedYearId)

{{-- 7-day mini summary --}}
@if($summary->isNotEmpty())
<div class="card p-4 mb-5">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Last 7 Days</h3>
    <div class="flex gap-3 overflow-x-auto pb-1">
        @foreach($summary as $date => $dayData)
        @php
            $dayData = collect($dayData)->keyBy('status');
            $present = $dayData['present']->cnt ?? 0;
            $absent  = $dayData['absent']->cnt ?? 0;
            $late    = $dayData['late']->cnt ?? 0;
            $total   = collect($dayData)->sum('cnt');
            $pct     = $total ? round(($present + $late) / $total * 100) : 0;
            $isSelected = $date === $selectedDate;
        @endphp
        <a href="{{ route('attendance.index', ['class_id' => $selectedClassId, 'year_id' => $selectedYearId, 'date' => $date]) }}"
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
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                @if($taken)
                — <span class="text-green-600 text-xs">Attendance Recorded</span>
                @else
                — <span class="text-amber-600 text-xs">Not Yet Recorded</span>
                @endif
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $students->count() }} student(s) enrolled</p>
        </div>
        @if($taken)
        <div class="flex gap-3 text-sm">
            <span class="text-green-600 font-medium">{{ $attendance->where('status','present')->count() }} Present</span>
            <span class="text-red-500 font-medium">{{ $attendance->where('status','absent')->count() }} Absent</span>
            @if($attendance->where('status','late')->count() > 0)
            <span class="text-amber-500 font-medium">{{ $attendance->where('status','late')->count() }} Late</span>
            @endif
        </div>
        @endif
    </div>

    @if($students->isEmpty())
    <div class="p-10 text-center text-gray-400 text-sm">
        No students enrolled in this class for the selected year.
    </div>
    @else

    {{-- Attendance form --}}
    @can('take attendance')
    <form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
    <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
    <input type="hidden" name="date" value="{{ $selectedDate }}">
    @endcan

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    @can('take attendance')
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Arrival Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                    @else
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($students as $i => $student)
                @php $record = $attendance[$student->id] ?? null; @endphp
                <tr>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('students.show', $student) }}"
                            class="font-medium text-gray-900 dark:text-white hover:text-blue-600 text-sm">
                            {{ $student->full_name }}
                        </a>
                    </td>
                    @can('take attendance')
                    <td class="px-4 py-3">
                        <input type="hidden" name="attendance[{{ $i }}][student_id]" value="{{ $student->id }}">
                        <div class="flex gap-2">
                            @foreach(\App\Models\StudentAttendance::STATUSES as $key => $info)
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
                        <input type="time" name="attendance[{{ $i }}][arrival_time]"
                            value="{{ $record?->arrival_time ? substr($record->arrival_time, 0, 5) : '' }}"
                            class="form-input text-xs py-1 w-28">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="attendance[{{ $i }}][reason]"
                            value="{{ $record?->reason }}"
                            class="form-input text-xs py-1" placeholder="Optional note">
                    </td>
                    @else
                    <td class="px-4 py-3">
                        @if($record)
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

    @can('take attendance')
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
        <button type="submit" class="btn-primary">
            {{ $taken ? 'Update Attendance' : 'Save Attendance' }}
        </button>
        @if($taken)
        <span class="text-xs text-gray-400">
            Recorded by {{ $attendance->first()?->takenBy?->name ?? 'Unknown' }}
            at {{ $attendance->first()?->updated_at?->format('H:i') }}
        </span>
        @endif
    </div>
    </form>
    @endcan
    @endif
</div>
@else
<div class="card p-10 text-center text-gray-400 text-sm">
    Select a class and date to view or record attendance.
</div>
@endif

@endsection

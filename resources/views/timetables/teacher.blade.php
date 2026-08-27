@extends('layouts.app')

@section('title', 'Teacher Timetable')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Teacher Timetable</h1>
        <p class="page-subtitle">Weekly schedule by teacher</p>
    </div>
    <div class="flex gap-2">
        @if($selectedTeacherId && $periods->isNotEmpty())
        <button onclick="window.print()" class="btn-secondary print:hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        @endif
        @can('manage timetables')
        <a href="{{ route('timetables.periods') }}" class="btn-secondary print:hidden">Manage Periods</a>
        @endcan
    </div>
</div>

{{-- View switcher + filters --}}
<div class="card p-4 mb-5">
    <div class="flex flex-wrap gap-4 items-end">
        {{-- View toggle --}}
        <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden text-sm">
            <a href="{{ route('timetables.index', ['view' => 'class', 'year_id' => $selectedYearId]) }}"
                class="px-4 py-2 font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                By Class
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'teacher']) }}"
                class="px-4 py-2 font-medium border-l border-gray-200 dark:border-gray-700 bg-blue-600 text-white">
                By Teacher
            </a>
        </div>

        <form method="GET" action="{{ route('timetables.teacher') }}" class="flex flex-wrap gap-3 items-end flex-1">
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
                <label class="form-label text-xs">Teacher</label>
                <select name="teacher_id" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">— Select teacher —</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($selectedTeacherId == $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

@if($periods->isEmpty())
<div class="card p-10 text-center text-gray-400 text-sm">
    No periods defined.
    @can('edit timetables')
    <a href="{{ route('timetables.periods') }}" class="text-blue-600 hover:underline ml-1">Set up periods first.</a>
    @endcan
</div>
@elseif(!$selectedTeacherId)
<div class="card p-10 text-center text-gray-400 text-sm">Select a teacher to view their timetable.</div>
@else
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-32">Period</th>
                    @foreach($activeDays as $day)
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase min-w-[160px]">
                        {{ \App\Models\Timetable::DAYS[$day] }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                <tr class="{{ $period->is_break ? 'bg-amber-50 dark:bg-amber-900/10' : '' }} border-b border-gray-100 dark:border-gray-800">
                    <td class="px-4 py-3 align-top">
                        <div class="font-medium text-xs text-gray-700 dark:text-gray-300">{{ $period->name }}</div>
                        <div class="text-xs text-gray-400">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</div>
                        @if($period->is_break)
                        <span class="badge badge-warning text-xs mt-0.5">Break</span>
                        @endif
                    </td>
                    @foreach($activeDays as $day)
                    @php $slot = $grid[$period->id][$day] ?? null; @endphp
                    <td class="px-3 py-2 align-top border-l border-gray-100 dark:border-gray-800">
                        @if($period->is_break)
                        <span class="text-xs text-amber-600 dark:text-amber-400 italic">Break</span>
                        @elseif($slot)
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2">
                            <p class="font-semibold text-xs text-green-800 dark:text-green-300">{{ $slot->subject->name ?? '—' }}</p>
                            @if($slot->schoolClass)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $slot->schoolClass->full_name }}</p>
                            @endif
                            @if($slot->room)
                            <p class="text-xs text-gray-400">{{ $slot->room }}</p>
                            @endif
                        </div>
                        @else
                        <div class="py-4 text-center text-gray-200 dark:text-gray-800 text-xs">Free</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

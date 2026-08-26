@extends('layouts.app')

@section('title', 'Timetable')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Timetable</h1>
        <p class="page-subtitle">Weekly class schedule</p>
    </div>
    @can('manage timetables')
    <a href="{{ route('timetables.periods') }}" class="btn-secondary">Manage Periods</a>
    @endcan
</div>

{{-- View switcher + filters --}}
<div class="card p-4 mb-5">
    <div class="flex flex-wrap gap-4 items-end">
        {{-- View toggle --}}
        <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden text-sm">
            <a href="{{ route('timetables.index', ['year_id' => $selectedYearId]) }}"
                class="px-4 py-2 font-medium bg-blue-600 text-white">
                By Class
            </a>
            <a href="{{ route('timetables.teacher', ['year_id' => $selectedYearId]) }}"
                class="px-4 py-2 font-medium border-l border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                By Teacher
            </a>
        </div>

        <form method="GET" action="{{ route('timetables.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
            <input type="hidden" name="view" value="class">
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
        </form>
    </div>
</div>

@if($periods->isEmpty())
<div class="card p-10 text-center text-gray-400 text-sm">
    No periods defined yet.
    @can('manage timetables')
    <a href="{{ route('timetables.periods') }}" class="text-blue-600 hover:underline ml-1">Set up periods first.</a>
    @endcan
</div>
@elseif(!$selectedClassId)
<div class="card p-10 text-center text-gray-400 text-sm">Select a class to view its timetable.</div>
@else
{{-- Timetable grid --}}
<div class="card overflow-hidden mb-6">
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
                    {{-- Period label --}}
                    <td class="px-4 py-3 align-top">
                        <div class="font-medium text-xs text-gray-700 dark:text-gray-300">{{ $period->name }}</div>
                        <div class="text-xs text-gray-400">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</div>
                        @if($period->is_break)
                        <span class="badge badge-warning text-xs mt-0.5">Break</span>
                        @endif
                    </td>

                    {{-- Day cells --}}
                    @foreach($activeDays as $day)
                    @php $slot = $grid[$period->id][$day] ?? null; @endphp
                    <td class="px-3 py-2 align-top border-l border-gray-100 dark:border-gray-800">
                        @if($period->is_break)
                        <span class="text-xs text-amber-600 dark:text-amber-400 italic">Break</span>
                        @elseif($slot)
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 relative group" x-data="{ editing: false }">
                            <div x-show="!editing">
                                <p class="font-semibold text-xs text-blue-800 dark:text-blue-300">{{ $slot->subject->name ?? '—' }}</p>
                                @if($slot->teacher)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $slot->teacher->name }}</p>
                                @endif
                                @if($slot->room)
                                <p class="text-xs text-gray-400">{{ $slot->room }}</p>
                                @endif
                                @can('manage timetables')
                                <div class="absolute top-1 right-1 hidden group-hover:flex gap-1">
                                    <button @click="editing = true"
                                        class="p-0.5 rounded text-blue-400 hover:text-blue-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('timetables.destroy', $slot) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-0.5 rounded text-red-400 hover:text-red-600"
                                            onclick="return confirm('Remove this slot?')">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endcan
                            </div>
                            @can('manage timetables')
                            <div x-show="editing" style="display:none">
                                <form method="POST" action="{{ route('timetables.update', $slot) }}">
                                    @csrf @method('PUT')
                                    <select name="subject_id" class="form-select text-xs mb-1 py-1">
                                        @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}" @selected($slot->subject_id == $subj->id)>{{ $subj->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="teacher_id" class="form-select text-xs mb-1 py-1">
                                        <option value="">— Teacher —</option>
                                        @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" @selected($slot->teacher_id == $t->id)>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="room" value="{{ $slot->room }}"
                                        class="form-input text-xs mb-1 py-1" placeholder="Room (opt.)">
                                    <div class="flex gap-1">
                                        <button type="submit" class="btn-primary text-xs py-0.5 px-2">Save</button>
                                        <button type="button" @click="editing = false" class="btn-secondary text-xs py-0.5 px-2">×</button>
                                    </div>
                                </form>
                            </div>
                            @endcan
                        </div>
                        @else
                        @can('manage timetables')
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full text-center text-gray-300 dark:text-gray-700 hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg py-4 transition-colors text-xs border-2 border-dashed border-transparent hover:border-blue-200">
                                + Add
                            </button>
                            <div x-show="open" x-collapse class="mt-1">
                                <form method="POST" action="{{ route('timetables.store') }}">
                                    @csrf
                                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                                    <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                                    <input type="hidden" name="period_id" value="{{ $period->id }}">
                                    <input type="hidden" name="day_of_week" value="{{ $day }}">
                                    <select name="subject_id" class="form-select text-xs mb-1 py-1" required>
                                        <option value="">Subject…</option>
                                        @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="teacher_id" class="form-select text-xs mb-1 py-1">
                                        <option value="">Teacher (opt.)</option>
                                        @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="room" class="form-input text-xs mb-1 py-1" placeholder="Room (opt.)">
                                    <div class="flex gap-1">
                                        <button type="submit" class="btn-primary text-xs py-0.5 px-2">Add</button>
                                        <button type="button" @click="open = false" class="btn-secondary text-xs py-0.5 px-2">×</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endcan
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

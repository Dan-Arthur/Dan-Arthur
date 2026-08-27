@extends('layouts.app')

@section('title', 'Timetable')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Timetable</h1>
        <p class="page-subtitle">Weekly class schedule</p>
    </div>
    <div class="flex gap-2 print:hidden">
        @if($selectedClassId && $periods->isNotEmpty())
        <button onclick="window.print()" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        @endif
        @can('manage timetables')
        <a href="{{ route('timetables.periods') }}" class="btn-secondary">Manage Periods</a>
        @endcan
    </div>
</div>

{{-- View switcher + filters --}}
<div class="card p-4 mb-5 print:hidden">
    <div class="flex flex-wrap gap-4 items-end">
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
<div class="card p-10 text-center text-gray-400 text-sm print:hidden">
    No periods defined yet.
    @can('manage timetables')
    <a href="{{ route('timetables.periods') }}" class="text-blue-600 hover:underline ml-1">Set up periods first.</a>
    @endcan
</div>
@elseif(!$selectedClassId)
<div class="card p-10 text-center text-gray-400 text-sm print:hidden">Select a class to view its timetable.</div>
@else

{{-- Print header (hidden on screen) --}}
<div class="hidden print:block mb-4">
    <h2 class="text-lg font-bold">{{ $classes->firstWhere('id', $selectedClassId)?->full_name }} — Timetable</h2>
    <p class="text-sm text-gray-600">{{ $years->firstWhere('id', $selectedYearId)?->name }}</p>
</div>

@if(session('success'))
<div class="mb-4 p-3 rounded bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 text-sm print:hidden">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm print:hidden">
    {{ session('error') }}
</div>
@endif

{{-- Timetable grid --}}
<div class="card overflow-hidden mb-6" x-data="timetableApp()">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-32">Period</th>
                    @foreach($activeDays as $day)
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase min-w-[170px]">
                        {{ \App\Models\Timetable::DAYS[$day] }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                <tr class="{{ $period->is_break ? 'bg-amber-50 dark:bg-amber-900/10' : '' }} border-b border-gray-100 dark:border-gray-800">
                    {{-- Period label --}}
                    <td class="px-4 py-3 align-top whitespace-nowrap">
                        <div class="font-medium text-xs text-gray-700 dark:text-gray-300">{{ $period->name }}</div>
                        <div class="text-xs text-gray-400">{{ substr($period->start_time,0,5) }}–{{ substr($period->end_time,0,5) }}</div>
                        @if($period->is_break)
                        <span class="badge badge-warning text-xs mt-0.5">Break</span>
                        @endif
                    </td>

                    {{-- Day cells --}}
                    @foreach($activeDays as $day)
                    @php $slot = $grid[$period->id][$day] ?? null; @endphp
                    <td class="px-2 py-2 align-top border-l border-gray-100 dark:border-gray-800">
                        @if($period->is_break)
                        <div class="text-center text-xs text-amber-600 dark:text-amber-400 italic py-2">— Break —</div>
                        @elseif($slot)
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 relative group" x-data="{ editing: false }">
                            <div x-show="!editing">
                                <p class="font-semibold text-xs text-blue-800 dark:text-blue-300 leading-tight">{{ $slot->subject->name ?? '—' }}</p>
                                @if($slot->teacher)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $slot->teacher->full_name }}</p>
                                @endif
                                @if($slot->room)
                                <p class="text-xs text-gray-400">Room {{ $slot->room }}</p>
                                @endif
                                @can('manage timetables')
                                <div class="absolute top-1 right-1 hidden group-hover:flex gap-1 print:hidden">
                                    <button @click="editing = true; $nextTick(() => $el.closest('td').scrollIntoView({block:'nearest'}))"
                                        class="p-0.5 rounded text-blue-400 hover:text-blue-600" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('timetables.destroy', $slot) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-0.5 rounded text-red-400 hover:text-red-600"
                                            onclick="return confirm('Remove this slot?')" title="Delete">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endcan
                            </div>
                            @can('manage timetables')
                            <div x-show="editing" style="display:none" class="print:hidden">
                                <form method="POST" action="{{ route('timetables.update', $slot) }}">
                                    @csrf @method('PUT')
                                    <select name="subject_id" class="form-select text-xs mb-1 py-1" required
                                        @change="autoFillTeacher($event.target.value, $el.closest('form'))">
                                        @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}" @selected($slot->subject_id == $subj->id)>{{ $subj->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="teacher_id" class="form-select text-xs mb-1 py-1">
                                        <option value="">— Teacher —</option>
                                        @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" @selected($slot->teacher_id == $t->id)>{{ $t->full_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="room" value="{{ $slot->room }}"
                                        class="form-input text-xs mb-1 py-1" placeholder="Room (opt.)">
                                    <div class="flex gap-1 mt-1">
                                        <button type="submit" class="btn-primary text-xs py-0.5 px-2">Save</button>
                                        <button type="button" @click="editing = false" class="btn-secondary text-xs py-0.5 px-2">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            @endcan
                        </div>
                        @else
                        @can('manage timetables')
                        <div x-data="{ open: false }" class="print:hidden">
                            <button @click="open = !open"
                                class="w-full text-center text-gray-300 dark:text-gray-700 hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg py-3 transition-colors text-xs border-2 border-dashed border-transparent hover:border-blue-200">
                                + Add
                            </button>
                            <div x-show="open" x-collapse class="mt-1">
                                <form method="POST" action="{{ route('timetables.store') }}">
                                    @csrf
                                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                                    <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                                    <input type="hidden" name="period_id" value="{{ $period->id }}">
                                    <input type="hidden" name="day_of_week" value="{{ $day }}">
                                    <select name="subject_id" class="form-select text-xs mb-1 py-1" required
                                        @change="autoFillTeacher($event.target.value, $el.closest('form'))">
                                        <option value="">Subject…</option>
                                        @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="teacher_id" class="form-select text-xs mb-1 py-1">
                                        <option value="">Teacher (opt.)</option>
                                        @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="room" class="form-input text-xs mb-1 py-1" placeholder="Room (opt.)">
                                    <div class="flex gap-1 mt-1">
                                        <button type="submit" class="btn-primary text-xs py-0.5 px-2">Add</button>
                                        <button type="button" @click="open = false" class="btn-secondary text-xs py-0.5 px-2">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @else
                        <div class="py-3 text-center text-xs text-gray-200 dark:text-gray-800">—</div>
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

<script>
function timetableApp() {
    return {
        subjectTeacherMap: @json($subjectTeacherMap),

        autoFillTeacher(subjectId, form) {
            if (!subjectId) return;
            const suggested = this.subjectTeacherMap[subjectId];
            if (!suggested) return;
            const teacherSelect = form.querySelector('[name="teacher_id"]');
            if (teacherSelect && teacherSelect.value === '') {
                teacherSelect.value = suggested;
            }
        },
    };
}
</script>

<style>
@media print {
    .sidebar, nav, header, .page-header .flex { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    table td, table th { font-size: 11px !important; padding: 4px 6px !important; }
    .group:hover .group-hover\:flex { display: none !important; }
}
</style>

@endif
@endsection

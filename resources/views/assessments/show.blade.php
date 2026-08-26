@extends('layouts.app')

@section('title', $assessment->title)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('assessments.index') }}" class="hover:text-blue-600">Assessments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="truncate max-w-xs">{{ $assessment->title }}</span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="page-title">{{ $assessment->title }}</h1>
            <span class="badge {{ $assessment->status_color }}">{{ $assessment->status_label }}</span>
            @if($assessment->marks_entered)
            <span class="badge badge-success">Marks Entered</span>
            @endif
        </div>
    </div>
    @can('edit assessments')
    <div class="flex items-center gap-2">
        <a href="{{ route('assessments.edit', $assessment) }}" class="btn-secondary">Edit</a>
        @can('delete assessments')
        <form method="POST" action="{{ route('assessments.destroy', $assessment) }}"
            onsubmit="return confirm('Delete this assessment?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Delete</button>
        </form>
        @endcan
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Details --}}
    <div class="space-y-5">
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Details</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Class</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $assessment->schoolClass->full_name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Subject</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->subject->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Teacher</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->teacher->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Term</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->term->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->type_label }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Max Score</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $assessment->max_score }}</span>
                </div>
                @if($assessment->weight)
                <div class="flex justify-between">
                    <span class="text-gray-500">Weight</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->weight }}%</span>
                </div>
                @endif
                @if($assessment->assessment_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Date</span>
                    <span class="text-gray-900 dark:text-white">{{ $assessment->assessment_date->format('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        @if($stats['count'] > 0)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Statistics</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Students Marked</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $stats['count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Average</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $stats['average'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Highest</span>
                    <span class="font-medium text-green-600">{{ $stats['highest'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lowest</span>
                    <span class="font-medium text-red-500">{{ $stats['lowest'] ?? '—' }}</span>
                </div>
                @if($stats['absent'] > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Absent</span>
                    <span class="font-medium text-amber-500">{{ $stats['absent'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Marks entry --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Marks — {{ $students->count() }} student(s)
                </h3>
            </div>
            @if($students->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No students enrolled in this class.</div>
            @else
            @can('enter marks')
            <form method="POST" action="{{ route('assessments.marks', $assessment) }}">
            @csrf
            @endcan

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Score /{{ $assessment->max_score }}</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Exempt</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($students as $i => $student)
                        @php $mark = $marks[$student->id] ?? null; @endphp
                        <tr>
                            <td class="px-6 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">
                                @can('enter marks')
                                <input type="hidden" name="marks[{{ $i }}][student_id]" value="{{ $student->id }}">
                                @endcan
                                <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $student->full_name }}</span>
                            </td>
                            @can('enter marks')
                            <td class="px-4 py-2 text-center">
                                <input type="number" name="marks[{{ $i }}][score]"
                                    value="{{ $mark?->score }}"
                                    min="0" max="{{ $assessment->max_score }}" step="0.5"
                                    class="form-input text-sm text-center w-20 py-1">
                            </td>
                            <td class="px-4 py-2 text-center">
                                <input type="hidden" name="marks[{{ $i }}][is_absent]" value="0">
                                <input type="checkbox" name="marks[{{ $i }}][is_absent]" value="1"
                                    @checked($mark?->is_absent) class="rounded">
                            </td>
                            <td class="px-4 py-2 text-center">
                                <input type="hidden" name="marks[{{ $i }}][is_exempt]" value="0">
                                <input type="checkbox" name="marks[{{ $i }}][is_exempt]" value="1"
                                    @checked($mark?->is_exempt) class="rounded">
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="marks[{{ $i }}][remarks]"
                                    value="{{ $mark?->remarks }}"
                                    class="form-input text-xs py-1" placeholder="Optional note">
                            </td>
                            @else
                            <td class="px-4 py-2 text-center font-medium {{ $mark?->is_absent ? 'text-red-500' : ($mark?->is_exempt ? 'text-gray-400' : 'text-gray-900 dark:text-white') }}">
                                @if($mark)
                                    @if($mark->is_absent) ABS
                                    @elseif($mark->is_exempt) EXM
                                    @else {{ $mark->score ?? '—' }}
                                    @endif
                                @else —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center text-xs text-gray-400">{{ $mark?->is_absent ? '✓' : '' }}</td>
                            <td class="px-4 py-2 text-center text-xs text-gray-400">{{ $mark?->is_exempt ? '✓' : '' }}</td>
                            <td class="px-4 py-2 text-xs text-gray-400">{{ $mark?->remarks }}</td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @can('enter marks')
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" class="btn-primary">
                    {{ $assessment->marks_entered ? 'Update Marks' : 'Save Marks' }}
                </button>
            </div>
            </form>
            @endcan
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.portal')
@section('title', 'Term Report — ' . $result->student->full_name)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('portal.child', $result->student_id) }}" class="text-gray-400 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ $result->term->name ?? 'Full Year' }} Result
        </h1>
        <p class="text-sm text-gray-500">{{ $result->student->full_name }} &bull; {{ $result->academicYear->name }}</p>
    </div>
</div>

{{-- Summary strip --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $result->average_score !== null ? number_format($result->average_score, 1) . '%' : '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Average Score</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-purple-600">{{ $result->overall_grade ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Overall Grade</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">
            @if ($result->position && $result->class_size)
                {{ $result->position }}<span class="text-sm text-gray-400">/{{ $result->class_size }}</span>
            @else —
            @endif
        </p>
        <p class="text-xs text-gray-500 mt-1">Class Position</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $result->schoolClass->name ?? '—' }}</p>
        <p class="text-xs text-gray-500 mt-1">Class</p>
    </div>
</div>

{{-- Subject scores --}}
@if ($result->subjectScores->isNotEmpty())
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="font-bold text-gray-900 dark:text-white">Subject Scores</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800 text-left">
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Subject</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Class Work</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Exam</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Total</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Grade</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase text-left">Remark</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach ($result->subjectScores as $score)
                <tr>
                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                        {{ $score->subject->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-gray-600 dark:text-gray-400">
                        {{ $score->class_score !== null ? number_format($score->class_score, 1) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-gray-600 dark:text-gray-400">
                        {{ $score->exam_score !== null ? number_format($score->exam_score, 1) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">
                        {{ $score->total_score !== null ? number_format($score->total_score, 1) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if ($score->grade)
                            <span class="px-2 py-0.5 rounded text-xs font-bold
                                {{ in_array($score->grade, ['A','A+']) ? 'bg-green-100 text-green-700' :
                                   (str_starts_with($score->grade, 'B') ? 'bg-blue-100 text-blue-700' :
                                   (str_starts_with($score->grade, 'C') ? 'bg-yellow-100 text-yellow-700' :
                                   'bg-red-100 text-red-700')) }}">
                                {{ $score->grade }}
                            </span>
                        @else —
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $score->remark ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Comments --}}
@if ($result->class_teacher_comment || $result->principal_comment || $result->overall_remark)
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
    <h2 class="font-bold text-gray-900 dark:text-white">Comments</h2>
    @if ($result->overall_remark)
    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Overall Remark</p>
        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $result->overall_remark }}</p>
    </div>
    @endif
    @if ($result->class_teacher_comment)
    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Class Teacher</p>
        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $result->class_teacher_comment }}"</p>
    </div>
    @endif
    @if ($result->principal_comment)
    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Principal</p>
        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $result->principal_comment }}"</p>
    </div>
    @endif
</div>
@endif
@endsection

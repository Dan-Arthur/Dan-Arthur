@extends('layouts.app')

@section('title', 'Result — ' . $result->student?->full_name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('results.index', ['class_id' => $result->class_id, 'year_id' => $result->academic_year_id, 'term_id' => $result->term_id]) }}"
                class="hover:text-blue-600">Results</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $result->student?->full_name }}</span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="page-title">{{ $result->student?->full_name }}</h1>
            <span class="badge {{ $result->status_color }}">{{ $result->status_label }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('results.report-card', $result) }}" target="_blank" class="btn-ghost">
            Print Report Card
        </a>

        @if($result->status === 'draft' || $result->status === 'pending_approval')
        @can('approve results')
        <form method="POST" action="{{ route('results.approve', $result) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn-secondary">Approve</button>
        </form>
        @endcan
        @endif

        @if(in_array($result->status, ['draft', 'approved']))
        @can('publish results')
        <form method="POST" action="{{ route('results.publish', $result) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn-primary">Publish</button>
        </form>
        @endcan
        @endif
    </div>
</div>

{{-- Report card header --}}
<div class="card p-6 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs text-gray-400">Class</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $result->schoolClass->full_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Term</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $result->term->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Academic Year</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $result->academicYear->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Position</p>
            <p class="font-medium text-gray-900 dark:text-white">
                {{ $result->position ? $result->position . ' / ' . $result->class_size : '—' }}
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Score</p>
            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $result->total_score ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Average</p>
            <p class="font-bold text-lg {{ ($result->average_score ?? 0) >= 50 ? 'text-green-600' : 'text-red-500' }}">
                {{ $result->average_score ? number_format($result->average_score, 1) . '%' : '—' }}
            </p>
        </div>
        @if($result->overall_grade)
        <div>
            <p class="text-xs text-gray-400">Grade</p>
            <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ $result->overall_grade }}</p>
        </div>
        @endif
        @if($result->overall_remark)
        <div>
            <p class="text-xs text-gray-400">Remark</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $result->overall_remark }}</p>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Subject scores --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Subject Scores ({{ $result->subjectScores->count() }})
                </h3>
            </div>
            @if($result->subjectScores->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No subject scores recorded yet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">CA</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Exam</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Pos</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Class Avg</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($result->subjectScores as $score)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $score->subject->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $score->ca_score ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $score->exam_score ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold {{ ($score->total_score ?? 0) >= 50 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $score->total_score ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">
                                {{ $score->grade ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $score->position ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">
                                {{ $score->class_average ? number_format($score->class_average, 1) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Comments --}}
    <div class="space-y-5">
        @can('edit results')
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Comments</h3>
            <form method="POST" action="{{ route('results.comment', $result) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="form-label text-xs">Class Teacher</label>
                    <textarea name="class_teacher_comment" rows="3"
                        class="form-input text-sm" placeholder="Teacher's comment…">{{ $result->class_teacher_comment }}</textarea>
                </div>
                @can('approve results')
                <div>
                    <label class="form-label text-xs">Principal</label>
                    <textarea name="principal_comment" rows="3"
                        class="form-input text-sm" placeholder="Principal's comment…">{{ $result->principal_comment }}</textarea>
                </div>
                @endcan
                <button type="submit" class="btn-primary text-sm w-full">Save Comments</button>
            </form>
        </div>
        @else
        @if($result->class_teacher_comment || $result->principal_comment)
        <div class="card p-5 space-y-3">
            @if($result->class_teacher_comment)
            <div>
                <p class="text-xs text-gray-400 mb-1">Class Teacher's Comment</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $result->class_teacher_comment }}</p>
            </div>
            @endif
            @if($result->principal_comment)
            <div>
                <p class="text-xs text-gray-400 mb-1">Principal's Comment</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $result->principal_comment }}</p>
            </div>
            @endif
        </div>
        @endif
        @endcan

        @if($result->approved_at)
        <div class="card p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Approval</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Approved by {{ $result->approvedBy?->name ?? '—' }}
                on {{ $result->approved_at->format('d M Y') }}
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

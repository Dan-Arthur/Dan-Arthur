@extends('layouts.app')

@section('title', 'Enrolments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Enrolments</h1>
        <p class="page-subtitle">Manage student class placements by academic year</p>
    </div>
    @can('edit students')
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('enrolments.promote') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            Promote Class
        </a>
        <a href="{{ route('enrolments.bulk') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Bulk Enrol
        </a>
        <a href="{{ route('enrolments.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Enrol Student
        </a>
    </div>
    @endcan
</div>

{{-- Status summary --}}
@php
$totalCount = $statusCounts->sum();
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['status'=>null,        'label'=>'Total',       'class'=>'border-gray-200 dark:border-gray-700'],
        ['status'=>'active',    'label'=>'Active',      'class'=>'border-green-200 dark:border-green-800'],
        ['status'=>'withdrawn', 'label'=>'Withdrawn',   'class'=>'border-red-200 dark:border-red-800'],
        ['status'=>'transferred','label'=>'Transferred','class'=>'border-yellow-200 dark:border-yellow-800'],
    ] as $stat)
    <a href="{{ request()->fullUrlWithQuery(['status' => $stat['status']]) }}"
        class="card p-4 border-l-4 {{ $stat['class'] }} hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $stat['status'] ? ($statusCounts[$stat['status']] ?? 0) : $totalCount }}
        </div>
        <div class="text-xs text-gray-500 mt-0.5">{{ $stat['label'] }}</div>
    </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('enrolments.index') }}" class="flex flex-wrap gap-3 items-end">
        {{-- Academic year --}}
        <div class="min-w-[180px]">
            <label class="form-label text-xs">Academic Year</label>
            <select name="year_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All Years</option>
                @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(request('year_id', $selectedYearId) == $year->id)>
                    {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Class --}}
        <div class="min-w-[160px]">
            <label class="form-label text-xs">Class</label>
            <select name="class_id" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                    {{ $class->full_name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Enrolment::STATUSES as $key => $info)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Search --}}
        <div class="flex-1 min-w-[200px]">
            <label class="form-label text-xs">Search Student</label>
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-input text-sm flex-1" placeholder="Name or student number…">
                <button type="submit" class="btn-primary px-3 py-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        @if(request()->hasAny(['year_id','class_id','status','search']))
        <a href="{{ route('enrolments.index') }}" class="btn-secondary text-sm self-end">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    @if($enrolments->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-500 text-sm">No enrolments found for the selected filters.</p>
        @can('edit students')
        <a href="{{ route('enrolments.create') }}" class="btn-primary mt-4 inline-flex">Enrol First Student</a>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student #</th>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Roll #</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrolments as $enrolment)
                <tr>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <img src="{{ $enrolment->student->photo_url }}"
                                class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $enrolment->student->full_name }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="text-xs font-mono text-gray-500">
                        {{ $enrolment->student->student_number ?? '—' }}
                    </td>
                    <td class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $enrolment->schoolClass->full_name ?? '—' }}
                    </td>
                    <td class="text-sm text-gray-500">
                        {{ $enrolment->term->name ?? '—' }}
                    </td>
                    <td class="text-sm font-mono text-gray-500">
                        {{ $enrolment->roll_number ?? '—' }}
                    </td>
                    <td class="text-sm text-gray-500">
                        {{ $enrolment->enrolled_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $enrolment->status_color }}">{{ $enrolment->status_label }}</span>
                        @if($enrolment->is_promoted)
                        <span class="badge badge-info ml-1 text-xs">Promoted</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('enrolments.show', $enrolment) }}"
                                class="icon-btn" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @can('edit students')
                            <a href="{{ route('enrolments.edit', $enrolment) }}"
                                class="icon-btn" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($enrolments->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $enrolments->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

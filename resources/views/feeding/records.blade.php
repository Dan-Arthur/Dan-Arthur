@extends('layouts.app')

@section('title', 'Feeding Records')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Daily Feeding Records</h1>
        <p class="text-sm text-muted mt-1">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('feeding.index') }}" class="btn btn-ghost">Dashboard</a>
        <a href="{{ route('feeding.enrollment') }}" class="btn btn-ghost">Enrollment</a>
    </div>
</div>

{{-- Instruction banner --}}
@can('record feeding')
<div class="card mb-4" style="border-left: 4px solid var(--color-primary);">
    <p class="text-sm font-medium">How to record feeding</p>
    <p class="text-sm text-muted mt-1">
        1. Select the date below &nbsp;·&nbsp;
        2. Tick every student who received food &nbsp;·&nbsp;
        3. Click <strong>Save Records</strong>
    </p>
    <p class="text-sm text-muted mt-1">
        Student not listed? <a href="{{ route('feeding.enrollment') }}" class="link">Enroll them first</a> on the Enrollment page.
    </p>
</div>
@endcan

{{-- Date picker --}}
<form method="GET" class="card mb-4">
    <div class="flex flex-wrap items-end gap-4">
        <div class="form-group mb-0">
            <label class="form-label">Select Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-input"
                   max="{{ today()->toDateString() }}" onchange="this.form.submit()">
        </div>
        <div>
            @if ($savedForDate)
                <span class="badge badge-success">Already saved — edit &amp; re-save to update</span>
            @else
                <span class="badge badge-warning">Not yet recorded for this date</span>
            @endif
        </div>
    </div>
</form>

@if ($byClass->isEmpty())
    <div class="card">
        <p class="font-medium mb-1">No enrolled students</p>
        <p class="text-muted text-sm">You need to enroll students in the feeding program before you can record meals.</p>
        @can('manage feeding')
        <a href="{{ route('feeding.enrollment') }}" class="btn btn-primary mt-3">Go to Enrollment</a>
        @endcan
    </div>
@else
@can('record feeding')

@php $totalStudents = collect($byClass)->flatten()->count(); @endphp

<form method="POST" action="{{ route('feeding.records.save') }}" x-data="{
    checkedCount: {{ $savedForDate ? collect($byClass)->flatten()->filter(fn($e) => ($records[$e->student_id] ?? false))->count() : $totalStudents }},
    checkAll(val) {
        document.querySelectorAll('input[name=\'fed[]\']').forEach(cb => cb.checked = val);
        this.checkedCount = val ? {{ $totalStudents }} : 0;
    },
    updateCount() {
        this.checkedCount = document.querySelectorAll('input[name=\'fed[]\']:checked').length;
    }
}">
    @csrf
    <input type="hidden" name="record_date" value="{{ $date }}">

    {{-- Action bar --}}
    <div class="card mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">Students Fed</p>
                    <p class="text-2xl font-bold">
                        <span class="text-success" x-text="checkedCount"></span>
                        <span class="text-muted font-normal text-base"> / {{ $totalStudents }}</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="checkAll(true)" class="btn btn-xs btn-ghost">Check All</button>
                    <button type="button" @click="checkAll(false)" class="btn btn-xs btn-ghost">Uncheck All</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                {{ $savedForDate ? 'Update Records' : 'Save Records' }}
            </button>
        </div>
    </div>

    {{-- Student list by class --}}
    @foreach ($byClass as $className => $classEnrollments)
    <div class="card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">{{ $className }}</h3>
            <span class="text-sm text-muted">{{ $classEnrollments->count() }} {{ Str::plural('student', $classEnrollments->count()) }}</span>
        </div>

        <div class="space-y-1">
            @foreach ($classEnrollments as $enrollment)
            @php
                $isFed = $savedForDate
                    ? ($records[$enrollment->student_id] ?? false)
                    : true;
            @endphp
            <label class="flex items-center gap-3 p-3 rounded-lg border border-transparent hover:border-border hover:bg-hover cursor-pointer transition-colors"
                   x-bind:class="{}">
                <input type="checkbox"
                       name="fed[]"
                       value="{{ $enrollment->student_id }}"
                       class="w-4 h-4 rounded border-border"
                       {{ $isFed ? 'checked' : '' }}
                       @change="updateCount()">
                <div class="flex-1">
                    <p class="text-sm font-medium">{{ $enrollment->student->full_name }}</p>
                    @if ($enrollment->student->admission_number)
                    <p class="text-xs text-muted font-mono">{{ $enrollment->student->admission_number }}</p>
                    @endif
                </div>
                <span x-show="false"></span>{{-- keeps Alpine happy --}}
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex justify-end mt-2 mb-6">
        <button type="submit" class="btn btn-primary">
            {{ $savedForDate ? 'Update Records' : 'Save Records' }}
        </button>
    </div>
</form>

@else
{{-- Read-only --}}
@foreach ($byClass as $className => $classEnrollments)
<div class="card mb-4">
    <h3 class="font-semibold mb-3">{{ $className }}</h3>
    <div class="space-y-1">
        @foreach ($classEnrollments as $enrollment)
        @php $isFed = $records[$enrollment->student_id] ?? null; @endphp
        <div class="flex items-center gap-3 p-2 rounded">
            @if (!$savedForDate)
                <span class="text-muted text-xs w-4">—</span>
            @elseif ($isFed)
                <span class="text-success font-bold w-4">✓</span>
            @else
                <span class="text-danger font-bold w-4">✗</span>
            @endif
            <span class="text-sm">{{ $enrollment->student->full_name }}</span>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endcan
@endif
@endsection

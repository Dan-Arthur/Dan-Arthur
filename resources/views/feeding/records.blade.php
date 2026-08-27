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

{{-- Date picker --}}
<form method="GET" class="card mb-4">
    <div class="flex items-end gap-3">
        <div class="form-group mb-0">
            <label class="form-label">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-input"
                   max="{{ today()->toDateString() }}" onchange="this.form.submit()">
        </div>
        @if ($savedForDate)
            <span class="badge badge-success">Saved</span>
        @else
            <span class="badge badge-warning">Not Saved</span>
        @endif
    </div>
</form>

@if ($byClass->isEmpty())
    <div class="card">
        <p class="text-muted text-sm">No active enrolled students. <a href="{{ route('feeding.enrollment') }}" class="link">Manage enrollment</a> first.</p>
    </div>
@else
@can('record feeding')
<form method="POST" action="{{ route('feeding.records.save') }}" x-data="{
    checkAll(val) {
        document.querySelectorAll('input[name=\'fed[]\']').forEach(cb => cb.checked = val);
        this.updateCount();
    },
    updateCount() {
        this.checkedCount = document.querySelectorAll('input[name=\'fed[]\']:checked').length;
    },
    checkedCount: {{ $savedForDate ? collect($byClass)->flatten()->filter(fn($e) => ($records[$e->student_id] ?? false))->count() : collect($byClass)->flatten()->count() }}
}">
    @csrf
    <input type="hidden" name="record_date" value="{{ $date }}">

    {{-- Summary bar --}}
    <div class="card mb-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <div>
                <span class="text-2xl font-bold text-success" x-text="checkedCount"></span>
                <span class="text-sm text-muted ml-1">/ {{ collect($byClass)->flatten()->count() }} fed</span>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="checkAll(true)" class="btn btn-xs btn-ghost">Check All</button>
                <button type="button" @click="checkAll(false)" class="btn btn-xs btn-ghost">Uncheck All</button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Records</button>
    </div>

    {{-- Students by class --}}
    @foreach ($byClass as $className => $classEnrollments)
    <div class="card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">{{ $className }}</h3>
            <span class="text-sm text-muted">{{ $classEnrollments->count() }} students</span>
        </div>
        <div class="space-y-1">
            @foreach ($classEnrollments as $enrollment)
            @php
                $isFed = $savedForDate
                    ? ($records[$enrollment->student_id] ?? false)
                    : true; // default all checked for new records
            @endphp
            <label class="flex items-center gap-3 p-2 rounded hover:bg-hover cursor-pointer"
                   :class="{ 'opacity-50': false }">
                <input type="checkbox" name="fed[]" value="{{ $enrollment->student_id }}"
                       class="rounded border-border"
                       {{ $isFed ? 'checked' : '' }}
                       @change="updateCount()">
                <span class="flex-1 text-sm font-medium">{{ $enrollment->student->full_name }}</span>
                <span class="text-xs text-muted font-mono">{{ $enrollment->student->admission_number ?? '' }}</span>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex justify-end mt-4">
        <button type="submit" class="btn btn-primary">Save Records</button>
    </div>
</form>
@else
{{-- Read-only view for users without record permission --}}
@foreach ($byClass as $className => $classEnrollments)
<div class="card mb-4">
    <h3 class="font-semibold mb-3">{{ $className }}</h3>
    <div class="space-y-1">
        @foreach ($classEnrollments as $enrollment)
        @php $isFed = $records[$enrollment->student_id] ?? null; @endphp
        <div class="flex items-center gap-3 p-2 rounded">
            @if (!$savedForDate)
                <span class="text-muted text-xs">—</span>
            @elseif ($isFed)
                <span class="text-success text-sm">✓</span>
            @else
                <span class="text-danger text-sm">✗</span>
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

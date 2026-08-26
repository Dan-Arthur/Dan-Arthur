@extends('layouts.app')

@section('title', $route->name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $route->name }}</h1>
        <p class="page-subtitle">
            {{ \App\Models\TransportRoute::DIRECTIONS[$route->direction] ?? $route->direction }}
            @if ($route->code) · {{ $route->code }} @endif
        </p>
    </div>
    <a href="{{ route('transport.index') }}" class="btn btn-ghost">Back</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Route info --}}
    <div class="card">
        <h2 class="card-title mb-4">Route Info</h2>
        <dl class="detail-list">
            <dt>Vehicle</dt>
            <dd>
                @if ($route->vehicle)
                    {{ $route->vehicle->registration_number }}
                    <span class="text-muted text-xs">({{ $route->vehicle->make }} {{ $route->vehicle->model }})</span>
                @else —
                @endif
            </dd>
            <dt>Driver</dt><dd>{{ $route->driver?->name ?? '—' }}</dd>
            <dt>Monthly Fee</dt><dd class="font-mono">{{ $route->monthly_fee ? number_format($route->monthly_fee, 2) : '—' }}</dd>
            <dt>Capacity</dt><dd>{{ $route->capacity ?? '—' }}</dd>
            <dt>Students Assigned</dt><dd>{{ $route->students->count() }}</dd>
        </dl>
    </div>

    {{-- Stops --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="card-title">Stops ({{ $route->stops->count() }})</h2>
            @can('manage transport')
            <button class="btn btn-sm btn-ghost" x-data @click="$dispatch('open-stop-modal')">+ Add Stop</button>
            @endcan
        </div>
        @if ($route->stops->isEmpty())
            <p class="text-muted text-sm">No stops added.</p>
        @else
            <ol class="space-y-2">
                @foreach ($route->stops->sortBy('sequence') as $stop)
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center">
                        {{ $stop->sequence }}
                    </span>
                    <div>
                        <p class="font-medium text-sm">{{ $stop->name }}</p>
                        @if ($stop->address)
                            <p class="text-xs text-muted">{{ $stop->address }}</p>
                        @endif
                        @if ($stop->pickup_time)
                            <p class="text-xs text-muted">{{ $stop->pickup_time }}</p>
                        @endif
                    </div>
                </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- Assign student --}}
    @can('manage transport')
    <div class="card">
        <h2 class="card-title mb-4">Assign Student</h2>
        <form method="POST" action="{{ route('transport.assign', $route) }}" class="space-y-3">
            @csrf
            <div class="form-group">
                <label class="form-label">Student <span class="required">*</span></label>
                <select name="student_id" class="form-select" required>
                    <option value="">— Select —</option>
                    @foreach ($availableStudents as $s)
                        <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->admission_number }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Pick-up Stop</label>
                <select name="stop_id" class="form-select">
                    <option value="">— Select Stop —</option>
                    @foreach ($route->stops->sortBy('sequence') as $stop)
                        <option value="{{ $stop->id }}">{{ $stop->sequence }}. {{ $stop->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full">Assign</button>
        </form>
    </div>
    @endcan
</div>

{{-- Assigned students --}}
<div class="card mt-6">
    <h2 class="card-title mb-4">Assigned Students ({{ $route->students->count() }})</h2>
    @if ($route->students->isEmpty())
        <p class="text-muted text-sm">No students assigned yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission #</th>
                        <th>Class</th>
                        <th>Pick-up Stop</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($route->students as $assignment)
                    <tr>
                        <td class="font-medium">{{ $assignment->student->full_name }}</td>
                        <td class="font-mono">{{ $assignment->student->admission_number }}</td>
                        <td>{{ $assignment->student->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $assignment->stop?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Add Stop Modal --}}
@can('manage transport')
<div x-data="{ open: false }" x-on:open-stop-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">Add Stop</h2>
            <form method="POST" action="{{ route('transport.stops.store', $route) }}" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label class="form-label">Stop Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address / Landmark</label>
                    <input type="text" name="address" class="form-input">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Pick-up Time</label>
                        <input type="time" name="pickup_time" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Drop-off Time</label>
                        <input type="time" name="dropoff_time" class="form-input">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Add Stop</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@extends('layouts.app')

@section('title', 'Vehicles & Drivers')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Vehicles & Drivers</h1></div>
    <div class="flex gap-3">
        @can('manage transport')
        <button class="btn btn-ghost" x-data @click="$dispatch('open-driver-modal')">Add Driver</button>
        <button class="btn btn-primary" x-data @click="$dispatch('open-vehicle-modal')">Add Vehicle</button>
        @endcan
        <a href="{{ route('transport.index') }}" class="btn btn-ghost">Routes</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Vehicles --}}
    <div class="card">
        <h2 class="card-title mb-4">Vehicles ({{ $vehicles->count() }})</h2>
        @if ($vehicles->isEmpty())
            <p class="text-muted text-sm">No vehicles registered.</p>
        @else
            <div class="space-y-3">
                @foreach ($vehicles as $vehicle)
                <div class="flex items-center justify-between p-3 rounded-lg border border-border">
                    <div>
                        <p class="font-medium">{{ $vehicle->registration_number }}</p>
                        <p class="text-sm text-muted">{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->year }}</p>
                        <div class="flex gap-2 mt-1">
                            <span class="badge {{ \App\Models\Vehicle::STATUSES[$vehicle->status]['color'] ?? 'badge-gray' }} text-xs">
                                {{ \App\Models\Vehicle::STATUSES[$vehicle->status]['label'] ?? $vehicle->status }}
                            </span>
                            @if ($vehicle->insurance_expired)
                                <span class="badge badge-danger text-xs">Insurance Expired</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right text-sm">
                        <p class="text-muted">Capacity</p>
                        <p class="font-mono font-semibold">{{ $vehicle->capacity ?? '—' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Drivers --}}
    <div class="card">
        <h2 class="card-title mb-4">Drivers ({{ $drivers->count() }})</h2>
        @if ($drivers->isEmpty())
            <p class="text-muted text-sm">No drivers registered.</p>
        @else
            <div class="space-y-3">
                @foreach ($drivers as $driver)
                <div class="flex items-center justify-between p-3 rounded-lg border border-border">
                    <div>
                        <p class="font-medium">{{ $driver->name }}</p>
                        <p class="text-sm text-muted font-mono">{{ $driver->licence_number }}</p>
                        <div class="flex gap-2 mt-1">
                            @if ($driver->licence_expired)
                                <span class="badge badge-danger text-xs">Licence Expired</span>
                            @else
                                <span class="badge badge-success text-xs">Licence Valid</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right text-sm text-muted">
                        <p>Exp: {{ $driver->licence_expiry ? $driver->licence_expiry->format('d M Y') : '—' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Add Vehicle Modal --}}
@can('manage transport')
<div x-data="{ open: false }" x-on:open-vehicle-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">Register Vehicle</h2>
            <form method="POST" action="{{ route('transport.vehicles.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 form-group">
                        <label class="form-label">Registration Number <span class="required">*</span></label>
                        <input type="text" name="registration_number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach (\App\Models\Vehicle::TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-input" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-input" placeholder="e.g. Toyota">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-input" placeholder="e.g. Coaster">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-input" min="1990" max="{{ date('Y') + 1 }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Insurance Expiry</label>
                        <input type="date" name="insurance_expiry" class="form-input">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Driver Modal --}}
<div x-data="{ open: false }" x-on:open-driver-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">Register Driver</h2>
            <form method="POST" action="{{ route('transport.drivers.store') }}" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label class="form-label">Employee (Staff) <span class="required">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">— Select Employee —</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Licence Number <span class="required">*</span></label>
                        <input type="text" name="licence_number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Licence Expiry</label>
                        <input type="date" name="licence_expiry" class="form-input">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Add Driver</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

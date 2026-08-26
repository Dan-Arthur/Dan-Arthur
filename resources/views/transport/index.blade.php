@extends('layouts.app')

@section('title', 'Transport')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Transport Routes</h1></div>
    <div class="flex gap-3">
        @can('manage transport')
        <a href="{{ route('transport.vehicles') }}" class="btn btn-ghost">Vehicles & Drivers</a>
        @endcan
        @can('create transport routes')
        <button class="btn btn-primary" x-data @click="$dispatch('open-route-modal')">Add Route</button>
        @endcan
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Search routes…">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('transport.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($routes->isEmpty())
    <div class="empty-state">No routes found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Code</th>
                    <th>Direction</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Stops</th>
                    <th>Students</th>
                    <th>Monthly Fee</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($routes as $route)
                <tr>
                    <td class="font-medium">{{ $route->name }}</td>
                    <td class="font-mono text-sm">{{ $route->code ?? '—' }}</td>
                    <td>{{ \App\Models\TransportRoute::DIRECTIONS[$route->direction] ?? $route->direction }}</td>
                    <td>{{ $route->vehicle?->registration_number ?? '—' }}</td>
                    <td>{{ $route->driver?->name ?? '—' }}</td>
                    <td class="text-center">{{ $route->stops_count }}</td>
                    <td class="text-center">{{ $route->students_count }}</td>
                    <td class="font-mono">{{ $route->monthly_fee ? number_format($route->monthly_fee, 2) : '—' }}</td>
                    <td class="table-actions">
                        <a href="{{ route('transport.show', $route) }}" class="action-link">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($routes->count() > 15)<div class="mt-4 text-sm text-gray-500">Showing all {{ $routes->count() }} routes</div>@endif
@endif

{{-- Add Route Modal --}}
@can('create transport routes')
<div x-data="{ open: false }" x-on:open-route-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">New Route</h2>
            <form method="POST" action="{{ route('transport.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 form-group">
                        <label class="form-label">Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Direction <span class="required">*</span></label>
                        <select name="direction" class="form-select" required>
                            @foreach (\App\Models\TransportRoute::DIRECTIONS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->registration_number }} ({{ $v->make }} {{ $v->model }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Fee</label>
                        <input type="number" name="monthly_fee" class="form-input" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-input" min="1">
                    </div>
                </div>
                <div class="flex gap-3 mt-2">
                    <button type="submit" class="btn btn-primary">Create Route</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

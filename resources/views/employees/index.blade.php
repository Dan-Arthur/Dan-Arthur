@extends('layouts.app')

@section('title', 'Staff')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Staff</h1>
        <p class="page-subtitle">All employees and positions</p>
    </div>
    <div class="flex gap-3">
        @can('view positions')
        <a href="{{ route('employees.positions') }}" class="btn btn-ghost">Positions</a>
        @endcan
        @can('create staff')
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Employee
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" class="form-input flex-1" placeholder="Name, employee number, email…"
           value="{{ request('search') }}">
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach ($statuses as $key => $meta)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <select name="department_id" class="form-select w-auto">
        <option value="">All Departments</option>
        @foreach ($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </select>
    <select name="position_type" class="form-select w-auto">
        <option value="">All Types</option>
        @foreach ($positionTypes as $key => $label)
            <option value="{{ $key }}" {{ request('position_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($employees->isEmpty())
    <div class="empty-state">No employees found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee #</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $emp)
                <tr>
                    <td class="font-mono text-sm">{{ $emp->employee_number }}</td>
                    <td>
                        <a href="{{ route('employees.show', $emp) }}" class="font-medium hover:underline">
                            {{ $emp->full_name }}
                        </a>
                        @if ($emp->email)
                            <div class="text-xs text-muted">{{ $emp->email }}</div>
                        @endif
                    </td>
                    <td>{{ $emp->position?->title ?? '—' }}</td>
                    <td>{{ $emp->department?->name ?? '—' }}</td>
                    <td>{{ \App\Models\Employee::EMPLOYMENT_TYPES[$emp->employment_type] ?? $emp->employment_type }}</td>
                    <td>{{ $emp->phone ?? '—' }}</td>
                    <td>{{ $emp->joining_date?->format('M Y') ?? '—' }}</td>
                    <td><span class="badge {{ $emp->status_color }}">{{ $emp->status_label }}</span></td>
                    <td class="table-actions">
                        <a href="{{ route('employees.show', $emp) }}" class="action-link">View</a>
                        @can('edit staff')
                        <a href="{{ route('employees.edit', $emp) }}" class="action-link">Edit</a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
@endif
@endsection

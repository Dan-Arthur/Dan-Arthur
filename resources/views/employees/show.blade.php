@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $employee->full_name }}</h1>
        <p class="page-subtitle">{{ $employee->position?->title ?? 'Employee' }} &bull; {{ $employee->employee_number }}</p>
    </div>
    <div class="flex gap-3">
        @can('edit staff')
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-ghost">Edit</a>
        @endcan
        @can('manage leave')
        <a href="{{ route('leave.create', ['employee_id' => $employee->id]) }}" class="btn btn-primary">Request Leave</a>
        @endcan
        <a href="{{ route('employees.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left sidebar --}}
    <div class="space-y-4">
        <div class="card text-center py-6">
            <div class="w-20 h-20 rounded-full bg-primary/10 text-primary text-2xl font-bold flex items-center justify-center mx-auto mb-3">
                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
            </div>
            <h2 class="font-semibold text-lg">{{ $employee->full_name }}</h2>
            <p class="text-muted text-sm">{{ $employee->position?->title ?? '—' }}</p>
            <div class="mt-2">
                <span class="badge {{ $employee->status_color }}">{{ $employee->status_label }}</span>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold mb-3">Quick Stats</h3>
            <div class="space-y-2 text-sm">
                @if ($employee->years_of_service !== null)
                <div class="flex justify-between">
                    <span class="text-muted">Years of Service</span>
                    <span class="font-medium">{{ $employee->years_of_service }} yrs</span>
                </div>
                @endif
                @if ($employee->age)
                <div class="flex justify-between">
                    <span class="text-muted">Age</span>
                    <span class="font-medium">{{ $employee->age }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-muted">Leave Requests</span>
                    <span class="font-medium">{{ $employee->leaveRequests->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h2 class="card-title mb-4">Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                <dl class="detail-list">
                    <dt>Employee No.</dt>
                    <dd class="font-mono">{{ $employee->employee_number }}</dd>
                    <dt>Department</dt>
                    <dd>{{ $employee->department?->name ?? '—' }}</dd>
                    <dt>Campus</dt>
                    <dd>{{ $employee->campus?->name ?? '—' }}</dd>
                    <dt>Employment Type</dt>
                    <dd>{{ \App\Models\Employee::EMPLOYMENT_TYPES[$employee->employment_type] ?? $employee->employment_type }}</dd>
                    <dt>Joining Date</dt>
                    <dd>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</dd>
                    @if ($employee->exit_date)
                    <dt>Exit Date</dt>
                    <dd>{{ $employee->exit_date->format('d M Y') }}</dd>
                    @endif
                </dl>
                <dl class="detail-list">
                    <dt>Phone</dt>
                    <dd>{{ $employee->phone ?? '—' }}</dd>
                    <dt>Email</dt>
                    <dd>{{ $employee->email ?? '—' }}</dd>
                    <dt>Qualification</dt>
                    <dd>{{ $employee->qualification ?? '—' }}</dd>
                    <dt>Specialisation</dt>
                    <dd>{{ $employee->specialisation ?? '—' }}</dd>
                    <dt>Experience</dt>
                    <dd>{{ $employee->years_experience !== null ? $employee->years_experience . ' yrs' : '—' }}</dd>
                    <dt>System User</dt>
                    <dd>{{ $employee->user?->name ?? '—' }}</dd>
                </dl>
            </div>
            @if ($employee->address)
            <div class="mt-4 pt-4 border-t border-border">
                <span class="text-muted text-sm">Address:</span>
                <p class="text-sm mt-1">{{ $employee->address }}</p>
            </div>
            @endif
        </div>

        {{-- Recent leave requests --}}
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="card-title">Recent Leave Requests</h2>
                @can('view leave')
                <a href="{{ route('leave.index', ['search' => $employee->employee_number]) }}" class="text-sm text-primary hover:underline">View All</a>
                @endcan
            </div>
            @if ($employee->leaveRequests->isEmpty())
                <p class="text-muted text-sm">No leave requests on record.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employee->leaveRequests as $req)
                            <tr>
                                <td>{{ $req->leaveType->name }}</td>
                                <td>{{ $req->start_date->format('d M Y') }} – {{ $req->end_date->format('d M Y') }}</td>
                                <td>{{ $req->days_requested }}</td>
                                <td><span class="badge {{ $req->status_color }}">{{ $req->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Leave Requests</h1>
        <p class="page-subtitle">Staff leave applications</p>
    </div>
    <div class="flex gap-3">
        @can('manage leave')
        <a href="{{ route('leave.types') }}" class="btn btn-ghost">Leave Types</a>
        <a href="{{ route('leave.create') }}" class="btn btn-primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Request
        </a>
        @endcan
    </div>
</div>

{{-- Summary strip --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach (\App\Models\LeaveRequest::STATUSES as $key => $meta)
    <div class="card text-center py-4">
        <p class="text-2xl font-bold">{{ $summary[$key] ?? 0 }}</p>
        <p class="text-muted text-sm mt-1">{{ $meta['label'] }}</p>
    </div>
    @endforeach
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" class="form-input flex-1" placeholder="Employee name or number…"
           value="{{ request('search') }}">
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach ($statuses as $key => $meta)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <select name="leave_type_id" class="form-select w-auto">
        <option value="">All Types</option>
        @foreach ($leaveTypes as $type)
            <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('leave.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($requests->isEmpty())
    <div class="empty-state">No leave requests found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $req)
                <tr>
                    <td>
                        <a href="{{ route('employees.show', $req->employee) }}" class="font-medium hover:underline">
                            {{ $req->employee->full_name }}
                        </a>
                        <div class="text-xs text-muted">{{ $req->employee->employee_number }}</div>
                    </td>
                    <td>
                        {{ $req->leaveType->name }}
                        @if (!$req->leaveType->is_paid)
                            <span class="badge badge-gray ml-1 text-xs">Unpaid</span>
                        @endif
                    </td>
                    <td>{{ $req->start_date->format('d M Y') }}</td>
                    <td>{{ $req->end_date->format('d M Y') }}</td>
                    <td>{{ $req->days_requested }}</td>
                    <td><span class="badge {{ $req->status_color }}">{{ $req->status_label }}</span></td>
                    <td>{{ $req->created_at->format('d M Y') }}</td>
                    <td class="table-actions">
                        <a href="{{ route('leave.show', $req) }}" class="action-link">View</a>
                        @can('approve leave')
                        @if ($req->status === 'pending')
                        <form method="POST" action="{{ route('leave.approve', $req) }}" class="inline">
                            @csrf @method('PATCH')
                            <button class="action-link text-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('leave.reject', $req) }}" class="inline">
                            @csrf @method('PATCH')
                            <button class="action-link text-danger">Reject</button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
@endif
@endsection

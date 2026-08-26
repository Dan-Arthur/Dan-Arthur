@extends('layouts.app')

@section('title', 'Leave Request')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Leave Request</h1>
        <p class="page-subtitle">{{ $leave->employee->full_name }} &bull; {{ $leave->leaveType->name }}</p>
    </div>
    <div class="flex gap-3">
        @can('approve leave')
        @if ($leave->status === 'pending')
        <form method="POST" action="{{ route('leave.approve', $leave) }}" x-data="{ note: '' }">
            @csrf @method('PATCH')
            <input type="hidden" name="action_note" x-model="note">
            <div class="flex gap-2 items-center">
                <input type="text" class="form-input w-48" placeholder="Optional note…" x-model="note">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Approve this leave request?')">Approve</button>
            </div>
        </form>
        <form method="POST" action="{{ route('leave.reject', $leave) }}" x-data="{ note: '' }">
            @csrf @method('PATCH')
            <input type="hidden" name="action_note" x-model="note">
            <div class="flex gap-2 items-center">
                <button type="submit" class="btn btn-ghost text-danger" onclick="return confirm('Reject this leave request?')">Reject</button>
            </div>
        </form>
        @endif
        @endcan
        @can('manage leave')
        @if (in_array($leave->status, ['pending', 'approved']))
        <form method="POST" action="{{ route('leave.cancel', $leave) }}" onsubmit="return confirm('Cancel this request?')">
            @csrf @method('PATCH')
            <button class="btn btn-ghost text-danger">Cancel</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('leave.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="max-w-2xl space-y-6">
    <div class="card">
        <div class="flex justify-between items-start mb-6">
            <h2 class="card-title">Request Details</h2>
            <span class="badge text-sm {{ $leave->status_color }}">{{ $leave->status_label }}</span>
        </div>

        <dl class="detail-list">
            <dt>Employee</dt>
            <dd>
                <a href="{{ route('employees.show', $leave->employee) }}" class="text-primary hover:underline font-medium">
                    {{ $leave->employee->full_name }}
                </a>
                <span class="text-muted text-sm">({{ $leave->employee->employee_number }})</span>
                @if ($leave->employee->position)
                    <div class="text-xs text-muted">{{ $leave->employee->position->title }}</div>
                @endif
            </dd>

            <dt>Leave Type</dt>
            <dd>
                {{ $leave->leaveType->name }}
                <span class="badge {{ $leave->leaveType->is_paid ? 'badge-success' : 'badge-gray' }} ml-2">
                    {{ $leave->leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                </span>
            </dd>

            <dt>Start Date</dt>
            <dd>{{ $leave->start_date->format('l, d F Y') }}</dd>

            <dt>End Date</dt>
            <dd>{{ $leave->end_date->format('l, d F Y') }}</dd>

            <dt>Days Requested</dt>
            <dd class="font-semibold text-lg">{{ $leave->days_requested }} working day(s)</dd>

            @if ($leave->reason)
            <dt>Reason</dt>
            <dd>{{ $leave->reason }}</dd>
            @endif

            <dt>Submitted</dt>
            <dd>{{ $leave->created_at->format('d M Y H:i') }}</dd>

            @if ($leave->actioned_at)
            <dt>Actioned</dt>
            <dd>{{ $leave->actioned_at->format('d M Y H:i') }} by {{ $leave->approvedBy?->name ?? '—' }}</dd>

            @if ($leave->action_note)
            <dt>Note</dt>
            <dd>{{ $leave->action_note }}</dd>
            @endif
            @endif
        </dl>
    </div>
</div>
@endsection

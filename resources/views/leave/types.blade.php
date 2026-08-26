@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Leave Types</h1>
        <p class="page-subtitle">Define leave categories and entitlements</p>
    </div>
    <a href="{{ route('leave.index') }}" class="btn btn-ghost">Back to Requests</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card">
        <h2 class="card-title mb-4">Add Leave Type</h2>
        <form method="POST" action="{{ route('leave.types.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}"
                       placeholder="e.g. Annual Leave" required>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Days Allowed per Year</label>
                <input type="number" name="days_allowed" class="form-input" value="{{ old('days_allowed', 0) }}" min="0">
            </div>
            <div class="form-group flex items-center gap-2">
                <input type="checkbox" name="is_paid" id="is_paid" value="1"
                       {{ old('is_paid', '1') ? 'checked' : '' }} class="form-checkbox">
                <label for="is_paid" class="form-label mb-0">Paid Leave</label>
            </div>
            <div class="form-group flex items-center gap-2">
                <input type="checkbox" name="requires_approval" id="req_approval" value="1"
                       {{ old('requires_approval', '1') ? 'checked' : '' }} class="form-checkbox">
                <label for="req_approval" class="form-label mb-0">Requires Approval</label>
            </div>
            <button type="submit" class="btn btn-primary w-full">Add Type</button>
        </form>
    </div>

    <div class="lg:col-span-2 card">
        <h2 class="card-title mb-4">Leave Types ({{ $leaveTypes->count() }})</h2>
        @if ($leaveTypes->isEmpty())
            <p class="text-muted">No leave types defined.</p>
        @else
            <div class="space-y-2">
                @foreach ($leaveTypes as $type)
                <div x-data="{ editing: false }" class="border border-border rounded-lg px-4 py-3">
                    <div class="flex items-start justify-between" x-show="!editing">
                        <div>
                            <span class="font-medium">{{ $type->name }}</span>
                            <div class="flex gap-2 mt-1">
                                <span class="badge {{ $type->is_paid ? 'badge-success' : 'badge-gray' }}">
                                    {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                </span>
                                <span class="badge badge-gray">{{ $type->days_allowed }} days</span>
                                @if ($type->requires_approval)
                                    <span class="badge badge-warning">Approval required</span>
                                @endif
                            </div>
                            <div class="text-xs text-muted mt-1">{{ $type->requests_count }} request(s) total</div>
                        </div>
                        <div class="flex gap-3 shrink-0">
                            <button @click="editing = true" class="action-link">Edit</button>
                            @if ($type->requests_count === 0)
                            <form method="POST" action="{{ route('leave.types.destroy', $type) }}"
                                  onsubmit="return confirm('Delete this leave type?')">
                                @csrf @method('DELETE')
                                <button class="action-link text-danger">Delete</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <form x-show="editing" x-cloak method="POST" action="{{ route('leave.types.update', $type) }}"
                          class="space-y-3">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="text" name="name" class="form-input" value="{{ $type->name }}" required>
                            </div>
                            <div>
                                <input type="number" name="days_allowed" class="form-input"
                                       value="{{ $type->days_allowed }}" min="0" placeholder="Days">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_paid" value="1"
                                       {{ $type->is_paid ? 'checked' : '' }} class="form-checkbox">
                                Paid
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="requires_approval" value="1"
                                       {{ $type->requires_approval ? 'checked' : '' }} class="form-checkbox">
                                Requires Approval
                            </label>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" @click="editing = false" class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

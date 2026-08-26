@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Audit Log</h1>
        <p class="page-subtitle">Activity trail for this school</p>
    </div>
</div>

{{-- Summary chips --}}
@if ($summary->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-6">
    @foreach (\App\Models\AuditLog::EVENTS as $key => $meta)
        @if ($summary->has($key))
        <a href="{{ route('audit.index', array_merge(request()->query(), ['event' => $key])) }}"
           class="badge {{ $meta['color'] }} text-sm cursor-pointer hover:opacity-80">
            {{ $meta['label'] }}: {{ number_format($summary[$key]) }}
        </a>
        @endif
    @endforeach
</div>
@endif

{{-- Filters --}}
<form method="GET" class="filter-bar mb-6 flex-wrap">
    <select name="event" class="form-select w-auto">
        <option value="">All Events</option>
        @foreach ($events as $k => $meta)
            <option value="{{ $k }}" {{ request('event') === $k ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <select name="user_id" class="form-select w-auto">
        <option value="">All Users</option>
        @foreach ($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
    </select>
    <select name="model" class="form-select w-auto">
        <option value="">All Models</option>
        @foreach ($modelTypes as $mt)
            <option value="{{ $mt }}" {{ request('model') === $mt ? 'selected' : '' }}>{{ $mt }}</option>
        @endforeach
    </select>
    <input type="text" name="tags" value="{{ request('tags') }}" class="form-input w-32" placeholder="Tag…">
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-auto">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-auto">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('audit.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($logs->isEmpty())
    <div class="empty-state">No audit log entries found.</div>
@else
    <div class="table-card">
        <table class="data-table text-sm">
            <thead>
                <tr>
                    <th>When</th>
                    <th>User</th>
                    <th>Event</th>
                    <th>Model</th>
                    <th>Record</th>
                    <th>IP</th>
                    <th>Tags</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                <tr>
                    <td class="text-muted whitespace-nowrap">
                        {{ $log->created_at->format('d M Y') }}<br>
                        <span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if ($log->user)
                            <span class="font-medium">{{ $log->user->name }}</span><br>
                            <span class="text-xs text-muted">{{ $log->user_type }}</span>
                        @else
                            <span class="text-muted italic text-xs">System</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $log->event_color }} text-xs">{{ $log->event_label }}</span>
                    </td>
                    <td>{{ $log->model_label }}</td>
                    <td class="font-mono text-xs text-muted">
                        {{ $log->auditable_id ?? '—' }}
                    </td>
                    <td class="font-mono text-xs text-muted">{{ $log->ip_address ?? '—' }}</td>
                    <td>
                        @if ($log->tags)
                            <span class="badge badge-gray text-xs">{{ $log->tags }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('audit.show', $log) }}" class="action-link">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between">
        <p class="text-sm text-muted">{{ $logs->total() }} entries total</p>
        {{ $logs->links() }}
    </div>
@endif
@endsection

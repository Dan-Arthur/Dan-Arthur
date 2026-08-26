@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Events</h1></div>
    @can('manage events')
    <a href="{{ route('events.create') }}" class="btn btn-primary">New Event</a>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    {{-- Upcoming sidebar --}}
    @if ($upcoming->isNotEmpty())
    <div class="card lg:row-span-2">
        <h2 class="card-title mb-3">Upcoming</h2>
        <div class="space-y-3">
            @foreach ($upcoming as $ev)
            <a href="{{ route('events.show', $ev) }}" class="block group">
                <div class="flex items-start gap-3">
                    <div class="w-1 flex-shrink-0 rounded-full h-full min-h-8" style="background: {{ $ev->color }}"></div>
                    <div>
                        <p class="font-medium text-sm group-hover:text-primary">{{ $ev->title }}</p>
                        <p class="text-xs text-muted">{{ $ev->start_datetime->format('d M') }}
                            @if (!$ev->all_day) · {{ $ev->start_datetime->format('H:i') }} @endif
                        </p>
                        @if ($ev->location)
                            <p class="text-xs text-muted">{{ $ev->location }}</p>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Main list --}}
    <div class="{{ $upcoming->isNotEmpty() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
        <form method="GET" class="filter-bar mb-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-input w-40" placeholder="Search…">
            <input type="month" name="month" value="{{ request('month') }}" class="form-input w-auto">
            <select name="type" class="form-select w-auto">
                <option value="">All Types</option>
                @foreach ($types as $k => $v)
                    <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select w-auto">
                <option value="">All Statuses</option>
                @foreach ($statuses as $k => $meta)
                    <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('events.index') }}" class="btn btn-ghost">Reset</a>
        </form>

        @if ($events->isEmpty())
            <div class="empty-state">No events found.</div>
        @else
            <div class="space-y-3">
                @foreach ($events as $event)
                <div class="card hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="w-1.5 flex-shrink-0 rounded-full self-stretch" style="background: {{ $event->color }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="badge badge-gray text-xs">{{ $types[$event->type] ?? $event->type }}</span>
                                <span class="badge {{ $event->status_color }} text-xs">{{ $event->status_label }}</span>
                            </div>
                            <a href="{{ route('events.show', $event) }}" class="font-semibold text-base hover:text-primary">
                                {{ $event->title }}
                            </a>
                            <p class="text-sm text-muted mt-0.5">
                                {{ $event->duration }}
                                @if ($event->location) · {{ $event->location }} @endif
                            </p>
                            @if ($event->description)
                                <p class="text-sm text-muted mt-1 line-clamp-1">{{ Str::limit($event->description, 100) }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <a href="{{ route('events.show', $event) }}" class="action-link">View</a>
                            @can('manage events')
                            <a href="{{ route('events.edit', $event) }}" class="action-link">Edit</a>
                            @if (!in_array($event->status, ['cancelled', 'completed']))
                            <form method="POST" action="{{ route('events.cancel', $event) }}">
                                @csrf @method('PATCH')
                                <button class="action-link text-danger">Cancel</button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection

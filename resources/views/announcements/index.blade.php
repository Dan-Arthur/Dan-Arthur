@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Announcements</h1></div>
    @can('create announcements')
    <a href="{{ route('announcements.create') }}" class="btn btn-primary">New Announcement</a>
    @endcan
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Search…">
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
    <select name="audience" class="form-select w-auto">
        <option value="">All Audiences</option>
        @foreach ($audiences as $k => $v)
            <option value="{{ $k }}" {{ request('audience') === $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('announcements.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($announcements->isEmpty())
    <div class="empty-state">No announcements found.</div>
@else
    <div class="space-y-4">
        @foreach ($announcements as $ann)
        <div class="card {{ $ann->is_pinned ? 'border-l-4 border-l-warning' : '' }} hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1">
                        @if ($ann->is_pinned)
                            <span class="text-warning text-xs font-bold uppercase tracking-wide">Pinned</span>
                        @endif
                        <span class="badge badge-gray text-xs">{{ $types[$ann->type] ?? $ann->type }}</span>
                        <span class="badge {{ $ann->status_color }} text-xs">{{ $ann->status_label }}</span>
                        <span class="badge badge-gray text-xs">{{ $audiences[$ann->audience] ?? $ann->audience }}</span>
                        @if ($ann->is_expired)
                            <span class="badge badge-danger text-xs">Expired</span>
                        @endif
                    </div>
                    <a href="{{ route('announcements.show', $ann) }}" class="font-semibold text-lg hover:text-primary">
                        {{ $ann->title }}
                    </a>
                    <p class="text-muted text-sm mt-1 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 150) }}</p>
                    <p class="text-xs text-muted mt-2">
                        By {{ $ann->author->name }} · {{ $ann->created_at->format('d M Y, H:i') }}
                        @if ($ann->publish_at && $ann->status === 'draft')
                            · Scheduled: {{ $ann->publish_at->format('d M Y, H:i') }}
                        @endif
                        @if ($ann->expires_at)
                            · Expires: {{ $ann->expires_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <a href="{{ route('announcements.show', $ann) }}" class="action-link">View</a>
                    @can('manage announcements')
                    <a href="{{ route('announcements.edit', $ann) }}" class="action-link">Edit</a>
                    @if ($ann->status === 'draft')
                    <form method="POST" action="{{ route('announcements.publish', $ann) }}">
                        @csrf @method('PATCH')
                        <button class="action-link text-success">Publish</button>
                    </form>
                    @elseif ($ann->status === 'published')
                    <form method="POST" action="{{ route('announcements.archive', $ann) }}">
                        @csrf @method('PATCH')
                        <button class="action-link">Archive</button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $announcements->links() }}</div>
@endif
@endsection

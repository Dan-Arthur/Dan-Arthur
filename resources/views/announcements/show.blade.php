@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $announcement->title }}</h1>
        <div class="flex items-center gap-2 mt-1 flex-wrap">
            @if ($announcement->is_pinned)
                <span class="text-warning text-xs font-bold uppercase tracking-wide">Pinned</span>
            @endif
            <span class="badge badge-gray">{{ \App\Models\Announcement::TYPES[$announcement->type] ?? $announcement->type }}</span>
            <span class="badge {{ $announcement->status_color }}">{{ $announcement->status_label }}</span>
            <span class="badge badge-gray">{{ \App\Models\Announcement::AUDIENCES[$announcement->audience] ?? $announcement->audience }}</span>
            @if ($announcement->is_expired)
                <span class="badge badge-danger">Expired</span>
            @endif
        </div>
    </div>
    <div class="flex gap-3">
        @can('manage announcements')
        <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-ghost">Edit</a>
        @if ($announcement->status === 'draft')
        <form method="POST" action="{{ route('announcements.publish', $announcement) }}">
            @csrf @method('PATCH')
            <button class="btn btn-primary">Publish</button>
        </form>
        @elseif ($announcement->status === 'published')
        <form method="POST" action="{{ route('announcements.archive', $announcement) }}">
            @csrf @method('PATCH')
            <button class="btn btn-ghost">Archive</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('announcements.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3 card">
        <div class="prose max-w-none text-base leading-relaxed">
            {!! nl2br(e($announcement->body)) !!}
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <h2 class="card-title mb-3">Details</h2>
            <dl class="detail-list text-sm">
                <dt>Author</dt><dd>{{ $announcement->author->name }}</dd>
                <dt>Created</dt><dd>{{ $announcement->created_at->format('d M Y, H:i') }}</dd>
                @if ($announcement->publish_at)
                <dt>Published</dt><dd>{{ $announcement->publish_at->format('d M Y, H:i') }}</dd>
                @endif
                @if ($announcement->expires_at)
                <dt>Expires</dt>
                <dd class="{{ $announcement->is_expired ? 'text-danger' : '' }}">
                    {{ $announcement->expires_at->format('d M Y, H:i') }}
                </dd>
                @endif
            </dl>
        </div>

        @can('manage announcements')
        <div class="card">
            <h2 class="card-title mb-3 text-danger">Danger</h2>
            <form method="POST" action="{{ route('announcements.destroy', $announcement) }}"
                  onsubmit="return confirm('Delete this announcement permanently?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger w-full">Delete Announcement</button>
            </form>
        </div>
        @endcan
    </div>
</div>
@endsection

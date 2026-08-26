@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $event->title }}</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="badge badge-gray">{{ \App\Models\Event::TYPES[$event->type] ?? $event->type }}</span>
            <span class="badge {{ $event->status_color }}">{{ $event->status_label }}</span>
        </div>
    </div>
    <div class="flex gap-3">
        @can('manage events')
        <a href="{{ route('events.edit', $event) }}" class="btn btn-ghost">Edit</a>
        @if (!in_array($event->status, ['cancelled', 'completed']))
        <form method="POST" action="{{ route('events.cancel', $event) }}">
            @csrf @method('PATCH')
            <button class="btn btn-ghost text-danger"
                    onclick="return confirm('Cancel this event?')">Cancel Event</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('events.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card">
        @if ($event->description)
            <div class="prose max-w-none text-base leading-relaxed">
                {!! nl2br(e($event->description)) !!}
            </div>
        @else
            <p class="text-muted italic">No description provided.</p>
        @endif
    </div>

    <div class="space-y-4">
        <div class="card">
            <h2 class="card-title mb-3">Details</h2>
            <dl class="detail-list text-sm">
                <dt>When</dt>
                <dd>{{ $event->duration }}</dd>
                @if ($event->location)
                <dt>Location</dt>
                <dd>{{ $event->location }}</dd>
                @endif
                <dt>Audience</dt>
                <dd>{{ \App\Models\Event::AUDIENCES[$event->audience] ?? $event->audience }}</dd>
                <dt>Created by</dt>
                <dd>{{ $event->author->name }}</dd>
                <dt>Created</dt>
                <dd>{{ $event->created_at->format('d M Y') }}</dd>
            </dl>
            <div class="mt-3">
                <span class="inline-block w-4 h-4 rounded-full" style="background: {{ $event->color }}"></span>
            </div>
        </div>

        @can('manage events')
        <div class="card">
            <h2 class="card-title mb-3 text-danger">Danger</h2>
            <form method="POST" action="{{ route('events.destroy', $event) }}"
                  onsubmit="return confirm('Delete this event permanently?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger w-full">Delete Event</button>
            </form>
        </div>
        @endcan
    </div>
</div>
@endsection

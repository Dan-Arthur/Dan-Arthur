@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Messages</h1>
        @if ($unreadCount > 0)
            <p class="page-subtitle">{{ $unreadCount }} unread</p>
        @endif
    </div>
    <a href="{{ route('messages.create') }}" class="btn btn-primary">Compose</a>
</div>

{{-- Tabs --}}
<div class="tabs mb-6">
    <a href="{{ route('messages.index') }}?tab=inbox"
       class="tab {{ $tab === 'inbox' ? 'tab-active' : '' }}">
        Inbox
        @if ($unreadCount > 0)
            <span class="badge badge-danger ml-1">{{ $unreadCount }}</span>
        @endif
    </a>
    <a href="{{ route('messages.index') }}?tab=sent"
       class="tab {{ $tab === 'sent' ? 'tab-active' : '' }}">Sent</a>
</div>

@if ($messages->isEmpty())
    <div class="empty-state">{{ $tab === 'inbox' ? 'Your inbox is empty.' : 'No sent messages.' }}</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    @if ($tab === 'inbox')
                        <th>From</th>
                    @else
                        <th>To</th>
                    @endif
                    <th>Subject</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($messages as $item)
                @if ($tab === 'inbox')
                    @php
                        $pivot   = $item;           // MessageRecipient
                        $message = $item->message;
                    @endphp
                    <tr class="{{ !$pivot->is_read ? 'font-semibold bg-surface-hover/50' : '' }}">
                        <td>
                            {{ $message->sender->name }}
                            @if ($pivot->is_starred) <span class="text-warning">★</span> @endif
                        </td>
                        <td>{{ $message->subject ?: '(no subject)' }}</td>
                        <td class="text-muted text-sm">{{ $message->created_at->format('d M Y, H:i') }}</td>
                        <td class="table-actions">
                            <a href="{{ route('messages.show', $message->id) }}" class="action-link">Read</a>
                            <form method="POST" action="{{ route('messages.star', $message->id) }}">
                                @csrf @method('PATCH')
                                <button class="action-link">{{ $pivot->is_starred ? 'Unstar' : 'Star' }}</button>
                            </form>
                            <form method="POST" action="{{ route('messages.trash', $message->id) }}">
                                @csrf @method('PATCH')
                                <button class="action-link text-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @else
                    @php $message = $item @endphp
                    <tr>
                        <td class="text-muted text-sm">
                            {{ $message->recipients->pluck('user.name')->join(', ') ?: '—' }}
                        </td>
                        <td>{{ $message->subject ?: '(no subject)' }}</td>
                        <td class="text-muted text-sm">{{ $message->created_at->format('d M Y, H:i') }}</td>
                        <td class="table-actions">
                            <a href="{{ route('messages.show', $message->id) }}?tab=sent" class="action-link">View</a>
                        </td>
                    </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
@endif
@endsection

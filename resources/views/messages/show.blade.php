@extends('layouts.app')

@section('title', $message->subject ?: '(no subject)')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $message->subject ?: '(no subject)' }}</h1>
        <p class="page-subtitle">
            From <strong>{{ $message->sender->name }}</strong>
            · {{ $message->created_at->format('d M Y, H:i') }}
        </p>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-ghost">Back</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3 space-y-4">
        {{-- Message body --}}
        <div class="card">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium">{{ $message->sender->name }}</p>
                    <p class="text-sm text-muted">{{ $message->sender->email }}</p>
                </div>
            </div>
            <div class="prose max-w-none text-base leading-relaxed whitespace-pre-wrap">{{ $message->body }}</div>
        </div>

        {{-- Reply form (inbox only) --}}
        @if ($pivot && $message->sender_id !== auth()->id())
        <div class="card" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                <span x-text="open ? '▾ Reply' : '▸ Reply'">▸ Reply</span>
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <form method="POST" action="{{ route('messages.reply', $message) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Reply to {{ $message->sender->name }}</label>
                        <textarea name="body" class="form-textarea" rows="5" required
                                  placeholder="Write your reply…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="card text-sm">
            <h2 class="card-title mb-3">Recipients</h2>
            <ul class="space-y-1">
                @foreach ($message->recipients as $mr)
                <li class="flex items-center justify-between">
                    <span>{{ $mr->user->name }}</span>
                    @if ($mr->is_read)
                        <span class="text-xs text-success">Read</span>
                    @else
                        <span class="text-xs text-muted">Unread</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        @if ($pivot)
        <div class="card text-sm">
            <h2 class="card-title mb-3">Actions</h2>
            <form method="POST" action="{{ route('messages.star', $message->id) }}" class="mb-2">
                @csrf @method('PATCH')
                <button class="btn btn-ghost w-full text-sm">
                    {{ $pivot->is_starred ? 'Unstar' : 'Star' }}
                </button>
            </form>
            <form method="POST" action="{{ route('messages.trash', $message->id) }}"
                  onsubmit="return confirm('Remove from inbox?')">
                @csrf @method('PATCH')
                <button class="btn btn-ghost w-full text-sm text-danger">Remove from Inbox</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

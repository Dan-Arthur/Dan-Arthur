@extends('layouts.app')

@section('title', 'SMS Alert Detail')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">SMS Alert</h1>
        <p class="page-subtitle">Sent {{ $smsAlert->sent_at?->format('d M Y \a\t g:ia') }}</p>
    </div>
    <a href="{{ route('sms.index') }}" class="btn btn-ghost">← Back</a>
</div>

<div class="max-w-2xl space-y-4">
    <div class="card space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-muted text-xs uppercase tracking-wider mb-1">Sent To</p>
                <p class="font-medium">
                    {{ $smsAlert->groupLabel() }}
                    @if ($smsAlert->schoolClass)
                        — {{ $smsAlert->schoolClass->name }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-muted text-xs uppercase tracking-wider mb-1">Recipients</p>
                <p class="font-medium text-lg">{{ number_format($smsAlert->recipients_count) }}</p>
            </div>
            <div>
                <p class="text-muted text-xs uppercase tracking-wider mb-1">Sent By</p>
                <p class="font-medium">{{ $smsAlert->sender->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-muted text-xs uppercase tracking-wider mb-1">Status</p>
                <span class="badge {{ $smsAlert->status === 'sent' ? 'badge-green' : 'badge-gray' }}">
                    {{ ucfirst($smsAlert->status) }}
                </span>
            </div>
        </div>

        <hr class="border-border">

        <div>
            <p class="text-muted text-xs uppercase tracking-wider mb-2">Message</p>
            <div class="bg-surface-alt rounded-lg p-4 font-mono text-sm whitespace-pre-wrap break-words">{{ $smsAlert->body }}</div>
            <p class="text-xs text-muted mt-2">
                {{ strlen($smsAlert->body) }} characters ·
                {{ ceil(strlen($smsAlert->body) / 160) }} SMS part(s)
            </p>
        </div>
    </div>

    @if ($smsAlert->phone_numbers && count($smsAlert->phone_numbers) > 0)
    <div class="card">
        <h2 class="card-title mb-3">Phone Numbers ({{ count($smsAlert->phone_numbers) }})</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ($smsAlert->phone_numbers as $phone)
                <span class="badge badge-gray text-xs font-mono">{{ $phone }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('sms.create') }}" class="btn btn-primary">Send Another</a>
        <a href="{{ route('sms.index') }}" class="btn btn-ghost">Back to History</a>
    </div>
</div>
@endsection

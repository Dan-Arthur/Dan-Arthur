@extends('layouts.app')

@section('title', 'Bulk SMS History')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Bulk SMS Alerts</h1>
        <p class="page-subtitle">History of all SMS alerts sent from this school</p>
    </div>
    @can('send messages')
    <a href="{{ route('sms.create') }}" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
        </svg>
        New SMS Alert
    </a>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if ($alerts->isEmpty())
    <div class="card text-center py-12 text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <p class="font-medium">No SMS alerts sent yet</p>
        <p class="text-sm mt-1">Use <a href="{{ route('sms.create') }}" class="link">New SMS Alert</a> to send your first bulk SMS.</p>
    </div>
@else
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Sent To</th>
                    <th>Message</th>
                    <th class="text-right">Recipients</th>
                    <th>Sent By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alerts as $alert)
                <tr>
                    <td class="whitespace-nowrap text-sm text-muted">
                        {{ $alert->sent_at?->format('d M Y, g:ia') ?? $alert->created_at->format('d M Y, g:ia') }}
                    </td>
                    <td>
                        <span class="badge badge-blue text-xs">
                            {{ $alert->groupLabel() }}
                            @if ($alert->schoolClass)
                                — {{ $alert->schoolClass->name }}
                            @endif
                        </span>
                    </td>
                    <td class="max-w-xs truncate text-sm">{{ Str::limit($alert->body, 60) }}</td>
                    <td class="text-right font-medium">{{ number_format($alert->recipients_count) }}</td>
                    <td class="text-sm text-muted">{{ $alert->sender->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('sms.show', $alert) }}" class="btn btn-xs btn-ghost">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $alerts->links() }}</div>
@endif
@endsection

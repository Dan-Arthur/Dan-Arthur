@extends('layouts.app')

@section('title', 'Audit Entry')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Audit Entry #{{ $audit->id }}</h1>
        <p class="page-subtitle">{{ $audit->created_at->format('d M Y, H:i:s') }}</p>
    </div>
    <a href="{{ route('audit.index') }}" class="btn btn-ghost">Back to Log</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Meta --}}
    <div class="card">
        <h2 class="card-title mb-4">Details</h2>
        <dl class="detail-list text-sm">
            <dt>Event</dt>
            <dd><span class="badge {{ $audit->event_color }}">{{ $audit->event_label }}</span></dd>

            <dt>User</dt>
            <dd>
                @if ($audit->user)
                    {{ $audit->user->name }}
                    <span class="text-xs text-muted">({{ $audit->user_type }})</span>
                @else
                    <span class="text-muted italic">System / Unauthenticated</span>
                @endif
            </dd>

            <dt>Model</dt>
            <dd>
                {{ $audit->model_label }}
                @if ($audit->auditable_id)
                    <span class="font-mono text-xs text-muted">#{{ $audit->auditable_id }}</span>
                @endif
            </dd>

            @if ($audit->tags)
            <dt>Tags</dt>
            <dd><span class="badge badge-gray text-xs">{{ $audit->tags }}</span></dd>
            @endif

            <dt>IP Address</dt>
            <dd class="font-mono text-xs">{{ $audit->ip_address ?? '—' }}</dd>

            <dt>URL</dt>
            <dd class="font-mono text-xs break-all">{{ $audit->url ?? '—' }}</dd>

            <dt>User Agent</dt>
            <dd class="text-xs text-muted break-all">{{ $audit->user_agent ?? '—' }}</dd>
        </dl>
    </div>

    {{-- Values diff --}}
    <div class="lg:col-span-2 space-y-4">
        @php $diff = $audit->changed_fields @endphp

        @if (!empty($diff))
        <div class="card">
            <h2 class="card-title mb-4">Changes ({{ count($diff) }} field{{ count($diff) !== 1 ? 's' : '' }})</h2>
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th class="text-danger">Before</th>
                            <th class="text-success">After</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diff as $field => $change)
                        <tr>
                            <td class="font-mono text-xs font-semibold">{{ $field }}</td>
                            <td class="font-mono text-xs text-danger max-w-xs break-all">
                                @if ($change['old'] === null)
                                    <span class="text-muted italic">null</span>
                                @elseif (is_array($change['old']))
                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($change['old'], JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $change['old'] }}
                                @endif
                            </td>
                            <td class="font-mono text-xs text-success max-w-xs break-all">
                                @if ($change['new'] === null)
                                    <span class="text-muted italic">null</span>
                                @elseif (is_array($change['new']))
                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($change['new'], JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $change['new'] }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($audit->new_values && $audit->event === 'created')
        <div class="card">
            <h2 class="card-title mb-4">Created Values</h2>
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead><tr><th>Field</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach ($audit->new_values as $field => $value)
                        <tr>
                            <td class="font-mono text-xs font-semibold">{{ $field }}</td>
                            <td class="font-mono text-xs break-all">
                                @if ($value === null)
                                    <span class="text-muted italic">null</span>
                                @elseif (is_array($value))
                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($audit->old_values && $audit->event === 'deleted')
        <div class="card">
            <h2 class="card-title mb-4">Deleted Record Values</h2>
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead><tr><th>Field</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach ($audit->old_values as $field => $value)
                        <tr>
                            <td class="font-mono text-xs font-semibold">{{ $field }}</td>
                            <td class="font-mono text-xs break-all text-danger">
                                @if ($value === null)
                                    <span class="text-muted italic">null</span>
                                @elseif (is_array($value))
                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if (in_array($audit->event, ['login', 'logout', 'viewed', 'exported']) && empty($diff) && !$audit->new_values && !$audit->old_values)
        <div class="card">
            <p class="text-muted italic text-sm">This is a {{ $audit->event_label }} event — no field changes recorded.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $item->name }}</h1>
        <p class="page-subtitle">
            {{ $item->code ?? '' }}
            @if ($item->category) · {{ $item->category->name }} @endif
        </p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Back</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Stats --}}
    <div class="card">
        <h2 class="card-title mb-4">Stock Info</h2>
        <dl class="detail-list">
            <dt>In Stock</dt>
            <dd class="text-2xl font-bold font-mono {{ $item->quantity_in_stock == 0 ? 'text-danger' : ($item->below_reorder ? 'text-warning' : 'text-success') }}">
                {{ number_format($item->quantity_in_stock) }}
                <span class="text-sm font-normal text-muted">{{ \App\Models\InventoryItem::UNITS[$item->unit] ?? $item->unit }}</span>
            </dd>
            <dt>Reorder Level</dt><dd class="font-mono">{{ number_format($item->reorder_level) }}</dd>
            <dt>Unit Cost</dt><dd class="font-mono">{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '—' }}</dd>
            <dt>Stock Value</dt>
            <dd class="font-mono">
                @if ($item->unit_cost)
                    {{ number_format($item->quantity_in_stock * $item->unit_cost, 2) }}
                @else
                    —
                @endif
            </dd>
        </dl>
    </div>

    {{-- Record movement --}}
    @can('manage inventory')
    <div class="card lg:col-span-2">
        <h2 class="card-title mb-4">Record Movement</h2>
        <form method="POST" action="{{ route('inventory.items.movement', $item) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Type <span class="required">*</span></label>
                    <select name="movement_type" class="form-select" required>
                        @foreach (\App\Models\StockMovement::TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v['sign'] }} {{ $v['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity <span class="required">*</span></label>
                    <input type="number" name="quantity" class="form-input" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Cost</label>
                    <input type="number" name="unit_cost" class="form-input" step="0.01" min="0"
                           value="{{ $item->unit_cost }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="movement_date" class="form-input" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference / Invoice #</label>
                    <input type="text" name="reference" class="form-input">
                </div>
                <div class="col-span-2 form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-input">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Record</button>
        </form>
    </div>
    @endcan
</div>

{{-- Movement history --}}
<div class="card mt-6">
    <h2 class="card-title mb-4">Movement History</h2>
    @if ($movements->isEmpty())
        <p class="text-muted text-sm">No movements recorded.</p>
    @else
        <div class="overflow-x-auto">
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Balance After</th>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $mv)
                    @php $meta = \App\Models\StockMovement::TYPES[$mv->movement_type] ?? ['label' => $mv->movement_type, 'sign' => '', 'color' => ''] @endphp
                    <tr>
                        <td>{{ $mv->movement_date ? $mv->movement_date->format('d M Y') : '—' }}</td>
                        <td><span class="{{ $meta['color'] }} font-medium">{{ $meta['label'] }}</span></td>
                        <td class="text-right font-mono {{ $meta['color'] }}">{{ $meta['sign'] }}{{ number_format(abs($mv->quantity)) }}</td>
                        <td class="text-right font-mono text-muted">{{ $mv->unit_cost ? number_format($mv->unit_cost, 2) : '—' }}</td>
                        <td class="text-right font-mono font-semibold">{{ number_format($mv->balance_after) }}</td>
                        <td>{{ $mv->reference ?? '—' }}</td>
                        <td>{{ $mv->supplier?->name ?? '—' }}</td>
                        <td>{{ $mv->recordedBy?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $movements->links() }}</div>
    @endif
</div>
@endsection

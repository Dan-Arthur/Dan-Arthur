@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Inventory</h1></div>
    <div class="flex gap-3">
        @can('manage inventory')
        <a href="{{ route('inventory.catalogue') }}" class="btn btn-ghost">Categories & Suppliers</a>
        <a href="{{ route('inventory.assets') }}" class="btn btn-ghost">Fixed Assets</a>
        <button class="btn btn-primary" x-data @click="$dispatch('open-item-modal')">Add Item</button>
        @endcan
    </div>
</div>

{{-- Summary cards --}}
<div class="stats-grid mb-6">
    <div class="stat-card">
        <p class="stat-label">Total Items</p>
        <p class="stat-value">{{ $totalItems }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Low Stock</p>
        <p class="stat-value text-warning">{{ $lowStockCount }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Out of Stock</p>
        <p class="stat-value text-danger">{{ $outOfStockCount }}</p>
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Search items…">
    <select name="category_id" class="form-select w-auto">
        <option value="">All Categories</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="stock" class="form-select w-auto">
        <option value="">All Stock</option>
        <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low / Reorder</option>
        <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($items->isEmpty())
    <div class="empty-state">No items found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Code</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th class="text-right">In Stock</th>
                    <th class="text-right">Reorder Level</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr>
                    <td class="font-medium">{{ $item->name }}</td>
                    <td class="font-mono text-sm">{{ $item->code ?? '—' }}</td>
                    <td>{{ $item->category?->name ?? '—' }}</td>
                    <td>{{ \App\Models\InventoryItem::UNITS[$item->unit] ?? $item->unit }}</td>
                    <td class="text-right font-mono {{ $item->quantity_in_stock == 0 ? 'text-danger font-semibold' : ($item->below_reorder ? 'text-warning font-semibold' : '') }}">
                        {{ number_format($item->quantity_in_stock) }}
                    </td>
                    <td class="text-right font-mono text-muted">{{ number_format($item->reorder_level) }}</td>
                    <td>
                        @if ($item->quantity_in_stock == 0)
                            <span class="badge badge-danger">Out of Stock</span>
                        @elseif ($item->below_reorder)
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-success">OK</span>
                        @endif
                    </td>
                    <td class="table-actions">
                        <a href="{{ route('inventory.items.show', $item) }}" class="action-link">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endif

{{-- Add Item Modal --}}
@can('manage inventory')
<div x-data="{ open: false }" x-on:open-item-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">Add Item</h2>
            <form method="POST" action="{{ route('inventory.items.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 form-group">
                        <label class="form-label">Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span class="required">*</span></label>
                        <select name="unit" class="form-select" required>
                            @foreach (\App\Models\InventoryItem::UNITS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-input" value="5" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" name="unit_cost" class="form-input" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-input" value="0" min="0">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Add Item</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

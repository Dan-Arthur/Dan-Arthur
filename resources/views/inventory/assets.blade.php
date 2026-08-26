@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Fixed Assets</h1></div>
    <div class="flex gap-3">
        @can('manage inventory')
        <button class="btn btn-primary" x-data @click="$dispatch('open-asset-modal')">Register Asset</button>
        @endcan
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Stock Items</a>
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Search assets…">
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach (\App\Models\Asset::STATUSES as $k => $meta)
            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('inventory.assets') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($assets->isEmpty())
    <div class="empty-state">No assets found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Tag #</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Condition</th>
                    <th>Status</th>
                    <th class="text-right">Purchase Value</th>
                    <th>Purchase Date</th>
                    <th>Warranty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assets as $asset)
                <tr>
                    <td class="font-medium">{{ $asset->name }}</td>
                    <td class="font-mono text-sm">{{ $asset->asset_tag ?? '—' }}</td>
                    <td>{{ $asset->category?->name ?? '—' }}</td>
                    <td>{{ $asset->location ?? '—' }}</td>
                    <td>{{ \App\Models\Asset::CONDITIONS[$asset->condition] ?? $asset->condition }}</td>
                    <td>
                        <span class="badge {{ \App\Models\Asset::STATUSES[$asset->status]['color'] ?? 'badge-gray' }}">
                            {{ \App\Models\Asset::STATUSES[$asset->status]['label'] ?? $asset->status }}
                        </span>
                    </td>
                    <td class="text-right font-mono">
                        {{ $asset->purchase_price ? number_format($asset->purchase_price, 2) : '—' }}
                    </td>
                    <td>{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '—' }}</td>
                    <td>
                        @if ($asset->warranty_expiry)
                            <span class="{{ $asset->warranty_expired ? 'text-danger' : 'text-success' }} text-sm">
                                {{ $asset->warranty_expiry->format('d M Y') }}
                                @if ($asset->warranty_expired) (Expired) @endif
                            </span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $assets->links() }}</div>
@endif

{{-- Register Asset Modal --}}
@can('manage inventory')
<div x-data="{ open: false }" x-on:open-asset-modal.window="open = true">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal max-w-2xl">
            <h2 class="modal-title">Register Asset</h2>
            <form method="POST" action="{{ route('inventory.assets.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 form-group">
                        <label class="form-label">Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Tag</label>
                        <input type="text" name="asset_tag" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" class="form-input">
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
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" placeholder="e.g. Staff Office">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Condition</label>
                        <select name="condition" class="form-select">
                            @foreach (\App\Models\Asset::CONDITIONS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Price</label>
                        <input type="number" name="purchase_price" class="form-input" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" class="form-input">
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
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@extends('layouts.app')

@section('title', 'Inventory Catalogue')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Categories & Suppliers</h1></div>
    <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Back to Stock</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add form --}}
    <div class="card">
        <h2 class="card-title mb-4">Add Entry</h2>
        <form method="POST" action="{{ route('inventory.catalogue.store') }}" x-data="{ type: 'category' }">
            @csrf
            <div class="form-group">
                <label class="form-label">Type</label>
                <select x-model="type" class="form-select">
                    <option value="category">Category</option>
                    <option value="supplier">Supplier</option>
                </select>
                <input type="hidden" name="type" :value="type">
            </div>
            <div class="form-group">
                <label class="form-label">Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div x-show="type === 'category'" class="form-group">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-input">
            </div>
            <template x-if="type === 'supplier'">
                <div class="space-y-3">
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input">
                    </div>
                </div>
            </template>
            <button type="submit" class="btn btn-primary w-full mt-2">Add</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-6">
        {{-- Categories --}}
        <div class="card">
            <h2 class="card-title mb-3">Categories ({{ $categories->count() }})</h2>
            @if ($categories->isEmpty())
                <p class="text-muted text-sm">None defined.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead><tr><th>Name</th><th>Code</th><th class="text-right">Items</th></tr></thead>
                        <tbody>
                            @foreach ($categories as $cat)
                            <tr>
                                <td>{{ $cat->name }}</td>
                                <td class="font-mono">{{ $cat->code ?? '—' }}</td>
                                <td class="text-right">{{ $cat->inventory_items_count ?? 0 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Suppliers --}}
        <div class="card">
            <h2 class="card-title mb-3">Suppliers ({{ $suppliers->count() }})</h2>
            @if ($suppliers->isEmpty())
                <p class="text-muted text-sm">None defined.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $sup)
                            <tr>
                                <td class="font-medium">{{ $sup->name }}</td>
                                <td>{{ $sup->contact_person ?? '—' }}</td>
                                <td>{{ $sup->phone ?? '—' }}</td>
                                <td>{{ $sup->email ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

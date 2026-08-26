@extends('layouts.app')

@section('title', 'Fee Management')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Fee Management</h1>
        <p class="page-subtitle">Manage fee structures and categories</p>
    </div>
    @can('manage fee structures')
    <a href="{{ route('fees.create') }}" class="btn btn-primary">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Fee Structure
    </a>
    @endcan
</div>

{{-- Tab navigation --}}
<div class="tabs-nav mb-6">
    <a href="{{ route('fees.index', ['tab' => 'structures', 'year_id' => $selectedYearId]) }}"
       class="tab-link {{ $tab === 'structures' ? 'active' : '' }}">Fee Structures</a>
    <a href="{{ route('fees.index', ['tab' => 'categories']) }}"
       class="tab-link {{ $tab === 'categories' ? 'active' : '' }}">Categories</a>
</div>

@if ($tab === 'structures')
    {{-- Year filter --}}
    <form method="GET" class="filter-bar mb-6">
        <input type="hidden" name="tab" value="structures">
        <select name="year_id" class="form-select w-auto" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </form>

    @if ($structures->isEmpty())
        <div class="empty-state">
            <p>No fee structures for this year.</p>
            @can('manage fee structures')
            <a href="{{ route('fees.create') }}" class="btn btn-primary mt-2">Create One</a>
            @endcan
        </div>
    @else
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Term</th>
                        <th>Class</th>
                        <th>Items</th>
                        <th class="text-right">Total Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($structures as $structure)
                    <tr>
                        <td>
                            <a href="{{ route('fees.show', $structure) }}" class="font-medium text-primary hover:underline">
                                {{ $structure->name }}
                            </a>
                            @if ($structure->student_category)
                                <span class="badge badge-gray ml-1">{{ $structure->student_category }}</span>
                            @endif
                        </td>
                        <td>{{ $structure->term?->name ?? 'All Terms' }}</td>
                        <td>{{ $structure->applies_to_all_classes ? 'All Classes' : ($structure->schoolClass?->name ?? '—') }}</td>
                        <td>{{ $structure->items->count() }}</td>
                        <td class="text-right font-mono">{{ number_format($structure->total, 2) }}</td>
                        <td>
                            @if ($structure->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="table-actions">
                            <a href="{{ route('fees.show', $structure) }}" class="action-link">View</a>
                            @can('manage fee structures')
                            <a href="{{ route('fees.edit', $structure) }}" class="action-link">Edit</a>
                            <form method="POST" action="{{ route('fees.destroy', $structure) }}" onsubmit="return confirm('Delete this fee structure?')">
                                @csrf @method('DELETE')
                                <button class="action-link text-danger">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@else
    {{-- CATEGORIES TAB --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @can('manage fee structures')
        <div class="card">
            <h2 class="card-title mb-4">Add Category</h2>
            <form method="POST" action="{{ route('fees.categories.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-input" value="{{ old('code') }}" placeholder="e.g. TUITION">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="2">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full">Add Category</button>
            </form>
        </div>
        @endcan

        <div class="{{ auth()->user()->can('manage fee structures') ? 'lg:col-span-2' : 'lg:col-span-3' }} card">
            <h2 class="card-title mb-4">All Categories ({{ $categories->count() }})</h2>
            @if ($categories->isEmpty())
                <p class="text-muted">No categories yet.</p>
            @else
                <div class="space-y-2">
                    @foreach ($categories as $category)
                    <div x-data="{ editing: false }" class="border border-border rounded-lg px-4 py-3">
                        <div class="flex items-center justify-between" x-show="!editing">
                            <div>
                                <span class="font-medium">{{ $category->name }}</span>
                                @if ($category->code)
                                    <span class="badge badge-gray ml-2">{{ $category->code }}</span>
                                @endif
                                @if ($category->description)
                                    <p class="text-sm text-muted mt-1">{{ $category->description }}</p>
                                @endif
                            </div>
                            @can('manage fee structures')
                            <div class="flex gap-3 shrink-0">
                                <button @click="editing = true" class="action-link">Edit</button>
                                <form method="POST" action="{{ route('fees.categories.destroy', $category) }}" onsubmit="return confirm('Delete category?')">
                                    @csrf @method('DELETE')
                                    <button class="action-link text-danger">Delete</button>
                                </form>
                            </div>
                            @endcan
                        </div>
                        @can('manage fee structures')
                        <form x-show="editing" method="POST" action="{{ route('fees.categories.update', $category) }}"
                              x-cloak class="flex gap-2 items-end">
                            @csrf @method('PATCH')
                            <div class="flex-1">
                                <input type="text" name="name" class="form-input" value="{{ $category->name }}" required>
                            </div>
                            <div class="w-32">
                                <input type="text" name="code" class="form-input" value="{{ $category->code }}" placeholder="Code">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" @click="editing = false" class="btn btn-ghost btn-sm">Cancel</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
@endsection

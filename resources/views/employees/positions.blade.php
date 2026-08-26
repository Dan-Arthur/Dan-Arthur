@extends('layouts.app')

@section('title', 'Positions')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Positions</h1>
        <p class="page-subtitle">Job titles and roles</p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">Back to Staff</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add form --}}
    @can('manage positions')
    <div class="card">
        <h2 class="card-title mb-4">Add Position</h2>
        <form method="POST" action="{{ route('employees.positions.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-input" value="{{ old('code') }}" placeholder="e.g. TCH">
            </div>
            <div class="form-group">
                <label class="form-label">Type <span class="required">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach (\App\Models\Position::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="2">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full">Add Position</button>
        </form>
    </div>
    @endcan

    {{-- Positions list --}}
    <div class="{{ auth()->user()->can('manage positions') ? 'lg:col-span-2' : 'lg:col-span-3' }} card">
        <h2 class="card-title mb-4">All Positions ({{ $positions->count() }})</h2>

        @if ($positions->isEmpty())
            <p class="text-muted">No positions defined yet.</p>
        @else
            @foreach (\App\Models\Position::TYPES as $typeKey => $typeLabel)
                @php $group = $positions->where('type', $typeKey) @endphp
                @if ($group->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-muted uppercase tracking-wide mb-3">{{ $typeLabel }}</h3>
                    <div class="space-y-2">
                        @foreach ($group as $position)
                        <div x-data="{ editing: false }" class="border border-border rounded-lg px-4 py-3">
                            <div class="flex items-center justify-between" x-show="!editing">
                                <div>
                                    <span class="font-medium">{{ $position->title }}</span>
                                    @if ($position->code)
                                        <span class="badge badge-gray ml-2">{{ $position->code }}</span>
                                    @endif
                                    @if ($position->department)
                                        <span class="text-sm text-muted ml-2">· {{ $position->department->name }}</span>
                                    @endif
                                    <div class="text-xs text-muted mt-1">{{ $position->employees_count }} employee(s)</div>
                                </div>
                                @can('manage positions')
                                <div class="flex gap-3 shrink-0">
                                    <button @click="editing = true" class="action-link">Edit</button>
                                    @if ($position->employees_count === 0)
                                    <form method="POST" action="{{ route('employees.positions.destroy', $position) }}"
                                          onsubmit="return confirm('Delete this position?')">
                                        @csrf @method('DELETE')
                                        <button class="action-link text-danger">Delete</button>
                                    </form>
                                    @endif
                                </div>
                                @endcan
                            </div>
                            @can('manage positions')
                            <form x-show="editing" x-cloak method="POST"
                                  action="{{ route('employees.positions.update', $position) }}"
                                  class="grid grid-cols-2 gap-2 items-end">
                                @csrf @method('PATCH')
                                <div>
                                    <input type="text" name="title" class="form-input" value="{{ $position->title }}" required>
                                </div>
                                <div>
                                    <input type="text" name="code" class="form-input" value="{{ $position->code }}" placeholder="Code">
                                </div>
                                <div>
                                    <select name="type" class="form-select">
                                        @foreach (\App\Models\Position::TYPES as $k => $l)
                                            <option value="{{ $k }}" {{ $position->type === $k ? 'selected' : '' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <select name="department_id" class="form-select">
                                        <option value="">— None —</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ $position->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2 flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="button" @click="editing = false" class="btn btn-ghost btn-sm">Cancel</button>
                                </div>
                            </form>
                            @endcan
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection

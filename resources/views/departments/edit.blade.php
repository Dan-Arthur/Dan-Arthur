@extends('layouts.app')

@section('title', 'Edit ' . $department->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('departments.index') }}" class="hover:text-blue-600">Departments</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('departments.show', $department) }}" class="hover:text-blue-600">{{ $department->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit Department</h1>
    </div>
</div>

<form method="POST" action="{{ route('departments.update', $department) }}" class="max-w-2xl space-y-6">
@csrf @method('PUT')

<div class="card p-6 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Department Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $department->name) }}"
                class="form-input @error('name') border-red-500 @enderror" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $department->code) }}"
                class="form-input @error('code') border-red-500 @enderror" required>
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Type</label>
            <input type="text" name="type" value="{{ old('type', $department->type) }}"
                class="form-input" placeholder="e.g. Academic, Administrative">
        </div>

        @if($campuses->count() > 0)
        <div>
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select">
                <option value="">— All Campuses / Not Specific —</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected(old('campus_id', $department->campus_id) == $campus->id)>{{ $campus->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="form-label">Department Head</label>
            <select name="head_id" class="form-select">
                <option value="">— Unassigned —</option>
                @foreach($heads as $head)
                <option value="{{ $head->id }}" @selected(old('head_id', $department->head_id) == $head->id)>{{ $head->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-input">{{ old('description', $department->description) }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary">Save Changes</button>
    <a href="{{ route('departments.show', $department) }}" class="btn-secondary">Cancel</a>
</div>
</form>
@endsection

@extends('layouts.app')

@section('title', 'Edit ' . $subject->name)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('subjects.index') }}" class="hover:text-blue-600">Subjects</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('subjects.show', $subject) }}" class="hover:text-blue-600">{{ $subject->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="page-title">Edit Subject</h1>
    </div>
</div>

<form method="POST" action="{{ route('subjects.update', $subject) }}" class="max-w-2xl space-y-6">
@csrf @method('PUT')

<div class="card p-6 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Subject Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $subject->name) }}"
                class="form-input @error('name') border-red-500 @enderror" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Subject Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $subject->code) }}"
                class="form-input @error('code') border-red-500 @enderror" required>
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Type <span class="text-red-500">*</span></label>
            <select name="type" class="form-select" required>
                @foreach(\App\Models\Subject::TYPES as $key => $info)
                <option value="{{ $key }}" @selected(old('type', $subject->type) === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Level</label>
            <select name="level" class="form-select">
                <option value="">— Universal (all levels) —</option>
                @foreach(\App\Models\Subject::LEVELS as $key => $label)
                <option value="{{ $key }}" @selected(old('level', $subject->level) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">— None —</option>
                @foreach(\App\Models\Subject::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(old('category', $subject->category) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">— None —</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id', $subject->department_id) == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Credit Hours <span class="text-red-500">*</span></label>
            <input type="number" name="credit_hours" value="{{ old('credit_hours', $subject->credit_hours) }}"
                min="1" max="10" class="form-input" required>
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="has_practical" value="0">
                <input type="checkbox" name="has_practical" value="1"
                    @checked(old('has_practical', $subject->has_practical)) class="rounded">
                <span class="text-sm text-gray-700 dark:text-gray-300">Has practical / lab component</span>
            </label>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary">Save Changes</button>
    <a href="{{ route('subjects.show', $subject) }}" class="btn-secondary">Cancel</a>
</div>
</form>
@endsection

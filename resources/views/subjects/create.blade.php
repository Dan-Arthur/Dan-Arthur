@extends('layouts.app')

@section('title', 'New Subject')

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('subjects.index') }}" class="hover:text-blue-600">Subjects</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>New Subject</span>
        </div>
        <h1 class="page-title">New Subject</h1>
    </div>
</div>

<form method="POST" action="{{ route('subjects.store') }}" class="max-w-2xl space-y-6">
@csrf

<div class="card p-6 space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Subject Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
                class="form-input @error('name') border-red-500 @enderror"
                placeholder="e.g. Mathematics" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Subject Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}"
                class="form-input @error('code') border-red-500 @enderror"
                placeholder="e.g. MTH101" required>
            <p class="text-xs text-gray-400 mt-1">Unique per school.</p>
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Type <span class="text-red-500">*</span></label>
            <select name="type" class="form-select @error('type') border-red-500 @enderror" required>
                @foreach(\App\Models\Subject::TYPES as $key => $info)
                <option value="{{ $key }}" @selected(old('type', 'core') === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
            @error('type')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">— None —</option>
                @foreach(\App\Models\Subject::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">— None —</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Credit Hours <span class="text-red-500">*</span></label>
            <input type="number" name="credit_hours" value="{{ old('credit_hours', 1) }}"
                min="1" max="10" class="form-input @error('credit_hours') border-red-500 @enderror" required>
            @error('credit_hours')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="has_practical" value="0">
                <input type="checkbox" name="has_practical" value="1"
                    @checked(old('has_practical')) class="rounded">
                <span class="text-sm text-gray-700 dark:text-gray-300">This subject has a practical / lab component</span>
            </label>
        </div>
    </div>
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="btn-primary">Create Subject</button>
    <a href="{{ route('subjects.index') }}" class="btn-secondary">Cancel</a>
</div>
</form>
@endsection

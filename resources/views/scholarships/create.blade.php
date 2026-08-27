@extends('layouts.app')
@section('title', 'New Scholarship')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Scholarship</h1>
        <p class="page-subtitle">Define a scholarship programme</p>
    </div>
    <a href="{{ route('scholarships.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('scholarships.store') }}"
      x-data="{ type: '{{ old('type', 'percentage') }}' }"
      class="max-w-xl card space-y-5">
    @csrf

    <div class="form-group">
        <label class="form-label">Scholarship Name <span class="required">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-input"
            placeholder="e.g. Academic Excellence Award" required>
        @error('name')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
            <label class="form-label">Type <span class="required">*</span></label>
            <select name="type" class="form-select" x-model="type" required>
                @foreach (\App\Models\Scholarship::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', 'percentage') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                Value <span class="required">*</span>
                <span class="text-xs text-gray-400 font-normal" x-text="type === 'percentage' ? '(%)' : '({{ $currency }})'"></span>
            </label>
            <input type="number" name="value" value="{{ old('value') }}" step="0.01" min="0"
                class="form-input font-mono" placeholder="0.00" required>
            @error('value')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-textarea"
            placeholder="Criteria, eligibility, notes…">{{ old('description') }}</textarea>
        @error('description')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="rounded"
                {{ old('is_active', '1') ? 'checked' : '' }}>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active (available for invoice creation)</span>
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Create Scholarship</button>
        <a href="{{ route('scholarships.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

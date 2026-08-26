@extends('layouts.app')

@section('title', 'New Announcement')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">New Announcement</h1></div>
    <a href="{{ route('announcements.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('announcements.store') }}" class="max-w-2xl space-y-6"
      x-data="{ audience: '{{ old('audience', 'all') }}' }">
    @csrf

    <div class="card space-y-4">
        <h2 class="card-title">Content</h2>

        <div class="form-group">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" class="form-input" value="{{ old('title') }}" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Body <span class="required">*</span></label>
            <textarea name="body" class="form-textarea" rows="8" required>{{ old('body') }}</textarea>
            @error('body')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="card space-y-4">
        <h2 class="card-title">Settings</h2>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Type <span class="required">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach ($types as $k => $v)
                        <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Audience <span class="required">*</span></label>
                <select name="audience" class="form-select" x-model="audience" required>
                    @foreach ($audiences as $k => $v)
                        <option value="{{ $k }}" {{ old('audience', 'all') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" name="publish_at" class="form-input"
                       value="{{ old('publish_at') }}">
                <p class="form-hint">Leave blank to publish immediately when status is Published.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Expires At</label>
                <input type="datetime-local" name="expires_at" class="form-input"
                       value="{{ old('expires_at') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <div class="form-group flex items-center gap-3 pt-6">
                <input type="checkbox" name="is_pinned" id="is_pinned" class="form-checkbox" value="1"
                       {{ old('is_pinned') ? 'checked' : '' }}>
                <label for="is_pinned" class="form-label mb-0 cursor-pointer">Pin to top</label>
            </div>
        </div>

        {{-- Class filter --}}
        @if ($classes->isNotEmpty())
        <div x-show="audience === 'students' || audience === 'parents'" class="form-group" x-cloak>
            <label class="form-label">Restrict to Classes (optional)</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($classes as $class)
                <label class="flex items-center gap-2 text-sm border border-border rounded px-3 py-1 cursor-pointer hover:bg-surface-hover">
                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                           {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }}
                           class="form-checkbox">
                    {{ $class->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Announcement</button>
        <a href="{{ route('announcements.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

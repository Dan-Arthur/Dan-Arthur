@extends('layouts.app')

@section('title', 'Edit Announcement')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Edit Announcement</h1></div>
    <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('announcements.update', $announcement) }}" class="max-w-2xl space-y-6"
      x-data="{ audience: '{{ old('audience', $announcement->audience) }}' }">
    @csrf @method('PUT')

    <div class="card space-y-4">
        <h2 class="card-title">Content</h2>

        <div class="form-group">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" class="form-input"
                   value="{{ old('title', $announcement->title) }}" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Body <span class="required">*</span></label>
            <textarea name="body" class="form-textarea" rows="8" required>{{ old('body', $announcement->body) }}</textarea>
        </div>
    </div>

    <div class="card space-y-4">
        <h2 class="card-title">Settings</h2>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    @foreach ($types as $k => $v)
                        <option value="{{ $k }}" {{ old('type', $announcement->type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Audience</label>
                <select name="audience" class="form-select" x-model="audience" required>
                    @foreach ($audiences as $k => $v)
                        <option value="{{ $k }}" {{ old('audience', $announcement->audience) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" name="publish_at" class="form-input"
                       value="{{ old('publish_at', $announcement->publish_at?->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Expires At</label>
                <input type="datetime-local" name="expires_at" class="form-input"
                       value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="draft" {{ old('status', $announcement->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $announcement->status) === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ old('status', $announcement->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <div class="form-group flex items-center gap-3 pt-6">
                <input type="checkbox" name="is_pinned" id="is_pinned" class="form-checkbox" value="1"
                       {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                <label for="is_pinned" class="form-label mb-0 cursor-pointer">Pin to top</label>
            </div>
        </div>

        @if ($classes->isNotEmpty())
        <div x-show="audience === 'students' || audience === 'parents'" class="form-group" x-cloak>
            <label class="form-label">Restrict to Classes (optional)</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($classes as $class)
                @php $selectedClasses = old('class_ids', $announcement->audience_filter['class_ids'] ?? []) @endphp
                <label class="flex items-center gap-2 text-sm border border-border rounded px-3 py-1 cursor-pointer hover:bg-surface-hover">
                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                           {{ in_array($class->id, $selectedClasses) ? 'checked' : '' }}
                           class="form-checkbox">
                    {{ $class->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

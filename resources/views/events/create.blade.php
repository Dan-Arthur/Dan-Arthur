@extends('layouts.app')

@section('title', 'New Event')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">New Event</h1></div>
    <a href="{{ route('events.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('events.store') }}" class="max-w-2xl space-y-6"
      x-data="{ allDay: {{ old('all_day') ? 'true' : 'false' }} }">
    @csrf

    <div class="card space-y-4">
        <h2 class="card-title">Event Details</h2>

        <div class="form-group">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" class="form-input" value="{{ old('title') }}" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea" rows="4">{{ old('description') }}</textarea>
        </div>

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
                <label class="form-label">Audience</label>
                <select name="audience" class="form-select">
                    @foreach ($audiences as $k => $v)
                        <option value="{{ $k }}" {{ old('audience', 'all') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Start <span class="required">*</span></label>
                <input type="datetime-local" name="start_datetime" class="form-input"
                       value="{{ old('start_datetime') }}" :type="allDay ? 'date' : 'datetime-local'" required>
                @error('start_datetime')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">End</label>
                <input :type="allDay ? 'date' : 'datetime-local'" name="end_datetime" class="form-input"
                       value="{{ old('end_datetime') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="{{ old('location') }}"
                       placeholder="e.g. Main Hall">
            </div>

            <div class="form-group">
                <label class="form-label">Colour</label>
                <input type="color" name="color" class="form-input h-10 p-1"
                       value="{{ old('color', '#3B82F6') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="scheduled" selected>Scheduled</option>
                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="form-group flex items-center gap-3 pt-6">
                <input type="checkbox" id="all_day" name="all_day" class="form-checkbox" value="1"
                       x-model="allDay" {{ old('all_day') ? 'checked' : '' }}>
                <label for="all_day" class="form-label mb-0 cursor-pointer">All-day event</label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Create Event</button>
        <a href="{{ route('events.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

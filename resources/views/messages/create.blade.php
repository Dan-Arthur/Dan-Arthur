@extends('layouts.app')

@section('title', 'Compose Message')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Compose Message</h1></div>
    <a href="{{ route('messages.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('messages.store') }}" class="max-w-2xl card"
      x-data="composeForm()">
    @csrf

    <div class="space-y-4">
        {{-- To --}}
        <div class="form-group">
            <label class="form-label">To <span class="required">*</span></label>

            {{-- Selected recipients chips --}}
            <div class="flex flex-wrap gap-2 mb-2">
                <template x-for="(r, i) in recipients" :key="r.id">
                    <span class="flex items-center gap-1 badge badge-gray text-sm">
                        <span x-text="r.name"></span>
                        <button type="button" @click="remove(i)" class="ml-1 text-muted hover:text-danger">✕</button>
                        <input type="hidden" name="recipient_ids[]" :value="r.id">
                    </span>
                </template>
            </div>

            <input type="text" class="form-input" placeholder="Type name or email…"
                   x-model="query"
                   x-on:input.debounce.350ms="search()"
                   autocomplete="off">

            <div x-show="suggestions.length" x-cloak class="relative">
                <ul class="absolute z-10 w-full bg-surface border border-border rounded-lg shadow-lg mt-1 py-1 max-h-48 overflow-y-auto">
                    <template x-for="s in suggestions" :key="s.id">
                        <li>
                            <button type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-surface-hover text-sm"
                                    @click="select(s)">
                                <span class="font-medium" x-text="s.name"></span>
                                <span class="text-muted ml-1" x-text="s.email"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
            @error('recipient_ids')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="{{ old('subject') }}" placeholder="(optional)">
        </div>

        <div class="form-group">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                @foreach (\App\Models\Message::TYPES as $k => $v)
                    <option value="{{ $k }}" {{ old('type', 'internal') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Message <span class="required">*</span></label>
            <textarea name="body" class="form-textarea" rows="8" required>{{ old('body') }}</textarea>
            @error('body')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Send Message</button>
            <a href="{{ route('messages.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
function composeForm() {
    return {
        query: '',
        recipients: [],
        suggestions: [],

        async search() {
            if (this.query.length < 2) { this.suggestions = []; return; }
            const resp = await fetch(`/messages/users/search?q=${encodeURIComponent(this.query)}`);
            const data = await resp.json();
            const taken = this.recipients.map(r => r.id);
            this.suggestions = data.filter(u => !taken.includes(u.id));
        },

        select(s) {
            this.recipients.push(s);
            this.query = '';
            this.suggestions = [];
        },

        remove(index) {
            this.recipients.splice(index, 1);
        },
    };
}
</script>
@endpush
@endsection

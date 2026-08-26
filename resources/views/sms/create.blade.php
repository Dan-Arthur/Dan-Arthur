@extends('layouts.app')

@section('title', 'Send Bulk SMS')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Bulk SMS Alert</h1>
        <p class="page-subtitle">Send an SMS to a group of parents, staff, or students</p>
    </div>
    <a href="{{ route('sms.index') }}" class="btn btn-ghost">← History</a>
</div>

<div class="max-w-2xl" x-data="smsForm()">
    <form method="POST" action="{{ route('sms.store') }}" class="card space-y-5">
        @csrf

        {{-- Recipient Group --}}
        <div class="form-group">
            <label class="form-label">Send To <span class="required">*</span></label>
            <select name="recipient_group" class="form-select" x-model="group" required>
                <option value="">— Choose recipient group —</option>
                @foreach ($groups as $key => $label)
                    <option value="{{ $key }}" {{ old('recipient_group') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('recipient_group')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Class selector (only when group needs a class) --}}
        <div class="form-group" x-show="needsClass" x-cloak>
            <label class="form-label">Class <span class="required">*</span></label>
            <select name="class_id" class="form-select" :required="needsClass">
                <option value="">— Select class —</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Message Body --}}
        <div class="form-group">
            <label class="form-label">
                Message <span class="required">*</span>
                <span class="text-muted text-xs font-normal ml-2" x-text="charInfo"></span>
            </label>
            <textarea name="body" class="form-textarea font-mono" rows="5"
                      maxlength="640" required
                      x-model="body"
                      placeholder="Type your SMS message here…">{{ old('body') }}</textarea>
            <p class="text-xs text-muted mt-1">
                Standard SMS is 160 characters. Longer messages are sent as multi-part SMS.
            </p>
            @error('body')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Preview bar --}}
        <div class="rounded-lg border border-border bg-surface-alt p-4 text-sm space-y-1" x-show="group">
            <div class="flex items-center gap-2 text-muted">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>This SMS will be dispatched to all phone numbers in the selected group.</span>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Send SMS
            </button>
            <a href="{{ route('sms.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function smsForm() {
    return {
        group: '{{ old('recipient_group', '') }}',
        body: `{{ old('body', '') }}`,

        get needsClass() {
            return this.group === 'class_parents' || this.group === 'class_students';
        },

        get charInfo() {
            const len = this.body.length;
            if (len === 0) return '0 / 160 chars';
            const parts = Math.ceil(len / 160);
            return `${len} chars · ${parts} SMS part${parts > 1 ? 's' : ''}`;
        },
    };
}
</script>
@endpush
@endsection

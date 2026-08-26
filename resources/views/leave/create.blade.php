@extends('layouts.app')

@section('title', 'New Leave Request')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Leave Request</h1>
        <p class="page-subtitle">Submit a staff leave application</p>
    </div>
    <a href="{{ route('leave.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('leave.store') }}" class="max-w-xl card"
      x-data="leaveForm()">
    @csrf

    <div class="space-y-4">
        <div class="form-group">
            <label class="form-label">Employee <span class="required">*</span></label>
            <select name="employee_id" class="form-select" required>
                <option value="">— Select Employee —</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}"
                            {{ old('employee_id', request('employee_id')) == $emp->id ? 'selected' : '' }}>
                        {{ $emp->full_name }} ({{ $emp->employee_number }})
                    </option>
                @endforeach
            </select>
            @error('employee_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Leave Type <span class="required">*</span></label>
            <select name="leave_type_id" class="form-select" x-model="selectedType"
                    @change="updateTypeInfo()" required>
                <option value="">— Select Type —</option>
                @foreach ($leaveTypes as $type)
                    <option value="{{ $type->id }}"
                            data-days="{{ $type->days_allowed }}"
                            data-paid="{{ $type->is_paid ? '1' : '0' }}"
                            {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }} ({{ $type->days_allowed }} days {{ $type->is_paid ? '' : '– Unpaid' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div x-show="typeInfo" x-cloak class="bg-surface-hover rounded-lg px-4 py-3 text-sm">
            <span x-text="typeInfo"></span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Start Date <span class="required">*</span></label>
                <input type="date" name="start_date" class="form-input"
                       value="{{ old('start_date') }}" x-model="startDate" @change="calcDays()" required>
            </div>
            <div class="form-group">
                <label class="form-label">End Date <span class="required">*</span></label>
                <input type="date" name="end_date" class="form-input"
                       value="{{ old('end_date') }}" x-model="endDate" @change="calcDays()" required>
            </div>
        </div>

        <div x-show="days > 0" x-cloak
             class="text-sm font-medium px-4 py-2 rounded-lg"
             :class="days > allowedDays ? 'bg-danger/10 text-danger' : 'bg-success/10 text-success'">
            <span x-text="days + ' working day(s) requested'"></span>
            <span x-show="allowedDays > 0" x-text="' (entitlement: ' + allowedDays + ')'"></span>
        </div>

        <div class="form-group">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-textarea" rows="3">{{ old('reason') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="{{ route('leave.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
function leaveForm() {
    return {
        selectedType: '{{ old('leave_type_id') }}',
        startDate: '{{ old('start_date') }}',
        endDate: '{{ old('end_date') }}',
        days: 0,
        allowedDays: 0,
        typeInfo: '',

        updateTypeInfo() {
            const sel = document.querySelector('select[name="leave_type_id"]');
            const opt = sel?.options[sel.selectedIndex];
            if (!opt || !opt.value) { this.typeInfo = ''; this.allowedDays = 0; return; }
            this.allowedDays = parseInt(opt.dataset.days) || 0;
            const paid = opt.dataset.paid === '1' ? 'Paid' : 'Unpaid';
            this.typeInfo = `${paid} leave · ${this.allowedDays} days entitlement`;
            this.calcDays();
        },

        calcDays() {
            if (!this.startDate || !this.endDate) { this.days = 0; return; }
            const start = new Date(this.startDate);
            const end   = new Date(this.endDate);
            if (end < start) { this.days = 0; return; }
            let count = 0;
            const cur = new Date(start);
            while (cur <= end) {
                const d = cur.getDay();
                if (d !== 0 && d !== 6) count++;
                cur.setDate(cur.getDate() + 1);
            }
            this.days = count;
        },
    };
}
</script>
@endpush
@endsection

@extends('layouts.app')
@section('title', 'Edit Payslip — ' . $payslip->employee->full_name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $payslip->employee->full_name }}</h1>
        <p class="page-subtitle">{{ $payslip->payrollRun->title }} · {{ $payslip->employee->employee_number }}</p>
    </div>
    <a href="{{ route('payroll.show', $payslip->payroll_run_id) }}" class="btn btn-ghost">← Back to Run</a>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="max-w-2xl space-y-4">

{{-- Read-only summary card --}}
<div class="card grid grid-cols-3 gap-4 text-center">
    <div>
        <p class="text-xs text-muted uppercase tracking-wider mb-1">Gross Pay</p>
        <p class="text-xl font-bold font-mono">{{ $currency }}{{ number_format($payslip->gross_pay, 2) }}</p>
    </div>
    <div>
        <p class="text-xs text-muted uppercase tracking-wider mb-1">Deductions</p>
        <p class="text-xl font-bold font-mono text-red-600">{{ $currency }}{{ number_format($payslip->total_deductions, 2) }}</p>
    </div>
    <div>
        <p class="text-xs text-muted uppercase tracking-wider mb-1">Net Pay</p>
        <p class="text-xl font-bold font-mono text-green-700">{{ $currency }}{{ number_format($payslip->net_pay, 2) }}</p>
    </div>
</div>

{{-- Edit form --}}
<form method="POST" action="{{ route('payroll.payslip.update', $payslip) }}"
      x-data="payslipForm()" class="card space-y-5">
    @csrf @method('PUT')

    <div class="form-group">
        <label class="form-label">Basic Salary</label>
        <input type="number" name="basic_salary" step="0.01" min="0"
               class="form-input font-mono" value="{{ old('basic_salary', $payslip->basic_salary) }}"
               x-model.number="basic">
        @error('basic_salary')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    {{-- Allowances --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="form-label mb-0">Allowances</label>
            <button type="button" @click="addAllowance()" class="btn btn-xs btn-ghost">+ Add</button>
        </div>
        <div class="space-y-2">
            <template x-for="(a, i) in allowances" :key="i">
                <div class="flex gap-2 items-center">
                    <input type="text" :name="'allowance_keys['+i+']'" x-model="a.key"
                           placeholder="e.g. Transport" class="form-input flex-1 text-sm">
                    <input type="number" :name="'allowance_values['+i+']'" x-model.number="a.val"
                           step="0.01" min="0" placeholder="0.00" class="form-input w-32 text-sm font-mono">
                    <button type="button" @click="allowances.splice(i,1)" class="text-muted hover:text-danger text-lg leading-none">✕</button>
                </div>
            </template>
            <p x-show="allowances.length === 0" class="text-sm text-muted">No allowances. Click + Add to include one.</p>
        </div>
    </div>

    {{-- Deductions --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="form-label mb-0">Deductions</label>
            <button type="button" @click="addDeduction()" class="btn btn-xs btn-ghost">+ Add</button>
        </div>
        <div class="space-y-2">
            <template x-for="(d, i) in deductions" :key="i">
                <div class="flex gap-2 items-center">
                    <input type="text" :name="'deduction_keys['+i+']'" x-model="d.key"
                           placeholder="e.g. SSNIT (5.5%)" class="form-input flex-1 text-sm">
                    <input type="number" :name="'deduction_values['+i+']'" x-model.number="d.val"
                           step="0.01" min="0" placeholder="0.00" class="form-input w-32 text-sm font-mono">
                    <button type="button" @click="deductions.splice(i,1)" class="text-muted hover:text-danger text-lg leading-none">✕</button>
                </div>
            </template>
            <p x-show="deductions.length === 0" class="text-sm text-muted">No deductions.</p>
        </div>
    </div>

    {{-- Live preview --}}
    <div class="rounded-lg border border-border bg-surface-alt p-4 text-sm space-y-1">
        <div class="flex justify-between">
            <span class="text-muted">Basic Salary</span>
            <span class="font-mono" x-text="fmt(basic)"></span>
        </div>
        <div class="flex justify-between text-blue-600">
            <span>Total Allowances</span>
            <span class="font-mono" x-text="fmt(totalAllowances)"></span>
        </div>
        <div class="flex justify-between font-medium border-t border-border pt-1 mt-1">
            <span>Gross Pay</span>
            <span class="font-mono" x-text="fmt(gross)"></span>
        </div>
        <div class="flex justify-between text-red-600">
            <span>Total Deductions</span>
            <span class="font-mono" x-text="fmt(totalDeductions)"></span>
        </div>
        <div class="flex justify-between font-bold text-green-700 border-t border-border pt-1 mt-1">
            <span>Net Pay</span>
            <span class="font-mono" x-text="fmt(net)"></span>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-textarea" rows="2" placeholder="Optional note for this payslip…">{{ old('notes', $payslip->notes) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Payslip</button>
        <a href="{{ route('payroll.show', $payslip->payroll_run_id) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
</div>

@push('scripts')
<script>
function payslipForm() {
    const sym = '{{ $currency }}';
    return {
        basic: {{ $payslip->basic_salary }},
        allowances: @json(collect($payslip->allowances ?? [])->map(fn($v, $k) => ['key' => $k, 'val' => $v])->values()),
        deductions: @json(collect($payslip->deductions ?? [])->map(fn($v, $k) => ['key' => $k, 'val' => $v])->values()),

        get totalAllowances() { return this.allowances.reduce((s,a) => s + (parseFloat(a.val)||0), 0); },
        get totalDeductions() { return this.deductions.reduce((s,d) => s + (parseFloat(d.val)||0), 0); },
        get gross() { return this.basic + this.totalAllowances; },
        get net()   { return Math.max(0, this.gross - this.totalDeductions); },

        addAllowance() { this.allowances.push({key:'', val:0}); },
        addDeduction() { this.deductions.push({key:'', val:0}); },
        fmt(v) { return sym + Number(v).toLocaleString('en-GH', {minimumFractionDigits:2}); },
    };
}
</script>
@endpush
@endsection

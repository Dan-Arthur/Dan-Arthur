@extends('layouts.app')
@section('title', 'New Payroll Run')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">New Payroll Run</h1>
        <p class="page-subtitle">Generate payslips for all active staff for a given month</p>
    </div>
    <a href="{{ route('payroll.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('payroll.store') }}" class="max-w-lg card space-y-5">
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
            <label class="form-label">Month <span class="required">*</span></label>
            <select name="month" class="form-select" required>
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}" {{ old('month', date('n')) == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('month')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Year <span class="required">*</span></label>
            <select name="year" class="form-select" required>
                @foreach ($years as $yr)
                    <option value="{{ $yr }}" {{ old('year', date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
            @error('year')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-textarea" rows="3" placeholder="Optional notes for this payroll run…">{{ old('notes') }}</textarea>
    </div>

    <div class="rounded-lg border border-border bg-surface-alt p-4 text-sm text-muted space-y-1">
        <p class="font-medium text-foreground">What happens when you create this run:</p>
        <ul class="list-disc list-inside space-y-0.5 mt-1">
            <li>A payslip is generated for every <strong>active</strong> employee</li>
            <li>Basic salary is pulled from each employee's profile</li>
            <li>SSNIT deduction (5.5%) is pre-filled — you can adjust per payslip</li>
            <li>You can add allowances and extra deductions before approving</li>
        </ul>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Generate Payroll</button>
        <a href="{{ route('payroll.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

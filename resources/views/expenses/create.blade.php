@extends('layouts.app')
@section('title', 'Record Expense')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Record Expense</h1>
        <p class="page-subtitle">Log a new school expenditure</p>
    </div>
    <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('expenses.store') }}" class="max-w-2xl card space-y-5">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="form-group sm:col-span-2">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-input"
                placeholder="e.g. Electricity bill — August 2026" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <select name="category" class="form-select" required>
                <option value="">— Select category —</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Amount ({{ $currency }}) <span class="required">*</span></label>
            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                class="form-input font-mono" placeholder="0.00" required>
            @error('amount')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Date <span class="required">*</span></label>
            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                max="{{ now()->toDateString() }}" class="form-input" required>
            @error('date')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Payment Method <span class="required">*</span></label>
            <select name="payment_method" class="form-select" required>
                @foreach ($paymentMethods as $key => $label)
                    <option value="{{ $key }}" @selected(old('payment_method', 'cash') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Paid To / Vendor</label>
            <input type="text" name="paid_to" value="{{ old('paid_to') }}" class="form-input"
                placeholder="e.g. ECG Ghana">
            @error('paid_to')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Reference / Receipt No.</label>
            <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="form-input"
                placeholder="e.g. RCP-001234">
            @error('reference_number')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" class="form-textarea"
            placeholder="Additional details about this expense…">{{ old('notes') }}</textarea>
        @error('notes')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save Expense</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

@extends('layouts.app')
@section('title', 'Edit Expense')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Expense</h1>
        <p class="page-subtitle">{{ $expense->title }}</p>
    </div>
    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('expenses.update', $expense) }}" class="max-w-2xl card space-y-5">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="form-group sm:col-span-2">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" value="{{ old('title', $expense->title) }}" class="form-input" required>
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <select name="category" class="form-select" required>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Amount ({{ $currency }}) <span class="required">*</span></label>
            <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}"
                step="0.01" min="0.01" class="form-input font-mono" required>
            @error('amount')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Date <span class="required">*</span></label>
            <input type="date" name="date" value="{{ old('date', $expense->date->toDateString()) }}"
                max="{{ now()->toDateString() }}" class="form-input" required>
            @error('date')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Payment Method <span class="required">*</span></label>
            <select name="payment_method" class="form-select" required>
                @foreach ($paymentMethods as $key => $label)
                    <option value="{{ $key }}" @selected(old('payment_method', $expense->payment_method) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Paid To / Vendor</label>
            <input type="text" name="paid_to" value="{{ old('paid_to', $expense->paid_to) }}" class="form-input">
            @error('paid_to')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Reference / Receipt No.</label>
            <input type="text" name="reference_number" value="{{ old('reference_number', $expense->reference_number) }}" class="form-input">
            @error('reference_number')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" class="form-textarea">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Update Expense</button>
        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

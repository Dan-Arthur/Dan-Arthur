@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Record Payment</h1>
        <p class="page-subtitle">
            @if ($invoice) For {{ $invoice->invoice_number }} @else Select invoice below @endif
        </p>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-ghost">Cancel</a>
</div>

<div class="max-w-2xl">
    @if (!$invoice)
    <div class="card mb-6">
        <h2 class="card-title mb-3">Find Invoice</h2>
        <form method="GET" class="flex gap-3">
            <input type="text" name="invoice_id" class="form-input flex-1" placeholder="Invoice ID or number…"
                   value="{{ request('invoice_id') }}">
            <button type="submit" class="btn btn-primary">Load</button>
        </form>
    </div>
    @endif

    @if ($invoice)
    {{-- Invoice summary --}}
    <div class="card mb-6">
        <h2 class="card-title mb-3">Invoice Summary</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-muted">Invoice</p>
                <p class="font-mono font-semibold">{{ $invoice->invoice_number }}</p>
            </div>
            <div>
                <p class="text-muted">Student</p>
                <p class="font-semibold">{{ $invoice->student->full_name }}</p>
                <p class="text-muted text-xs">{{ $invoice->student->admission_number }}</p>
            </div>
            <div>
                <p class="text-muted">Total Due</p>
                <p class="font-mono font-semibold">{{ number_format($invoice->total_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-muted">Balance Remaining</p>
                <p class="font-mono font-bold text-danger">{{ number_format($invoice->balance, 2) }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" class="card">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

        <h2 class="card-title mb-4">Payment Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Amount <span class="required">*</span></label>
                <input type="number" name="amount" class="form-input" value="{{ old('amount', $invoice->balance) }}"
                       min="0.01" step="0.01" max="{{ $invoice->balance }}" required>
                @error('amount')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Payment Date <span class="required">*</span></label>
                <input type="date" name="payment_date" class="form-input" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Payment Method <span class="required">*</span></label>
                <select name="payment_method" class="form-select" required>
                    <option value="">Select Method</option>
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('payment_method')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Reference Number</label>
                <input type="text" name="reference_number" class="form-input"
                       value="{{ old('reference_number') }}" placeholder="Cheque #, transfer ID…">
            </div>

            <div class="form-group">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="{{ old('bank_name') }}">
            </div>

            <div class="sm:col-span-2 form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Record Payment</button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost">Back to Invoice</a>
        </div>
    </form>
    @endif
</div>
@endsection

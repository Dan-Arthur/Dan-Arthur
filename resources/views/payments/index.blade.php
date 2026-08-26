@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Payments</h1>
        <p class="page-subtitle">All recorded payments</p>
    </div>
    @can('record payments')
    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Record Payment
    </a>
    @endcan
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" class="form-input flex-1" placeholder="Payment #, reference, student…"
           value="{{ request('search') }}">
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach ($statuses as $key => $meta)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <select name="method" class="form-select w-auto">
        <option value="">All Methods</option>
        @foreach ($methods as $key => $label)
            <option value="{{ $key }}" {{ request('method') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" class="form-input w-40" value="{{ request('date_from') }}" placeholder="From">
    <input type="date" name="date_to" class="form-input w-40" value="{{ request('date_to') }}" placeholder="To">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('payments.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($payments->isEmpty())
    <div class="empty-state">No payments found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Student</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                <tr>
                    <td class="font-mono text-sm">
                        <a href="{{ route('payments.show', $payment) }}" class="text-primary hover:underline">
                            {{ $payment->payment_number }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('students.show', $payment->student) }}" class="hover:underline">
                            {{ $payment->student->full_name }}
                        </a>
                    </td>
                    <td class="font-mono text-sm">
                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-primary hover:underline">
                            {{ $payment->invoice->invoice_number }}
                        </a>
                    </td>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                    <td>{{ $payment->method_label }}</td>
                    <td>{{ $payment->reference_number ?? '—' }}</td>
                    <td class="text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge {{ $payment->status_color }}">{{ $payment->status_label }}</span></td>
                    <td>
                        @if ($payment->receipt)
                            <span class="font-mono text-xs">{{ $payment->receipt->receipt_number }}</span>
                        @else
                            <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="table-actions">
                        <a href="{{ route('payments.show', $payment) }}" class="action-link">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endif
@endsection

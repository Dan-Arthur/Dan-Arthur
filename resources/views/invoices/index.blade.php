@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Invoices</h1>
        <p class="page-subtitle">Student fee invoices</p>
    </div>
    @can('create invoices')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Invoice
    </a>
    @endcan
</div>

<form method="GET" class="filter-bar mb-6">
    <select name="year_id" class="form-select w-auto">
        <option value="">All Years</option>
        @foreach ($years as $year)
            <option value="{{ $year->id }}" {{ request('year_id', $currentYear?->id) == $year->id ? 'selected' : '' }}>
                {{ $year->name }}
            </option>
        @endforeach
    </select>
    <select name="term_id" class="form-select w-auto">
        <option value="">All Terms</option>
        @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                {{ $term->name }}
            </option>
        @endforeach
    </select>
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach (\App\Models\Invoice::STATUSES as $key => $meta)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <input type="text" name="search" class="form-input flex-1" placeholder="Search student, admission no…" value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($invoices->isEmpty())
    <div class="empty-state">No invoices found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Term / Year</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                <tr>
                    <td class="font-mono text-sm">{{ $invoice->invoice_number }}</td>
                    <td>
                        <a href="{{ route('students.show', $invoice->student) }}" class="font-medium hover:underline">
                            {{ $invoice->student->full_name }}
                        </a>
                        <div class="text-xs text-muted">{{ $invoice->student->admission_number }}</div>
                    </td>
                    <td>
                        <div>{{ $invoice->term?->name ?? '—' }}</div>
                        <div class="text-xs text-muted">{{ $invoice->academicYear->name }}</div>
                    </td>
                    <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                    <td>
                        @if ($invoice->due_date)
                            <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger font-medium' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-right font-mono {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($invoice->balance, 2) }}
                    </td>
                    <td><span class="badge {{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td>
                    <td class="table-actions">
                        <a href="{{ route('invoices.show', $invoice) }}" class="action-link">View</a>
                        @can('record payments')
                        @if (!in_array($invoice->status, ['paid', 'cancelled']))
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="action-link">Pay</a>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endif
@endsection

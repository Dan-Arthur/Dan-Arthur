@extends('layouts.app')

@section('title', 'Fee Defaulters')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Fee Defaulters</h1>
        <p class="page-subtitle">Students with unpaid or partially paid invoices</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-ghost">← Reports</a>
</div>

{{-- Outstanding total --}}
<div class="card mb-6 flex items-center justify-between">
    <div>
        <p class="text-sm text-muted">Total Outstanding Balance</p>
        <p class="text-3xl font-bold text-red-500 font-mono">{{ $currency }}{{ number_format($totalOutstanding, 2) }}</p>
    </div>
    <div class="text-right">
        <p class="text-sm text-muted">Defaulters shown</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($defaulters->total()) }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar mb-6">
    <select name="year_id" class="form-select w-44" onchange="this.form.submit()">
        <option value="">All Years</option>
        @foreach ($years as $yr)
            <option value="{{ $yr->id }}" {{ $yr->id == $yearId ? 'selected' : '' }}>{{ $yr->name }}</option>
        @endforeach
    </select>
    <select name="class_id" class="form-select w-40">
        <option value="">All Classes</option>
        @foreach ($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select w-36">
        <option value="">All Statuses</option>
        <option value="unpaid"  {{ request('status') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('reports.defaulters') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($defaulters->isEmpty())
    <div class="empty-state">No defaulters found for the selected filters.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Invoice #</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($defaulters as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('students.show', $invoice->student_id) }}" class="font-medium hover:text-blue-600">
                            {{ $invoice->student->full_name }}
                        </a>
                        <p class="text-xs text-muted font-mono">{{ $invoice->student->admission_number }}</p>
                    </td>
                    <td class="text-sm">{{ $invoice->student->schoolClass?->name ?? '—' }}</td>
                    <td class="font-mono text-sm">{{ $invoice->invoice_number }}</td>
                    <td class="text-sm">
                        {{ $invoice->academicYear?->name ?? '—' }}
                        @if ($invoice->term) <span class="text-muted">/ {{ $invoice->term->name }}</span> @endif
                    </td>
                    <td class="text-sm {{ $invoice->due_date?->isPast() ? 'text-red-500 font-medium' : '' }}">
                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="text-right font-mono text-sm">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-right font-mono text-sm text-green-600">{{ $currency }}{{ number_format($invoice->amount_paid, 2) }}</td>
                    <td class="text-right font-mono text-sm font-bold text-red-500">{{ $currency }}{{ number_format($invoice->balance, 2) }}</td>
                    <td><span class="{{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td>
                    <td class="table-actions">
                        <a href="{{ route('invoices.show', $invoice) }}" class="action-link">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $defaulters->links() }}</div>
@endif
@endsection

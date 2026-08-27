@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage school expenditure</p>
    </div>
    @can('create expenses')
    <a href="{{ route('expenses.create') }}" class="btn-primary">+ Record Expense</a>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-input text-sm">
        </div>
        <div>
            <label class="form-label text-xs">To</label>
            <input type="date" name="to" value="{{ $to }}" max="{{ now()->toDateString() }}" class="form-input text-sm">
        </div>
        <div>
            <label class="form-label text-xs">Category</label>
            <select name="category" class="form-select text-sm">
                <option value="">All Categories</option>
                @foreach (\App\Models\Expense::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm">
                <option value="">All Statuses</option>
                @foreach (\App\Models\Expense::STATUSES as $key => $info)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Filter</button>
        <a href="{{ route('expenses.index') }}" class="btn-ghost text-sm px-3 py-2">Reset</a>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card text-center p-4">
        <p class="text-xl font-bold font-mono text-green-700">
            {{ $currency }}{{ number_format($totals['approved']->total ?? 0, 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Approved</p>
        <p class="text-xs text-gray-400">{{ $totals['approved']->cnt ?? 0 }} expense(s)</p>
    </div>
    <div class="card text-center p-4">
        <p class="text-xl font-bold font-mono text-amber-600">
            {{ $currency }}{{ number_format($totals['pending']->total ?? 0, 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Pending Approval</p>
        <p class="text-xs text-gray-400">{{ $totals['pending']->cnt ?? 0 }} expense(s)</p>
    </div>
    <div class="card text-center p-4">
        <p class="text-xl font-bold font-mono text-gray-900 dark:text-white">
            {{ $currency }}{{ number_format(collect($totals)->sum('total'), 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Total (All Statuses)</p>
        <p class="text-xs text-gray-400">{{ collect($totals)->sum('cnt') }} expense(s)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Top Categories</p>
        @forelse ($byCategory->take(3) as $cat)
        <div class="flex justify-between text-xs mb-1">
            <span class="text-gray-600 dark:text-gray-400 truncate mr-2">
                {{ \App\Models\Expense::CATEGORIES[$cat->category] ?? $cat->category }}
            </span>
            <span class="font-mono font-medium shrink-0">{{ $currency }}{{ number_format($cat->total, 2) }}</span>
        </div>
        @empty
        <p class="text-xs text-gray-400">No approved expenses yet.</p>
        @endforelse
    </div>
</div>

{{-- Expenses table --}}
<div class="card overflow-hidden">
    @if ($expenses->isEmpty())
        <div class="p-10 text-center text-gray-400 text-sm">
            No expenses found for the selected filters.
            @can('create expenses')
                <a href="{{ route('expenses.create') }}" class="text-blue-600 hover:underline">Record one now.</a>
            @endcan
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm data-table">
            <thead>
                <tr>
                    <th class="text-left">Date</th>
                    <th class="text-left">Title</th>
                    <th class="text-left">Category</th>
                    <th class="text-left">Paid To</th>
                    <th class="text-left">Method</th>
                    <th class="text-right">Amount</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                <tr>
                    <td class="whitespace-nowrap text-gray-500">
                        {{ $expense->date->format('d M Y') }}
                    </td>
                    <td>
                        <a href="{{ route('expenses.show', $expense) }}"
                            class="font-medium text-gray-900 dark:text-white hover:text-blue-600">
                            {{ $expense->title }}
                        </a>
                        @if ($expense->reference_number)
                            <p class="text-xs text-gray-400">Ref: {{ $expense->reference_number }}</p>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-gray text-xs">{{ $expense->category_label }}</span>
                    </td>
                    <td class="text-gray-500 text-xs">{{ $expense->paid_to ?: '—' }}</td>
                    <td class="text-gray-500 text-xs">{{ $expense->payment_method_label }}</td>
                    <td class="text-right font-mono font-semibold text-gray-900 dark:text-white">
                        {{ $currency }}{{ number_format($expense->amount, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $expense->status_color }}">{{ $expense->status_label }}</span>
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-xs btn-ghost">View</a>
                        @can('approve expenses')
                            @if ($expense->status === 'pending')
                            <form method="POST" action="{{ route('expenses.approve', $expense) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-xs btn-ghost text-green-600">Approve</button>
                            </form>
                            @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
        {{ $expenses->links() }}
    </div>
    @endif
</div>
@endsection

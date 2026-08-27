@extends('layouts.app')
@section('title', 'Expense — ' . $expense->title)

@section('content')
<div class="page-header">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('expenses.index') }}" class="hover:text-blue-600">Expenses</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="truncate">{{ $expense->title }}</span>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="page-title">{{ $expense->title }}</h1>
            <span class="badge {{ $expense->status_color }}">{{ $expense->status_label }}</span>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        @can('approve expenses')
            @if ($expense->status === 'pending')
            <form method="POST" action="{{ route('expenses.approve', $expense) }}">
                @csrf @method('PATCH')
                <button class="btn btn-primary">Approve</button>
            </form>
            <form method="POST" action="{{ route('expenses.reject', $expense) }}">
                @csrf @method('PATCH')
                <button class="btn btn-ghost text-red-600">Reject</button>
            </form>
            @endif
        @endcan

        @can('manage expenses')
            @if ($expense->status !== 'approved')
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-ghost">Edit</a>
            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                onsubmit="return confirm('Delete this expense? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost text-red-600">Delete</button>
            </form>
            @endif
        @endcan

        <a href="{{ route('expenses.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-4xl">

    {{-- Main details --}}
    <div class="lg:col-span-2 card divide-y divide-gray-100 dark:divide-gray-700">
        <div class="px-6 py-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Amount</p>
            <p class="text-3xl font-bold font-mono text-gray-900 dark:text-white">
                {{ $currency }}{{ number_format($expense->amount, 2) }}
            </p>
        </div>

        <dl class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
            <div class="px-6 py-3 flex justify-between">
                <dt class="text-gray-400">Category</dt>
                <dd class="font-medium text-gray-900 dark:text-white">
                    <span class="badge badge-gray">{{ $expense->category_label }}</span>
                </dd>
            </div>
            <div class="px-6 py-3 flex justify-between">
                <dt class="text-gray-400">Date</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $expense->date->format('d F Y') }}</dd>
            </div>
            <div class="px-6 py-3 flex justify-between">
                <dt class="text-gray-400">Paid To</dt>
                <dd class="text-gray-700 dark:text-gray-300">{{ $expense->paid_to ?: '—' }}</dd>
            </div>
            <div class="px-6 py-3 flex justify-between">
                <dt class="text-gray-400">Payment Method</dt>
                <dd class="text-gray-700 dark:text-gray-300">{{ $expense->payment_method_label }}</dd>
            </div>
            @if ($expense->reference_number)
            <div class="px-6 py-3 flex justify-between">
                <dt class="text-gray-400">Reference / Receipt No.</dt>
                <dd class="font-mono text-gray-700 dark:text-gray-300">{{ $expense->reference_number }}</dd>
            </div>
            @endif
            @if ($expense->notes)
            <div class="px-6 py-3">
                <dt class="text-gray-400 mb-1">Notes</dt>
                <dd class="text-gray-700 dark:text-gray-300 text-sm">{{ $expense->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Meta panel --}}
    <div class="space-y-4">
        <div class="card p-5 space-y-3 text-sm">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Record Info</h3>
            <div>
                <p class="text-gray-400 text-xs">Recorded by</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $expense->recordedBy?->name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ $expense->created_at->format('d M Y, H:i') }}</p>
            </div>
            @if ($expense->approved_at)
            <div>
                <p class="text-gray-400 text-xs">{{ $expense->status === 'approved' ? 'Approved' : 'Actioned' }} by</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $expense->approvedBy?->name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ $expense->approved_at->format('d M Y, H:i') }}</p>
            </div>
            @endif
        </div>

        @if ($expense->status === 'pending')
        <div class="card p-4 border-l-4 border-amber-400 bg-amber-50 dark:bg-amber-900/20">
            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Awaiting Approval</p>
            <p class="text-xs text-amber-600 dark:text-amber-500">
                This expense has been recorded but is not yet approved.
            </p>
        </div>
        @elseif ($expense->status === 'rejected')
        <div class="card p-4 border-l-4 border-red-400 bg-red-50 dark:bg-red-900/20">
            <p class="text-xs font-semibold text-red-700 dark:text-red-400">Rejected</p>
        </div>
        @endif
    </div>
</div>
@endsection

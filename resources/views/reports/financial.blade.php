@extends('layouts.app')

@section('title', 'Financial Report')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Financial Overview</h1>
        @if ($selectedYear)
            <p class="page-subtitle">{{ $selectedYear->name }}</p>
        @endif
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-ghost">← Reports</a>
</div>

{{-- Year filter --}}
<form method="GET" class="filter-bar mb-6">
    <select name="year_id" class="form-select w-44" onchange="this.form.submit()">
        <option value="">All Years</option>
        @foreach ($years as $yr)
            <option value="{{ $yr->id }}" {{ $yr->id == $yearId ? 'selected' : '' }}>{{ $yr->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('reports.financial') }}" class="btn btn-ghost">Reset</a>
</form>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card text-center py-5">
        <p class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ $currency }}{{ number_format($totalBilled, 2) }}</p>
        <p class="text-sm text-muted mt-1">Total Billed</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-2xl font-bold text-green-600 font-mono">{{ $currency }}{{ number_format($totalCollected, 2) }}</p>
        <p class="text-sm text-muted mt-1">Collected</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-2xl font-bold text-red-500 font-mono">{{ $currency }}{{ number_format($totalOutstanding, 2) }}</p>
        <p class="text-sm text-muted mt-1">Outstanding</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-2xl font-bold {{ $collectionRate >= 80 ? 'text-green-600' : ($collectionRate >= 50 ? 'text-yellow-500' : 'text-red-500') }}">{{ $collectionRate }}%</p>
        <p class="text-sm text-muted mt-1">Collection Rate</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Invoice status breakdown --}}
    <div class="card">
        <h2 class="card-title mb-4">Invoice Status Breakdown</h2>
        @if ($statusBreakdown->isEmpty())
            <p class="text-muted text-sm">No invoices found.</p>
        @else
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th class="text-right">Invoices</th>
                        <th class="text-right">Billed</th>
                        <th class="text-right">Collected</th>
                        <th class="text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['paid','partial','unpaid','overdue','cancelled'] as $st)
                        @if ($row = $statusBreakdown[$st] ?? null)
                        <tr>
                            <td>
                                @php $colors = ['paid'=>'badge-green','partial'=>'badge-blue','unpaid'=>'badge-red','overdue'=>'badge-red','cancelled'=>'badge-gray']; @endphp
                                <span class="{{ $colors[$st] ?? 'badge-gray' }}">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="text-right font-mono">{{ number_format($row->count) }}</td>
                            <td class="text-right font-mono">{{ $currency }}{{ number_format($row->total, 2) }}</td>
                            <td class="text-right font-mono text-green-600">{{ $currency }}{{ number_format($row->collected, 2) }}</td>
                            <td class="text-right font-mono text-red-500">{{ $currency }}{{ number_format(max(0, $row->outstanding), 2) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="font-semibold border-t-2 border-gray-200 dark:border-gray-700">
                        <td>Total</td>
                        <td class="text-right font-mono">{{ number_format($statusBreakdown->sum('count')) }}</td>
                        <td class="text-right font-mono">{{ $currency }}{{ number_format($totalBilled, 2) }}</td>
                        <td class="text-right font-mono text-green-600">{{ $currency }}{{ number_format($totalCollected, 2) }}</td>
                        <td class="text-right font-mono text-red-500">{{ $currency }}{{ number_format($totalOutstanding, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @if ($totalDiscount > 0 || $totalScholarship > 0)
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex gap-6 text-sm text-muted">
                @if ($totalDiscount > 0)
                    <span>Discounts: <strong class="text-gray-700 dark:text-gray-300">{{ $currency }}{{ number_format($totalDiscount, 2) }}</strong></span>
                @endif
                @if ($totalScholarship > 0)
                    <span>Scholarships: <strong class="text-gray-700 dark:text-gray-300">{{ $currency }}{{ number_format($totalScholarship, 2) }}</strong></span>
                @endif
            </div>
            @endif
        @endif
    </div>

    {{-- Payment method breakdown --}}
    <div class="card">
        <h2 class="card-title mb-4">Payment Methods</h2>
        @if ($methodBreakdown->isEmpty())
            <p class="text-muted text-sm">No payments recorded.</p>
        @else
            <div class="space-y-3">
                @php $methodTotal = $methodBreakdown->sum('total'); @endphp
                @foreach ($methodBreakdown as $method)
                @php
                    $pct = $methodTotal > 0 ? round(($method->total / $methodTotal) * 100) : 0;
                    $label = \App\Models\Invoice::METHODS[$method->payment_method] ?? ucfirst($method->payment_method);
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $label }}</span>
                        <span class="font-mono text-muted">{{ $currency }}{{ number_format($method->total, 2) }} <span class="text-xs">({{ $pct }}%)</span></span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-2 rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-muted mt-0.5">{{ number_format($method->count) }} {{ Str::plural('payment', $method->count) }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Monthly collections --}}
    <div class="card">
        <h2 class="card-title mb-4">Monthly Collections</h2>
        @if ($monthlyCollections->isEmpty())
            <p class="text-muted text-sm">No payment data.</p>
        @else
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Payments</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthlyCollections as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('M Y') }}</td>
                            <td class="text-right">{{ number_format($row->count) }}</td>
                            <td class="text-right font-mono font-semibold text-green-600">{{ $currency }}{{ number_format($row->total, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold border-t-2 border-gray-200 dark:border-gray-700">
                            <td>Total</td>
                            <td class="text-right">{{ number_format($monthlyCollections->sum('count')) }}</td>
                            <td class="text-right font-mono text-green-600">{{ $currency }}{{ number_format($monthlyCollections->sum('total'), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Expenses --}}
    <div class="card">
        <h2 class="card-title mb-4">Expenses by Category</h2>
        <div class="flex justify-between items-center mb-4 text-sm">
            <span class="text-muted">Total expenses</span>
            <span class="font-mono font-bold text-red-500">{{ $currency }}{{ number_format($totalExpenses, 2) }}</span>
        </div>
        <div class="mb-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <span class="text-sm font-semibold">Net Income (Collected − Expenses)</span>
            <span class="font-mono font-bold {{ $netIncome >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $currency }}{{ number_format(abs($netIncome), 2) }}
                {{ $netIncome < 0 ? '(deficit)' : '' }}
            </span>
        </div>
        @if ($expenseByCategory->isEmpty())
            <p class="text-muted text-sm">No expenses recorded for this period.</p>
        @else
            <div class="space-y-2">
                @foreach ($expenseByCategory as $exp)
                <div class="flex justify-between items-center text-sm py-1.5 border-b border-gray-50 dark:border-gray-800 last:border-0">
                    <span>{{ \App\Models\Expense::CATEGORIES[$exp->category] ?? ucfirst($exp->category) }}</span>
                    <span class="font-mono text-muted">{{ $currency }}{{ number_format($exp->total, 2) }}</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

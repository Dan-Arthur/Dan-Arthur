@extends('layouts.app')
@section('title', $payrollRun->title)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $payrollRun->title }}</h1>
        <p class="page-subtitle">
            <span class="badge {{ $payrollRun->status_color }}">{{ $payrollRun->status_label }}</span>
            &nbsp;·&nbsp; Run by {{ $payrollRun->runner->name ?? '—' }}
            on {{ $payrollRun->created_at->format('d M Y') }}
        </p>
    </div>
    <div class="flex gap-2">
        @can('manage payroll')
            @if ($payrollRun->status === 'draft')
            <form method="POST" action="{{ route('payroll.approve', $payrollRun) }}">
                @csrf @method('PATCH')
                <button class="btn btn-secondary">Approve Payroll</button>
            </form>
            @elseif ($payrollRun->status === 'approved')
            <form method="POST" action="{{ route('payroll.mark-paid', $payrollRun) }}">
                @csrf @method('PATCH')
                <button class="btn btn-primary">Mark as Paid + Send SMS</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('payroll.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

{{-- Summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card text-center">
        <p class="text-2xl font-bold">{{ $payslips->count() }}</p>
        <p class="text-sm text-muted mt-1">Employees</p>
    </div>
    <div class="card text-center">
        <p class="text-2xl font-bold font-mono">{{ $currency }}{{ number_format($payrollRun->total_gross, 2) }}</p>
        <p class="text-sm text-muted mt-1">Total Gross</p>
    </div>
    <div class="card text-center">
        <p class="text-2xl font-bold font-mono text-red-600">{{ $currency }}{{ number_format($payrollRun->total_deductions, 2) }}</p>
        <p class="text-sm text-muted mt-1">Deductions</p>
    </div>
    <div class="card text-center">
        <p class="text-2xl font-bold font-mono text-green-700">{{ $currency }}{{ number_format($payrollRun->total_net, 2) }}</p>
        <p class="text-sm text-muted mt-1">Net Pay</p>
    </div>
</div>

{{-- Tabs by staff type --}}
<div x-data="{ tab: 'all' }" class="space-y-4">
    <div class="flex gap-2 border-b border-border pb-1">
        <button @click="tab='all'" :class="tab==='all' ? 'tab-active' : 'tab'" class="tab">All Staff ({{ $payslips->count() }})</button>
        @foreach ($byType as $type => $group)
        <button @click="tab='{{ $type }}'" :class="tab==='{{ $type }}' ? 'tab-active' : 'tab'" class="tab">
            {{ \App\Models\Position::TYPES[$type] ?? ucfirst($type) }} ({{ $group->count() }})
        </button>
        @endforeach
    </div>

    @foreach (['all'] + $byType->keys()->toArray() as $tabKey)
    @php
        $rows = $tabKey === 'all' ? $payslips : ($byType[$tabKey] ?? collect());
    @endphp
    <div x-show="tab==='{{ $tabKey }}'" x-cloak>
        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th class="text-right">Basic</th>
                        <th class="text-right">Allowances</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right font-semibold">Net Pay</th>
                        <th>Status</th>
                        @if ($payrollRun->status !== 'paid')
                        <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $slip)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $slip->employee->full_name }}</p>
                            <p class="text-xs text-muted">{{ $slip->employee->employee_number }} · {{ $slip->employee->department?->name }}</p>
                        </td>
                        <td class="text-sm">
                            <span class="badge badge-gray">
                                {{ \App\Models\Position::TYPES[$slip->employee->position?->type ?? ''] ?? '—' }}
                            </span>
                        </td>
                        <td class="text-right font-mono text-sm">{{ $currency }}{{ number_format($slip->basic_salary, 2) }}</td>
                        <td class="text-right font-mono text-sm text-blue-600">{{ $currency }}{{ number_format(array_sum($slip->allowances ?? []), 2) }}</td>
                        <td class="text-right font-mono text-sm">{{ $currency }}{{ number_format($slip->gross_pay, 2) }}</td>
                        <td class="text-right font-mono text-sm text-red-600">{{ $currency }}{{ number_format($slip->total_deductions, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-green-700">{{ $currency }}{{ number_format($slip->net_pay, 2) }}</td>
                        <td>
                            <span class="badge {{ $slip->status === 'paid' ? 'badge-green' : 'badge-gray' }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </td>
                        @if ($payrollRun->status !== 'paid')
                        <td>
                            <a href="{{ route('payroll.payslip', $slip) }}" class="btn btn-xs btn-ghost">Edit</a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    <tr class="font-semibold bg-surface-alt">
                        <td colspan="2" class="text-muted text-sm">Subtotals</td>
                        <td class="text-right font-mono">{{ $currency }}{{ number_format($rows->sum('basic_salary'), 2) }}</td>
                        <td class="text-right font-mono text-blue-600">{{ $currency }}{{ number_format($rows->sum(fn($s) => array_sum($s->allowances ?? [])), 2) }}</td>
                        <td class="text-right font-mono">{{ $currency }}{{ number_format($rows->sum('gross_pay'), 2) }}</td>
                        <td class="text-right font-mono text-red-600">{{ $currency }}{{ number_format($rows->sum('total_deductions'), 2) }}</td>
                        <td class="text-right font-mono text-green-700">{{ $currency }}{{ number_format($rows->sum('net_pay'), 2) }}</td>
                        <td colspan="{{ $payrollRun->status !== 'paid' ? 2 : 1 }}"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

@if ($payrollRun->notes)
<div class="card mt-4">
    <p class="text-xs text-muted uppercase tracking-wider mb-1">Notes</p>
    <p class="text-sm">{{ $payrollRun->notes }}</p>
</div>
@endif
@endsection

@extends('layouts.app')
@section('title', 'Payroll')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Payroll</h1>
        <p class="page-subtitle">Monthly salary runs for all staff</p>
    </div>
    @can('manage payroll')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Payroll Run
    </a>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if ($runs->isEmpty())
    <div class="card text-center py-12 text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="font-medium">No payroll runs yet</p>
        <p class="text-sm mt-1">
            <a href="{{ route('payroll.create') }}" class="link">Create your first payroll run</a> to get started.
        </p>
    </div>
@else
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="text-right">Staff</th>
                    <th class="text-right">Gross Pay</th>
                    <th class="text-right">Deductions</th>
                    <th class="text-right">Net Pay</th>
                    <th>Status</th>
                    <th>Run By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($runs as $run)
                <tr>
                    <td class="font-medium">{{ $run->period_label }}</td>
                    <td class="text-right">{{ $run->payslips_count }}</td>
                    <td class="text-right font-mono">{{ auth()->user()->school->currency_symbol ?? '₵' }}{{ number_format($run->total_gross, 2) }}</td>
                    <td class="text-right font-mono text-red-600">{{ auth()->user()->school->currency_symbol ?? '₵' }}{{ number_format($run->total_deductions, 2) }}</td>
                    <td class="text-right font-mono font-semibold text-green-700">{{ auth()->user()->school->currency_symbol ?? '₵' }}{{ number_format($run->total_net, 2) }}</td>
                    <td><span class="badge {{ $run->status_color }}">{{ $run->status_label }}</span></td>
                    <td class="text-sm text-muted">{{ $run->runner->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('payroll.show', $run) }}" class="btn btn-xs btn-ghost">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $runs->links() }}</div>
@endif
@endsection

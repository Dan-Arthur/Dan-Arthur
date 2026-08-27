@extends('layouts.app')

@section('title', 'Payment ' . $payment->payment_number)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $payment->payment_number }}</h1>
        <p class="page-subtitle">Payment Receipt</p>
    </div>
    <div class="flex gap-3">
        @can('record payments')
        @if ($payment->status === 'confirmed')
        <form method="POST" action="{{ route('payments.reverse', $payment) }}"
              onsubmit="return confirm('Reverse this payment? The invoice balance will be updated.')">
            @csrf @method('PATCH')
            <button class="btn btn-ghost text-danger">Reverse</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('payments.receipt-pdf', $payment) }}" class="btn btn-ghost" target="_blank">Download Receipt</a>
        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-ghost">View Invoice</a>
        <a href="{{ route('payments.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="max-w-2xl space-y-6">
    {{-- Receipt card --}}
    @if ($payment->receipt)
    <div class="card border-2 border-success/30 bg-success/5">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="font-semibold text-success">Receipt Issued</h2>
        </div>
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div>
                <span class="text-muted">Receipt Number</span>
                <p class="font-mono font-semibold">{{ $payment->receipt->receipt_number }}</p>
            </div>
            <div>
                <span class="text-muted">Issued At</span>
                <p>{{ $payment->receipt->issued_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <span class="text-muted">Issued By</span>
                <p>{{ $payment->receipt->issuedBy?->name ?? '—' }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Payment details --}}
    <div class="card">
        <h2 class="card-title mb-4">Payment Details</h2>

        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-3xl font-bold font-mono">{{ number_format($payment->amount, 2) }}</p>
                <p class="text-muted text-sm">{{ $payment->method_label }}</p>
            </div>
            <span class="badge text-base {{ $payment->status_color }}">{{ $payment->status_label }}</span>
        </div>

        <dl class="detail-list">
            <dt>Payment Number</dt>
            <dd class="font-mono">{{ $payment->payment_number }}</dd>

            <dt>Student</dt>
            <dd>
                <a href="{{ route('students.show', $payment->student) }}" class="text-primary hover:underline">
                    {{ $payment->student->full_name }}
                </a>
                <span class="text-muted text-sm">({{ $payment->student->admission_number }})</span>
            </dd>

            <dt>Invoice</dt>
            <dd>
                <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-primary hover:underline font-mono">
                    {{ $payment->invoice->invoice_number }}
                </a>
            </dd>

            <dt>Payment Date</dt>
            <dd>{{ $payment->payment_date->format('d M Y') }}</dd>

            <dt>Method</dt>
            <dd>{{ $payment->method_label }}</dd>

            @if ($payment->reference_number)
            <dt>Reference</dt>
            <dd class="font-mono">{{ $payment->reference_number }}</dd>
            @endif

            @if ($payment->bank_name)
            <dt>Bank</dt>
            <dd>{{ $payment->bank_name }}</dd>
            @endif

            <dt>Received By</dt>
            <dd>{{ $payment->receivedBy?->name ?? '—' }}</dd>

            @if ($payment->notes)
            <dt>Notes</dt>
            <dd>{{ $payment->notes }}</dd>
            @endif
        </dl>
    </div>

    {{-- Invoice items for context --}}
    <div class="card">
        <h2 class="card-title mb-4">Invoice Items</h2>
        <div class="overflow-x-auto">
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payment->invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right font-mono">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t-2 border-border">
                        <td>Invoice Total</td>
                        <td class="text-right font-mono">{{ number_format($payment->invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-success">This Payment</td>
                        <td class="text-right font-mono text-success">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr class="{{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }} font-bold">
                        <td>Remaining Balance</td>
                        <td class="text-right font-mono">{{ number_format($payment->invoice->balance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

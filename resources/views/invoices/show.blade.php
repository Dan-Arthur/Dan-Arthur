@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
        <p class="page-subtitle">Invoice for {{ $invoice->student->full_name }}</p>
    </div>
    <div class="flex gap-3">
        @can('record payments')
        @if (!in_array($invoice->status, ['paid', 'cancelled']))
        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-primary">Record Payment</a>
        @endif
        @endcan
        @can('edit invoices')
        @if (!in_array($invoice->status, ['paid', 'cancelled']))
        <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Cancel this invoice?')">
            @csrf @method('PATCH')
            <button class="btn btn-ghost text-danger">Cancel Invoice</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-ghost" target="_blank">Download PDF</a>
        <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: invoice details --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Invoice header card --}}
        <div class="card">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-bold">INVOICE</h2>
                    <p class="font-mono text-muted">{{ $invoice->invoice_number }}</p>
                </div>
                <span class="badge text-sm {{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                <div>
                    <p class="text-muted mb-1">Bill To</p>
                    <p class="font-semibold">{{ $invoice->student->full_name }}</p>
                    <p class="text-muted">{{ $invoice->student->admission_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-muted mb-1">Issue Date</p>
                    <p class="font-semibold">{{ $invoice->issue_date->format('d M Y') }}</p>
                    @if ($invoice->due_date)
                    <p class="text-muted mt-2 mb-1">Due Date</p>
                    <p class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger font-semibold' : 'font-semibold' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </p>
                    @endif
                </div>
            </div>

            <div class="text-sm text-muted mb-4">
                {{ $invoice->academicYear->name }} @if ($invoice->term) &bull; {{ $invoice->term->name }} @endif
                @if ($invoice->feeStructure) &bull; {{ $invoice->feeStructure->name }} @endif
            </div>

            {{-- Line items --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-muted border-b border-border">
                            <th class="pb-2 font-medium">Description</th>
                            <th class="pb-2 font-medium text-right">Unit Price</th>
                            <th class="pb-2 font-medium text-right">Qty</th>
                            <th class="pb-2 font-medium text-right">Discount</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                        <tr class="border-b border-border/50">
                            <td class="py-2">
                                {{ $item->description }}
                                @if ($item->feeCategory)
                                    <span class="text-xs text-muted">&bull; {{ $item->feeCategory->name }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-right font-mono">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 text-right">{{ $item->quantity }}</td>
                            <td class="py-2 text-right font-mono text-danger">
                                {{ $item->discount > 0 ? '- ' . number_format($item->discount, 2) : '—' }}
                            </td>
                            <td class="py-2 text-right font-mono font-medium">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-sm">
                        <tr>
                            <td colspan="4" class="pt-3 text-right text-muted">Subtotal</td>
                            <td class="pt-3 text-right font-mono">{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if ($invoice->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="pt-1 text-right text-muted">Discount</td>
                            <td class="pt-1 text-right font-mono text-danger">- {{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if ($invoice->scholarship_amount > 0)
                        <tr>
                            <td colspan="4" class="pt-1 text-right text-muted">Scholarship</td>
                            <td class="pt-1 text-right font-mono text-danger">- {{ number_format($invoice->scholarship_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-t-2 border-border font-semibold text-base">
                            <td colspan="4" class="pt-3 text-right">Total Due</td>
                            <td class="pt-3 text-right font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="pt-1 text-right text-muted">Amount Paid</td>
                            <td class="pt-1 text-right font-mono text-success">{{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                        <tr class="font-bold text-lg {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            <td colspan="4" class="pt-2 text-right">Balance</td>
                            <td class="pt-2 text-right font-mono">{{ number_format($invoice->balance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($invoice->notes)
            <div class="mt-4 pt-4 border-t border-border text-sm text-muted">
                <span class="font-medium">Notes:</span> {{ $invoice->notes }}
            </div>
            @endif
        </div>

        {{-- Payments --}}
        <div class="card">
            <h2 class="card-title mb-4">Payment History ({{ $invoice->payments->count() }})</h2>
            @if ($invoice->payments->isEmpty())
                <p class="text-muted text-sm">No payments recorded yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th>Receipt</th>
                                @can('record payments')
                                <th></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $payment)
                            <tr>
                                <td class="font-mono text-sm">
                                    <a href="{{ route('payments.show', $payment) }}" class="text-primary hover:underline">
                                        {{ $payment->payment_number }}
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
                                @can('record payments')
                                <td>
                                    @if ($payment->status === 'confirmed')
                                    <form method="POST" action="{{ route('payments.reverse', $payment) }}"
                                          onsubmit="return confirm('Reverse this payment?')">
                                        @csrf @method('PATCH')
                                        <button class="action-link text-danger text-xs">Reverse</button>
                                    </form>
                                    @endif
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">
        <div class="card">
            <h3 class="font-semibold mb-3">Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted">Total</span>
                    <span class="font-mono font-semibold">{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Paid</span>
                    <span class="font-mono text-success">{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-border pt-2 mt-2">
                    <span class="font-semibold">Balance</span>
                    <span class="font-mono font-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($invoice->balance, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card text-sm space-y-2">
            <h3 class="font-semibold mb-2">Details</h3>
            <div><span class="text-muted">Created by:</span> {{ $invoice->createdBy?->name ?? '—' }}</div>
            <div><span class="text-muted">Created at:</span> {{ $invoice->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>
</div>
@endsection

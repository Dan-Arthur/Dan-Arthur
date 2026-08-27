@extends('layouts.portal')
@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('portal.child', $invoice->student_id) }}" class="text-gray-400 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
        <p class="text-sm text-gray-500">Invoice for {{ $invoice->student->full_name }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-5">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <p class="text-xs text-gray-500 mb-1">Issue Date</p>
            <p class="font-semibold">{{ $invoice->issue_date->format('d M Y') }}</p>
            @if ($invoice->due_date)
                <p class="text-xs text-gray-500 mt-2 mb-1">Due Date</p>
                <p class="font-semibold {{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-red-600' : '' }}">
                    {{ $invoice->due_date->format('d M Y') }}
                </p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 mb-1">Period</p>
            <p class="font-semibold">{{ $invoice->academicYear->name ?? '—' }}</p>
            @if ($invoice->term)<p class="text-sm text-gray-500">{{ $invoice->term->name }}</p>@endif
            <div class="mt-2">
                @php $sc = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-red-100 text-red-700','partial'=>'bg-blue-100 text-blue-700','overdue'=>'bg-red-100 text-red-800','cancelled'=>'bg-gray-100 text-gray-600'][$invoice->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $sc }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800 text-left">
                    <th class="pb-2 font-medium text-gray-500 text-xs uppercase">Description</th>
                    <th class="pb-2 font-medium text-gray-500 text-xs uppercase text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach ($invoice->items as $item)
                <tr>
                    <td class="py-2.5 text-gray-700 dark:text-gray-300">{{ $item->description }}</td>
                    <td class="py-2.5 text-right font-mono">{{ $currency }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="text-sm border-t border-gray-200 dark:border-gray-700">
                @if ($invoice->discount_amount > 0)
                <tr>
                    <td class="pt-2 text-gray-500">Discount</td>
                    <td class="pt-2 text-right font-mono text-red-600">- {{ $currency }}{{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if ($invoice->scholarship_amount > 0)
                <tr>
                    <td class="pt-1 text-gray-500">Scholarship</td>
                    <td class="pt-1 text-right font-mono text-red-600">- {{ $currency }}{{ number_format($invoice->scholarship_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="font-semibold text-base">
                    <td class="pt-3">Total</td>
                    <td class="pt-3 text-right font-mono">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr class="text-green-600">
                    <td class="pt-1">Paid</td>
                    <td class="pt-1 text-right font-mono">{{ $currency }}{{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                <tr class="font-bold text-lg {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    <td class="pt-2 border-t border-gray-200 dark:border-gray-700">Balance</td>
                    <td class="pt-2 text-right font-mono border-t border-gray-200 dark:border-gray-700">{{ $currency }}{{ number_format($invoice->balance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($invoice->notes)
    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-sm text-gray-500">
        <strong>Notes:</strong> {{ $invoice->notes }}
    </div>
    @endif
</div>

{{-- Payments --}}
@if ($invoice->payments->isNotEmpty())
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
    <h2 class="font-bold text-gray-900 dark:text-white mb-4">Payment History</h2>
    <div class="space-y-2">
        @foreach ($invoice->payments->where('status', 'confirmed') as $pay)
        <div class="flex justify-between items-center text-sm py-2 border-b border-gray-50 dark:border-gray-800 last:border-0">
            <div>
                <p class="font-medium">{{ $pay->payment_date->format('d M Y') }}</p>
                <p class="text-xs text-gray-500">{{ $pay->method_label }} @if ($pay->reference_number) &bull; Ref: {{ $pay->reference_number }} @endif</p>
            </div>
            <p class="font-mono font-semibold text-green-600">{{ $currency }}{{ number_format($pay->amount, 2) }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

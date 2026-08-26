@extends('layouts.app')

@section('title', $fee->name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $fee->name }}</h1>
        <p class="page-subtitle">Fee Structure Details</p>
    </div>
    <div class="flex gap-3">
        @can('manage fee structures')
        <a href="{{ route('fees.edit', $fee) }}" class="btn btn-ghost">Edit</a>
        @endcan
        <a href="{{ route('fees.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Details sidebar --}}
    <div class="card">
        <h2 class="card-title mb-4">Details</h2>
        <dl class="detail-list">
            <dt>Academic Year</dt>
            <dd>{{ $fee->academicYear->name }}</dd>

            <dt>Term</dt>
            <dd>{{ $fee->term?->name ?? 'All Terms' }}</dd>

            <dt>Class</dt>
            <dd>{{ $fee->applies_to_all_classes ? 'All Classes' : ($fee->schoolClass?->name ?? '—') }}</dd>

            @if ($fee->campus)
            <dt>Campus</dt>
            <dd>{{ $fee->campus->name }}</dd>
            @endif

            @if ($fee->student_category)
            <dt>Student Category</dt>
            <dd>{{ $fee->student_category }}</dd>
            @endif

            <dt>Status</dt>
            <dd>
                @if ($fee->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-gray">Inactive</span>
                @endif
            </dd>

            <dt>Total Amount</dt>
            <dd class="font-semibold text-lg">{{ number_format($fee->total, 2) }}</dd>
        </dl>

        @can('create invoices')
        <div class="mt-4 pt-4 border-t border-border">
            <a href="{{ route('invoices.create', ['fee_structure_id' => $fee->id]) }}" class="btn btn-primary w-full">
                Generate Invoice from This Structure
            </a>
        </div>
        @endcan
    </div>

    {{-- Items table --}}
    <div class="lg:col-span-2 card">
        <h2 class="card-title mb-4">Fee Items ({{ $fee->items->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th>Mandatory</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fee->items as $item)
                    <tr>
                        <td class="font-medium">{{ $item->name }}</td>
                        <td>{{ $item->feeCategory?->name ?? '—' }}</td>
                        <td class="text-right font-mono">{{ number_format($item->amount, 2) }}</td>
                        <td>
                            @if ($item->is_mandatory)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-gray">Optional</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t-2 border-border">
                        <td colspan="2">Total</td>
                        <td class="text-right font-mono">{{ number_format($fee->total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

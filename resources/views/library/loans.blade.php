@extends('layouts.app')

@section('title', 'Library Loans')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Active Loans</h1></div>
    <div class="flex gap-3">
        @can('issue books')
        <a href="{{ route('library.issue') }}" class="btn btn-primary">Issue Book</a>
        @endcan
        <a href="{{ route('library.index') }}" class="btn btn-ghost">Book Catalogue</a>
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <select name="status" class="form-select w-auto">
        <option value="">All Statuses</option>
        @foreach ($statuses as $key => $meta)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('library.loans') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($loans->isEmpty())
    <div class="empty-state">No loans found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Copy</th>
                    <th>Borrower</th>
                    <th>Loan Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Fine</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loans as $loan)
                <tr>
                    <td>
                        <a href="{{ route('library.books.show', $loan->bookCopy->book) }}" class="font-medium hover:underline">
                            {{ $loan->bookCopy->book->title }}
                        </a>
                    </td>
                    <td class="font-mono text-sm">{{ $loan->bookCopy->accession_number ?? '—' }}</td>
                    <td>
                        {{ $loan->member?->full_name ?? '—' }}
                        <span class="text-xs text-muted">({{ $loan->member_type }})</span>
                    </td>
                    <td>{{ $loan->loan_date->format('d M Y') }}</td>
                    <td>
                        <span class="{{ $loan->is_overdue ? 'text-danger font-semibold' : '' }}">
                            {{ $loan->due_date->format('d M Y') }}
                            @if ($loan->is_overdue)
                                <span class="text-xs">({{ $loan->days_overdue }}d late)</span>
                            @endif
                        </span>
                    </td>
                    <td><span class="badge {{ $loan->status_color }}">{{ $loan->status_label }}</span></td>
                    <td class="font-mono text-sm">
                        {{ $loan->fine_amount > 0 ? number_format($loan->fine_amount, 2) : '—' }}
                    </td>
                    <td class="table-actions">
                        @can('return books')
                        @if (in_array($loan->status, ['active', 'overdue']))
                        <form method="POST" action="{{ route('library.loans.return', $loan) }}"
                              x-data="{ open: false }">
                            @csrf @method('PATCH')
                            <button type="button" @click="open = true" class="action-link">Return</button>
                            <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
                                <div class="modal">
                                    <h2 class="modal-title">Return — {{ $loan->bookCopy->book->title }}</h2>
                                    <div class="form-group">
                                        <label class="form-label">Condition on Return</label>
                                        <select name="return_condition" class="form-select" required>
                                            @foreach (\App\Models\BookCopy::CONDITIONS as $k => $v)
                                                <option value="{{ $k }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Fine Collected</label>
                                        <input type="number" name="fine_paid" class="form-input" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="flex gap-3 mt-4">
                                        <button type="submit" class="btn btn-primary">Confirm</button>
                                        <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $loans->links() }}</div>
@endif
@endsection

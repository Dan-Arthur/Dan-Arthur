@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">{{ $book->title }}</h1>
        <p class="page-subtitle">{{ $book->author_names }}</p>
    </div>
    <div class="flex gap-3">
        @can('issue books')
        @if ($book->available_copies > 0)
        <a href="{{ route('library.issue') }}?book_id={{ $book->id }}" class="btn btn-primary">Issue Copy</a>
        @endif
        @endcan
        <a href="{{ route('library.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card">
        <h2 class="card-title mb-4">Book Info</h2>
        <dl class="detail-list">
            <dt>Category</dt><dd>{{ $book->category?->name ?? '—' }}</dd>
            <dt>Publisher</dt><dd>{{ $book->publisher?->name ?? '—' }}</dd>
            <dt>ISBN</dt><dd class="font-mono">{{ $book->isbn ?? '—' }}</dd>
            <dt>Edition</dt><dd>{{ $book->edition ?? '—' }}</dd>
            <dt>Year</dt><dd>{{ $book->publish_year ?? '—' }}</dd>
            <dt>Language</dt><dd>{{ $book->language }}</dd>
            <dt>Location</dt><dd>{{ $book->location ?? '—' }}</dd>
            <dt>Total Copies</dt><dd>{{ $book->total_copies }}</dd>
            <dt>Available</dt>
            <dd class="{{ $book->available_copies > 0 ? 'text-success font-semibold' : 'text-danger font-semibold' }}">
                {{ $book->available_copies }}
            </dd>
        </dl>
    </div>

    <div class="lg:col-span-2 card">
        <h2 class="card-title mb-4">Copies ({{ $book->copies->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="data-table text-sm">
                <thead>
                    <tr>
                        <th>Accession #</th>
                        <th>Barcode</th>
                        <th>Condition</th>
                        <th>Status</th>
                        <th>Borrower</th>
                        <th>Due</th>
                        @can('return books')
                        <th></th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($book->copies as $copy)
                    <tr>
                        <td class="font-mono">{{ $copy->accession_number ?? '—' }}</td>
                        <td class="font-mono">{{ $copy->barcode ?? '—' }}</td>
                        <td>{{ \App\Models\BookCopy::CONDITIONS[$copy->condition] ?? $copy->condition }}</td>
                        <td>
                            <span class="badge {{ \App\Models\BookCopy::STATUSES[$copy->status]['color'] ?? 'badge-gray' }}">
                                {{ \App\Models\BookCopy::STATUSES[$copy->status]['label'] ?? $copy->status }}
                            </span>
                        </td>
                        <td>
                            @if ($copy->activeLoan)
                                {{ $copy->activeLoan->member?->full_name ?? '—' }}
                                <span class="text-xs text-muted">({{ $copy->activeLoan->member_type }})</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($copy->activeLoan)
                                <span class="{{ $copy->activeLoan->is_overdue ? 'text-danger font-medium' : '' }}">
                                    {{ $copy->activeLoan->due_date->format('d M Y') }}
                                </span>
                            @else —
                            @endif
                        </td>
                        @can('return books')
                        <td>
                            @if ($copy->activeLoan)
                            <button type="button" class="action-link"
                                    x-data x-on:click="$dispatch('open-return', {{ $copy->activeLoan->id }})">
                                Return
                            </button>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Return modal --}}
@can('return books')
<div x-data="{ open: false, loanId: null }" x-on:open-return.window="open = true; loanId = $event.detail">
    <div x-show="open" x-cloak class="modal-backdrop" @click.self="open = false">
        <div class="modal">
            <h2 class="modal-title">Return Book</h2>
            <form :action="`/library/loans/${loanId}/return`" method="POST">
                @csrf @method('PATCH')
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
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

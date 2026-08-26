@extends('layouts.app')

@section('title', 'Library')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Library</h1>
        <p class="page-subtitle">Book catalogue and loans</p>
    </div>
    <div class="flex gap-3">
        @can('view library')
        <a href="{{ route('library.loans') }}" class="btn btn-ghost">Loans</a>
        @endcan
        @can('manage books')
        <a href="{{ route('library.catalogue') }}" class="btn btn-ghost">Catalogue</a>
        <a href="{{ route('library.books.create') }}" class="btn btn-primary">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Book
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="filter-bar mb-6">
    <input type="text" name="search" class="form-input flex-1" placeholder="Title, ISBN, author…"
           value="{{ request('search') }}">
    <select name="category_id" class="form-select w-auto">
        <option value="">All Categories</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm whitespace-nowrap">
        <input type="checkbox" name="available" value="1" {{ request('available') ? 'checked' : '' }} class="form-checkbox">
        Available only
    </label>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('library.index') }}" class="btn btn-ghost">Reset</a>
</form>

@if ($books->isEmpty())
    <div class="empty-state">No books found.</div>
@else
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author(s)</th>
                    <th>Category</th>
                    <th>ISBN</th>
                    <th class="text-right">Copies</th>
                    <th class="text-right">Available</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($books as $book)
                <tr>
                    <td>
                        <a href="{{ route('library.books.show', $book) }}" class="font-medium hover:underline">
                            {{ $book->title }}
                        </a>
                        @if ($book->edition)
                            <span class="text-xs text-muted">({{ $book->edition }})</span>
                        @endif
                    </td>
                    <td class="text-sm">{{ $book->author_names }}</td>
                    <td>{{ $book->category?->name ?? '—' }}</td>
                    <td class="font-mono text-sm">{{ $book->isbn ?? '—' }}</td>
                    <td class="text-right">{{ $book->total_copies }}</td>
                    <td class="text-right">
                        <span class="{{ $book->available_copies > 0 ? 'text-success font-semibold' : 'text-danger' }}">
                            {{ $book->available_copies }}
                        </span>
                    </td>
                    <td class="table-actions">
                        <a href="{{ route('library.books.show', $book) }}" class="action-link">View</a>
                        @can('issue books')
                        @if ($book->available_copies > 0)
                        <a href="{{ route('library.issue') }}?book_id={{ $book->id }}" class="action-link">Issue</a>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $books->links() }}</div>
@endif
@endsection

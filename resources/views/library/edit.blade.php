@extends('layouts.app')

@section('title', 'Edit Book')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Book</h1>
        <p class="page-subtitle">{{ $book->title }}</p>
    </div>
    <a href="{{ route('library.books.show', $book) }}" class="btn btn-ghost">Cancel</a>
</div>

<form method="POST" action="{{ route('library.books.update', $book) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')

    <div class="card">
        <h2 class="card-title mb-4">Book Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $book->title) }}" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-input" value="{{ old('isbn', $book->isbn) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Edition</label>
                <input type="text" name="edition" class="form-input" value="{{ old('edition', $book->edition) }}" placeholder="e.g. 3rd">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Publisher</label>
                <select name="publisher_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($publishers as $pub)
                        <option value="{{ $pub->id }}" {{ old('publisher_id', $book->publisher_id) == $pub->id ? 'selected' : '' }}>{{ $pub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Publish Year</label>
                <input type="number" name="publish_year" class="form-input" value="{{ old('publish_year', $book->publish_year) }}"
                       min="1800" max="{{ date('Y') + 1 }}">
            </div>
            <div class="form-group">
                <label class="form-label">Language</label>
                <input type="text" name="language" class="form-input" value="{{ old('language', $book->language) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Shelf / Location</label>
                <input type="text" name="location" class="form-input" value="{{ old('location', $book->location) }}" placeholder="e.g. Shelf A2">
            </div>
            <div class="sm:col-span-2 form-group">
                <label class="form-label">Authors</label>
                <div class="flex flex-wrap gap-2">
                    @php $selectedAuthors = old('author_ids', $book->authors->pluck('id')->toArray()); @endphp
                    @foreach ($authors as $author)
                    <label class="flex items-center gap-2 text-sm border border-border rounded px-3 py-1 cursor-pointer hover:bg-surface-hover">
                        <input type="checkbox" name="author_ids[]" value="{{ $author->id }}"
                               {{ in_array($author->id, $selectedAuthors) ? 'checked' : '' }}
                               class="form-checkbox">
                        {{ $author->name }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Update Book</button>
        <a href="{{ route('library.books.show', $book) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection

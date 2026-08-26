@extends('layouts.app')

@section('title', 'Library Catalogue')

@section('content')
<div class="content-header">
    <div><h1 class="page-title">Library Catalogue</h1></div>
    <a href="{{ route('library.index') }}" class="btn btn-ghost">Back to Books</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add form --}}
    <div class="card">
        <h2 class="card-title mb-4">Add Entry</h2>
        <form method="POST" action="{{ route('library.catalogue.store') }}" x-data="{ type: 'category' }">
            @csrf
            <div class="form-group">
                <label class="form-label">Type</label>
                <select x-model="type" class="form-select">
                    <option value="category">Category</option>
                    <option value="author">Author</option>
                    <option value="publisher">Publisher</option>
                </select>
                <input type="hidden" name="type" :value="type">
            </div>
            <div class="form-group">
                <label class="form-label">Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div x-show="type === 'category'" class="form-group">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-input">
            </div>
            <div x-show="type === 'publisher'" class="form-group">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary w-full">Add</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-6">
        {{-- Categories --}}
        <div class="card">
            <h2 class="card-title mb-3">Categories ({{ $categories->count() }})</h2>
            @if ($categories->isEmpty())
                <p class="text-muted text-sm">None defined.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($categories as $cat)
                    <div class="badge badge-gray text-sm">
                        {{ $cat->name }}
                        @if ($cat->code) <span class="opacity-60">({{ $cat->code }})</span> @endif
                        <span class="ml-1 opacity-60">· {{ $cat->books_count }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Authors --}}
        <div class="card">
            <h2 class="card-title mb-3">Authors ({{ $authors->count() }})</h2>
            @if ($authors->isEmpty())
                <p class="text-muted text-sm">None defined.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead><tr><th>Name</th><th class="text-right">Books</th></tr></thead>
                        <tbody>
                            @foreach ($authors as $author)
                            <tr>
                                <td>{{ $author->name }}</td>
                                <td class="text-right">{{ $author->books_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Publishers --}}
        <div class="card">
            <h2 class="card-title mb-3">Publishers ({{ $publishers->count() }})</h2>
            @if ($publishers->isEmpty())
                <p class="text-muted text-sm">None defined.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead><tr><th>Name</th><th>Contact</th><th class="text-right">Books</th></tr></thead>
                        <tbody>
                            @foreach ($publishers as $pub)
                            <tr>
                                <td>{{ $pub->name }}</td>
                                <td>{{ $pub->contact ?? '—' }}</td>
                                <td class="text-right">{{ $pub->books_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

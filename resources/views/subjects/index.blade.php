@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Subjects</h1>
        <p class="page-subtitle">Manage the school's subject catalogue</p>
    </div>
    @can('create subjects')
    <a href="{{ route('subjects.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Subject
    </a>
    @endcan
</div>

{{-- Type summary chips --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}"
        class="px-3 py-1 rounded-full text-sm font-medium border transition-colors
            {{ !request('type') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white' : 'border-gray-300 text-gray-600 hover:border-gray-500 dark:border-gray-600 dark:text-gray-400' }}">
        All ({{ $typeCounts->sum() }})
    </a>
    @foreach(\App\Models\Subject::TYPES as $key => $info)
    <a href="{{ request()->fullUrlWithQuery(['type' => $key]) }}"
        class="px-3 py-1 rounded-full text-sm font-medium border transition-colors
            {{ request('type') === $key ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:border-blue-400 dark:border-gray-600 dark:text-gray-400' }}">
        {{ $info['label'] }} ({{ $typeCounts[$key] ?? 0 }})
    </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('subjects.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label text-xs">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-input text-sm" placeholder="Name or code…">
        </div>
        <div>
            <label class="form-label text-xs">Level</label>
            <select name="level" class="form-select text-sm">
                <option value="">All Levels</option>
                @foreach(\App\Models\Subject::LEVELS as $key => $label)
                <option value="{{ $key }}" @selected(request('level') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Category</label>
            <select name="category" class="form-select text-sm">
                <option value="">All Categories</option>
                @foreach(\App\Models\Subject::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if($departments->count() > 0)
        <div>
            <label class="form-label text-xs">Department</label>
            <select name="department_id" class="form-select text-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                <option value="active"   @selected(request('status','active')==='active')>Active</option>
                <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-3 py-2">Filter</button>
        @if(request()->hasAny(['search','level','category','department_id']))
        <a href="{{ route('subjects.index') }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    @if($subjects->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-gray-500 text-sm">No subjects found.</p>
        @can('create subjects')
        <a href="{{ route('subjects.create') }}" class="btn-primary mt-4 inline-flex">Add First Subject</a>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Level</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Department</th>
                    <th>Credits</th>
                    <th>Status</th>
                    <th class="w-24"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $subject->name }}</p>
                            @if($subject->has_practical)
                            <span class="badge badge-warning text-xs">Lab</span>
                            @endif
                        </div>
                    </td>
                    <td class="font-mono text-xs text-gray-500">{{ $subject->code }}</td>
                    <td class="text-xs text-gray-500">{{ $subject->level ? $subject->level_label : '—' }}</td>
                    <td><span class="badge {{ $subject->type_color }}">{{ $subject->type_label }}</span></td>
                    <td class="text-sm text-gray-500">{{ $subject->category_label ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $subject->department->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500 text-center">{{ $subject->credit_hours }}</td>
                    <td>
                        @can('edit subjects')
                        <form method="POST" action="{{ route('subjects.toggle-active', $subject) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="badge cursor-pointer {{ $subject->is_active ? 'badge-success' : 'badge-gray' }}">
                                {{ $subject->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        @else
                        <span class="badge {{ $subject->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @endcan
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('subjects.show', $subject) }}" class="icon-btn" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @can('edit subjects')
                            <a href="{{ route('subjects.edit', $subject) }}" class="icon-btn" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($subjects->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $subjects->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

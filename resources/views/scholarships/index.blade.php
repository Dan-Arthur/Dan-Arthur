@extends('layouts.app')
@section('title', 'Scholarships')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Scholarships</h1>
        <p class="page-subtitle">Manage scholarship programmes and student awards</p>
    </div>
    <a href="{{ route('scholarships.create') }}" class="btn-primary">+ New Scholarship</a>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

@if ($scholarships->isEmpty())
    <div class="card p-10 text-center text-gray-400 text-sm">
        No scholarships defined yet.
        <a href="{{ route('scholarships.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
    </div>
@else
<div class="card overflow-hidden">
    <table class="w-full text-sm data-table">
        <thead>
            <tr>
                <th class="text-left">Scholarship</th>
                <th class="text-left">Type</th>
                <th class="text-center">Value</th>
                <th class="text-center">Students</th>
                <th class="text-center">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($scholarships as $scholarship)
            <tr>
                <td>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $scholarship->name }}</p>
                    @if ($scholarship->description)
                        <p class="text-xs text-gray-400 truncate max-w-xs">{{ $scholarship->description }}</p>
                    @endif
                </td>
                <td>
                    <span class="badge badge-gray text-xs">{{ $scholarship->type_label }}</span>
                </td>
                <td class="text-center font-mono font-semibold text-gray-900 dark:text-white">
                    {{ $scholarship->value_display }}
                </td>
                <td class="text-center">
                    <a href="{{ route('scholarships.students', $scholarship) }}"
                        class="text-blue-600 hover:underline font-medium">
                        {{ $scholarship->student_scholarships_count }}
                        <span class="text-xs text-gray-400 font-normal">assigned</span>
                    </a>
                </td>
                <td class="text-center">
                    <span class="badge {{ $scholarship->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $scholarship->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('scholarships.students', $scholarship) }}" class="btn btn-xs btn-ghost">Assign</a>
                    <a href="{{ route('scholarships.edit', $scholarship) }}" class="btn btn-xs btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('scholarships.destroy', $scholarship) }}" class="inline"
                        onsubmit="return confirm('Delete this scholarship?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-ghost text-red-600">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

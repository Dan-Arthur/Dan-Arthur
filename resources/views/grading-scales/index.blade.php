@extends('layouts.app')
@section('title', 'Grading Scales')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Grading Scales</h1>
        <p class="page-subtitle">Define grade bands used when generating results</p>
    </div>
    <a href="{{ route('grading-scales.create') }}" class="btn-primary">+ New Scale</a>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

@if ($scales->isEmpty())
    <div class="card p-10 text-center text-gray-400 text-sm">
        No grading scales yet.
        <a href="{{ route('grading-scales.create') }}" class="text-blue-600 hover:underline">Create one now</a>
        — the default scale is applied automatically when generating results.
    </div>
@else
<div class="space-y-6">
    @foreach ($scales as $scale)
    <div class="card overflow-hidden">
        {{-- Scale header --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $scale->name }}</h2>
                @if ($scale->is_default)
                    <span class="badge badge-green">Default</span>
                @endif
                <span class="text-xs text-gray-400">{{ $scale->bands->count() }} grade band(s)</span>
            </div>
            <div class="flex gap-2 flex-wrap">
                @unless ($scale->is_default)
                <form method="POST" action="{{ route('grading-scales.set-default', $scale) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-xs btn-ghost">Set as Default</button>
                </form>
                @endunless
                <a href="{{ route('grading-scales.edit', $scale) }}" class="btn btn-xs btn-ghost">Edit</a>
                @unless ($scale->is_default)
                <form method="POST" action="{{ route('grading-scales.destroy', $scale) }}"
                    onsubmit="return confirm('Delete this grading scale? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-ghost text-red-600">Delete</button>
                </form>
                @endunless
            </div>
        </div>

        {{-- Bands table --}}
        @if ($scale->bands->isEmpty())
            <p class="px-6 py-4 text-sm text-gray-400 italic">No bands defined.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">Grade</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Min Score</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Max Score</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Range</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remark</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">GPA Point</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach ($scale->bands as $band)
                    @php
                        $isPass = $band->min_score >= 50;
                    @endphp
                    <tr>
                        <td class="px-6 py-2.5">
                            <span class="text-lg font-bold {{ $isPass ? 'text-gray-900 dark:text-white' : 'text-red-500' }}">
                                {{ $band->grade }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center font-mono text-sm text-gray-600 dark:text-gray-300">
                            {{ number_format($band->min_score, 1) }}
                        </td>
                        <td class="px-4 py-2.5 text-center font-mono text-sm text-gray-600 dark:text-gray-300">
                            {{ number_format($band->max_score, 1) }}
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                {{ number_format($band->min_score, 0) }}–{{ number_format($band->max_score, 0) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300">
                            {{ $band->remark ?: '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-sm font-mono text-gray-500">{{ $band->gpa_point }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

<div class="mt-6 card p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 text-sm text-blue-700 dark:text-blue-300">
    <strong>How grading scales work:</strong> The <em>default</em> scale is used automatically when generating results.
    Each student's subject score is matched against the grade bands to assign a grade and remark.
    You can create multiple scales (e.g. one for Primary, one for JHS) and switch the default before generating results.
</div>
@endsection

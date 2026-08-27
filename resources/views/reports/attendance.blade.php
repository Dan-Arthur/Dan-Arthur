@extends('layouts.app')

@section('title', 'Attendance Summary')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Attendance Summary</h1>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-ghost">← Reports</a>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar mb-6" x-data="{ yearId: '{{ $yearId }}' }">
    <select name="year_id" class="form-select w-44" x-model="yearId" @change="$el.form.submit()">
        <option value="">All Years</option>
        @foreach ($years as $yr)
            <option value="{{ $yr->id }}" {{ $yr->id == $yearId ? 'selected' : '' }}>{{ $yr->name }}</option>
        @endforeach
    </select>
    <select name="term_id" class="form-select w-36">
        <option value="">All Terms</option>
        @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
        @endforeach
    </select>
    <select name="class_id" class="form-select w-36">
        <option value="">All Classes</option>
        @foreach ($classes as $cls)
            <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('reports.attendance') }}" class="btn btn-ghost">Reset</a>
</form>

{{-- Overall rate --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card text-center py-5">
        <p class="text-3xl font-bold {{ $overallRate >= 90 ? 'text-green-600' : ($overallRate >= 75 ? 'text-yellow-500' : 'text-red-500') }}">
            {{ $overallRate }}%
        </p>
        <p class="text-sm text-muted mt-1">Overall Attendance Rate</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($overallTotal) }}</p>
        <p class="text-sm text-muted mt-1">Total Records</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-green-600">{{ number_format($overallPresent) }}</p>
        <p class="text-sm text-muted mt-1">Present / Late</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-red-500">{{ number_format($overallTotal - $overallPresent) }}</p>
        <p class="text-sm text-muted mt-1">Absent</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Class-by-class table --}}
    <div class="lg:col-span-2 card">
        <h2 class="card-title mb-4">By Class</h2>
        @if ($classSummary->isEmpty())
            <p class="text-muted text-sm">No attendance data for the selected period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th class="text-right">Records</th>
                            <th class="text-right">Present</th>
                            <th class="text-right">Late</th>
                            <th class="text-right">Absent</th>
                            <th class="text-right">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classSummary as $row)
                        <tr>
                            <td class="font-medium">{{ $row->class_name }}</td>
                            <td class="text-right">{{ number_format($row->total_records) }}</td>
                            <td class="text-right text-green-600">{{ number_format($row->present_count) }}</td>
                            <td class="text-right text-yellow-600">{{ number_format($row->late_count) }}</td>
                            <td class="text-right text-red-500">{{ number_format($row->absent_count) }}</td>
                            <td class="text-right">
                                <span class="font-semibold {{ $row->rate >= 90 ? 'text-green-600' : ($row->rate >= 75 ? 'text-yellow-500' : 'text-red-500') }}">
                                    {{ $row->rate }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-bold border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td>Total</td>
                            <td class="text-right">{{ number_format($overallTotal) }}</td>
                            <td class="text-right text-green-600">{{ number_format($classSummary->sum('present_count')) }}</td>
                            <td class="text-right text-yellow-600">{{ number_format($classSummary->sum('late_count')) }}</td>
                            <td class="text-right text-red-500">{{ number_format($classSummary->sum('absent_count')) }}</td>
                            <td class="text-right {{ $overallRate >= 90 ? 'text-green-600' : ($overallRate >= 75 ? 'text-yellow-500' : 'text-red-500') }}">
                                {{ $overallRate }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Most absent students --}}
    <div class="card">
        <h2 class="card-title mb-4">Most Absent Students</h2>
        @if ($mostAbsent->isEmpty())
            <p class="text-muted text-sm">No absence data.</p>
        @else
            <ol class="space-y-3">
                @foreach ($mostAbsent as $i => $row)
                <li class="flex items-center gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center
                        {{ $i === 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('students.show', $row->student_id) }}" class="text-sm font-medium truncate hover:text-blue-600 block">
                            {{ $row->student?->full_name ?? 'Unknown' }}
                        </a>
                        <p class="text-xs text-muted">{{ $row->student?->schoolClass?->name ?? '—' }}</p>
                    </div>
                    <span class="flex-shrink-0 text-sm font-bold text-red-500">{{ $row->absent_count }}d</span>
                </li>
                @endforeach
            </ol>
        @endif
    </div>

</div>
@endsection

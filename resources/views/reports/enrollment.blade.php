@extends('layouts.app')

@section('title', 'Enrollment Report')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Enrollment Report</h1>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-ghost">← Reports</a>
</div>

{{-- Year filter --}}
<form method="GET" class="filter-bar mb-6">
    <select name="year_id" class="form-select w-44" onchange="this.form.submit()">
        <option value="">All Years</option>
        @foreach ($years as $yr)
            <option value="{{ $yr->id }}" {{ $yr->id == $yearId ? 'selected' : '' }}>{{ $yr->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('reports.enrollment') }}" class="btn btn-ghost">Reset</a>
</form>

{{-- Totals --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-blue-600">{{ number_format($totalEnrolled) }}</p>
        <p class="text-sm text-muted mt-1">Total Enrolled</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-blue-400">{{ number_format($totalMale) }}</p>
        <p class="text-sm text-muted mt-1">Male</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-pink-500">{{ number_format($totalFemale) }}</p>
        <p class="text-sm text-muted mt-1">Female</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold {{ $unenrolled > 0 ? 'text-orange-500' : 'text-gray-400' }}">{{ number_format($unenrolled) }}</p>
        <p class="text-sm text-muted mt-1">Active but Unenrolled</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- By class --}}
    <div class="lg:col-span-2 card">
        <h2 class="card-title mb-4">Enrollment by Class</h2>
        @if ($byClass->isEmpty())
            <p class="text-muted text-sm">No enrollment data for the selected year.</p>
        @else
            <div class="overflow-x-auto">
                <table class="data-table text-sm">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Male</th>
                            <th class="text-right">Female</th>
                            <th>Gender Split</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byClass as $row)
                        @php
                            $mPct = $row->total > 0 ? round(($row->male_count / $row->total) * 100) : 0;
                            $fPct = 100 - $mPct;
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $row->class->name }}</td>
                            <td class="text-right font-bold">{{ number_format($row->total) }}</td>
                            <td class="text-right text-blue-500">{{ number_format($row->male_count) }}</td>
                            <td class="text-right text-pink-500">{{ number_format($row->female_count) }}</td>
                            <td class="w-32">
                                <div class="flex h-2 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    @if ($mPct > 0)
                                        <div class="h-2 bg-blue-400" style="width: {{ $mPct }}%" title="{{ $mPct }}% Male"></div>
                                    @endif
                                    @if ($fPct > 0)
                                        <div class="h-2 bg-pink-400" style="width: {{ $fPct }}%" title="{{ $fPct }}% Female"></div>
                                    @endif
                                </div>
                                <p class="text-xs text-muted mt-0.5">{{ $mPct }}M / {{ $fPct }}F</p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-bold border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td>Total</td>
                            <td class="text-right">{{ number_format($totalEnrolled) }}</td>
                            <td class="text-right text-blue-500">{{ number_format($totalMale) }}</td>
                            <td class="text-right text-pink-500">{{ number_format($totalFemale) }}</td>
                            <td>
                                @php $mTotalPct = $totalEnrolled > 0 ? round(($totalMale / $totalEnrolled) * 100) : 0; @endphp
                                <p class="text-xs text-muted">{{ $mTotalPct }}M / {{ 100 - $mTotalPct }}F</p>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Enrollment trend --}}
    <div class="card">
        <h2 class="card-title mb-4">Year-on-Year Trend</h2>
        @if ($trend->isEmpty())
            <p class="text-muted text-sm">No historical data available.</p>
        @else
            @php $maxTrend = $trend->max('total'); @endphp
            <div class="space-y-3">
                @foreach ($trend as $row)
                @php $barWidth = $maxTrend > 0 ? round(($row->total / $maxTrend) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $row->year_name }}</span>
                        <span class="font-bold text-blue-600">{{ number_format($row->total) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-2.5 rounded-full bg-blue-500 transition-all" style="width: {{ $barWidth }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        @if ($unenrolled > 0)
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-sm font-semibold text-orange-600">{{ number_format($unenrolled) }} active {{ Str::plural('student', $unenrolled) }}</p>
            <p class="text-xs text-muted mt-1">Active in the system but not enrolled in any class for this year.</p>
            <a href="{{ route('students.index') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View all students →</a>
        </div>
        @endif
    </div>

</div>
@endsection

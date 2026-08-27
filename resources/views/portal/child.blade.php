@extends('layouts.portal')
@section('title', $student->full_name)

@section('content')
{{-- Header --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('portal.dashboard') }}" class="text-gray-400 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-lg flex-shrink-0">
        {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h1>
        <p class="text-sm text-gray-500">{{ $student->classroom->name ?? 'No class' }} &bull; {{ $student->student_number ?? $student->admission_number }}</p>
    </div>
</div>

{{-- Year filter --}}
<form method="GET" class="mb-6 flex items-center gap-3">
    <label class="text-sm text-gray-600 dark:text-gray-400 font-medium whitespace-nowrap">Academic Year:</label>
    <select name="year_id" class="form-select text-sm w-44" onchange="this.form.submit()">
        @foreach ($years as $year)
            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
        @endforeach
    </select>
</form>

{{-- Attendance summary --}}
<div class="grid grid-cols-3 gap-4 mb-8">
    @php $pct = $attendanceSummary['total'] > 0 ? round($attendanceSummary['present'] / $attendanceSummary['total'] * 100) : 0; @endphp
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $attendanceSummary['present'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Present (last 60 days)</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ $attendanceSummary['absent'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Absent</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold {{ $pct >= 80 ? 'text-green-600' : ($pct >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $pct }}%</p>
        <p class="text-xs text-gray-500 mt-1">Attendance Rate</p>
    </div>
</div>

{{-- Results --}}
<section class="mb-8">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Term Results</h2>
    @if ($results->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 text-center text-sm text-gray-400">
            No published results for this year.
        </div>
    @else
    <div class="space-y-3">
        @foreach ($results as $result)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $result->term->name ?? 'Full Year' }}
                        <span class="text-sm text-gray-400 font-normal ml-1">{{ $result->academicYear->name }}</span>
                    </p>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @if ($result->average_score !== null)
                            <span>Average: <strong>{{ number_format($result->average_score, 1) }}%</strong></span>
                        @endif
                        @if ($result->overall_grade)
                            <span>Grade: <strong>{{ $result->overall_grade }}</strong></span>
                        @endif
                        @if ($result->position && $result->class_size)
                            <span>Position: <strong>{{ $result->position }}/{{ $result->class_size }}</strong></span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('portal.result', $result->id) }}"
                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium whitespace-nowrap">
                    View Full Report →
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>

{{-- Invoices --}}
<section class="mb-8">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Fee Invoices</h2>
    @if ($invoices->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 text-center text-sm text-gray-400">
            No invoices for this year.
        </div>
    @else
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Period</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Balance</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach ($invoices as $inv)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $inv->academicYear->name ?? '' }}
                        @if ($inv->term) &bull; {{ $inv->term->name }} @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono">{{ $currency }}{{ number_format($inv->total_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold {{ $inv->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $currency }}{{ number_format($inv->balance, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $sc = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-red-100 text-red-700','partial'=>'bg-blue-100 text-blue-700','overdue'=>'bg-red-100 text-red-800','cancelled'=>'bg-gray-100 text-gray-600'][$inv->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc }}">{{ ucfirst($inv->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('portal.invoice', $inv->id) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section>

{{-- Disciplinary --}}
@if ($disciplinary->isNotEmpty())
<section class="mb-8">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
        Open Conduct Matters
        <span class="ml-2 text-sm font-normal text-red-500">({{ $disciplinary->count() }})</span>
    </h2>
    <div class="space-y-3">
        @foreach ($disciplinary as $record)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-red-200 dark:border-red-800 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $record->category_label }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $record->incident_date->format('d M Y') }} &bull; Severity: {{ $record->severity_label }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ Str::limit($record->description, 120) }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                    {{ $record->status === 'open' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $record->status_label }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Recent attendance log --}}
@if ($attendance->isNotEmpty())
<section>
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Attendance Log (Last 60 Days)</h2>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach ($attendance->take(20) as $att)
                <tr>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $att->date->format('D, d M Y') }}</td>
                    <td class="px-4 py-2.5">
                        @php $ac = ['present'=>'text-green-600','absent'=>'text-red-600','late'=>'text-yellow-600','excused'=>'text-blue-600'][$att->status] ?? 'text-gray-600'; @endphp
                        <span class="font-medium {{ $ac }} capitalize">{{ $att->status_label }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $att->reason ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection

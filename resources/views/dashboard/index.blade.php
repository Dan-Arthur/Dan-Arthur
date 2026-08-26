@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumbs')
<span class="text-gray-900 font-medium">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="page-title">Executive Dashboard</h1>
        <p class="page-subtitle">
            {{ now()->format('l, F j, Y') }}
            @if($currentTerm)
            · <span class="font-medium text-blue-600">{{ $currentTerm->name }}, {{ $currentYear->name }}</span>
            @endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('attendance.index') }}" class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Take Attendance
        </a>
        <a href="{{ route('students.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Student
        </a>
    </div>
</div>

{{-- === KPI CARDS === --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    {{-- Total Students --}}
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            @if($newStudentsThisMonth > 0)
            <span class="kpi-change-up">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                +{{ $newStudentsThisMonth }}
            </span>
            @endif
        </div>
        <p class="kpi-value">{{ number_format($totalStudents) }}</p>
        <p class="kpi-label">Total Students</p>
        <p class="text-xs text-gray-400 mt-1">{{ $newStudentsThisMonth }} enrolled this month</p>
    </div>

    {{-- Present Today --}}
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="kpi-value">{{ number_format($presentToday) }}</p>
        <p class="kpi-label">Present Today</p>
        <div class="flex items-center gap-2 mt-2">
            @if($presentToday + $absentToday > 0)
            @php $attendanceRate = round($presentToday / max(1, $presentToday + $absentToday) * 100); @endphp
            <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $attendanceRate }}%"></div>
            </div>
            <span class="text-xs text-gray-500">{{ $attendanceRate }}%</span>
            @else
            <span class="text-xs text-gray-400">No records yet today</span>
            @endif
        </div>
    </div>

    {{-- Absent Today --}}
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            @if($absentToday > 0)
            <span class="kpi-change-down">{{ $absentToday }} absent</span>
            @endif
        </div>
        <p class="kpi-value">{{ number_format($absentToday) }}</p>
        <p class="kpi-label">Absent Today</p>
        <p class="text-xs text-gray-400 mt-1">{{ $staffPresentToday }} staff present</p>
    </div>

    {{-- Today's Fee Collection --}}
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="kpi-value">{{ $school?->currency_symbol ?? '₵' }}{{ number_format($todaysCollections, 0) }}</p>
        <p class="kpi-label">Today's Collections</p>
        <p class="text-xs text-gray-400 mt-1">
            {{ $school?->currency_symbol ?? '₵' }}{{ number_format($outstandingFees, 0) }} outstanding
        </p>
    </div>
</div>

{{-- === FINANCE OVERVIEW === --}}
@if($currentTerm)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="card p-5">
        <p class="text-sm font-medium text-gray-500 mb-1">Total Billed ({{ $currentTerm->name }})</p>
        <p class="text-2xl font-bold text-gray-900">{{ $school?->currency_symbol ?? '₵' }}{{ number_format($totalFeesBilled, 0) }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-gray-500 mb-1">Collected</p>
        <p class="text-2xl font-bold text-green-600">{{ $school?->currency_symbol ?? '₵' }}{{ number_format($totalFeesCollected, 0) }}</p>
        @if($totalFeesBilled > 0)
        <div class="mt-2 bg-gray-200 rounded-full h-1.5">
            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ min(100, round($totalFeesCollected / $totalFeesBilled * 100)) }}%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-1">{{ min(100, round($totalFeesCollected / $totalFeesBilled * 100)) }}% collected</p>
        @endif
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-gray-500 mb-1">Outstanding</p>
        <p class="text-2xl font-bold text-red-600">{{ $school?->currency_symbol ?? '₵' }}{{ number_format($outstandingFees, 0) }}</p>
    </div>
</div>
@endif

{{-- === CHARTS & DATA SECTION === --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Attendance Trend Chart --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Attendance Trend</h3>
                <p class="text-xs text-gray-500">Last 7 days</p>
            </div>
        </div>
        <div class="card-body">
            <canvas id="attendanceChart" height="120"></canvas>
        </div>
    </div>

    {{-- Class Distribution --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">Class Distribution</h3>
        </div>
        <div class="card-body">
            @if($classDistribution->count() > 0)
            <div class="space-y-3">
                @foreach($classDistribution as $cls)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700 truncate">{{ $cls->class_name }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $cls->count }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ min(100, round($cls->count / max(1, $classDistribution->max('count')) * 100)) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm">No data yet</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- === BOTTOM ROW: Recent Students + Announcements + Events === --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Students --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Recent Students</h3>
            <a href="{{ route('students.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all</a>
        </div>
        <div class="overflow-x-auto">
            @if($recentStudents->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Admitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentStudents as $student)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $student->photo_url }}" alt="Photo" class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->student_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->currentClass?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $student->status === 'active' ? 'badge-success' : 'badge-gray' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="text-gray-500">{{ $student->admission_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <p class="text-sm">No students yet. <a href="{{ route('students.create') }}" class="text-blue-600 hover:underline">Add your first student</a></p>
            </div>
            @endif
        </div>
    </div>

    {{-- Announcements & Events --}}
    <div class="space-y-4">
        {{-- Announcements --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Announcements</h3>
                <a href="{{ route('announcements.index') }}" class="text-xs text-blue-600">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentAnnouncements as $announcement)
                <div class="px-4 py-3">
                    <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $announcement->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ strip_tags($announcement->body) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $announcement->publish_at?->diffForHumans() }}</p>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-400">
                    <p class="text-sm">No announcements</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Upcoming Events --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Upcoming Events</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($upcomingEvents as $event)
                <div class="px-4 py-3 flex items-start gap-3">
                    <div class="text-center bg-blue-50 rounded-lg px-2 py-1 flex-shrink-0">
                        <p class="text-xs font-bold text-blue-700">{{ $event->start_datetime->format('d') }}</p>
                        <p class="text-xs text-blue-500">{{ $event->start_datetime->format('M') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $event->title }}</p>
                        <p class="text-xs text-gray-500">{{ $event->start_datetime->format('g:i A') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-400">
                    <p class="text-sm">No upcoming events</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Trend Chart
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        const trend = @json($attendanceTrend);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trend.map(t => t.date),
                datasets: [
                    {
                        label: 'Present',
                        data: trend.map(t => t.present),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#22c55e',
                        pointRadius: 4,
                    },
                    {
                        label: 'Absent',
                        data: trend.map(t => t.absent),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ef4444',
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 12 }, usePointStyle: true }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, precision: 0 }
                    }
                }
            }
        });
    }
});
</script>
@endpush

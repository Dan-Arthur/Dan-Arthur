@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Reports & Analytics</h1>
        @if ($currentYear)
            <p class="page-subtitle">Current year: {{ $currentYear->name }}</p>
        @endif
    </div>
</div>

{{-- Quick KPI strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-blue-600">{{ number_format($totalStudents) }}</p>
        <p class="text-sm text-muted mt-1">Active Students</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-green-600">{{ $currency }}{{ number_format($feesCollected, 2) }}</p>
        <p class="text-sm text-muted mt-1">Fees Collected ({{ $currentYear?->name ?? 'Current Year' }})</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-red-500">{{ $currency }}{{ number_format($feesOutstanding, 2) }}</p>
        <p class="text-sm text-muted mt-1">Outstanding Balance</p>
    </div>
    <div class="card text-center py-5">
        <p class="text-3xl font-bold text-orange-500">{{ number_format($defaulterCount) }}</p>
        <p class="text-sm text-muted mt-1">Students with Arrears</p>
    </div>
</div>

{{-- Report categories --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    {{-- Financial --}}
    <a href="{{ route('reports.financial') }}" class="card hover:border-blue-400 dark:hover:border-blue-500 transition-colors group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">Financial Overview</h2>
                <p class="text-sm text-muted mt-1">Fee collection rates, payment method breakdown, income vs expenses, monthly collection trends.</p>
                <p class="text-xs text-blue-600 font-medium mt-3">View report →</p>
            </div>
        </div>
    </a>

    {{-- Defaulters --}}
    <a href="{{ route('reports.defaulters') }}" class="card hover:border-red-400 dark:hover:border-red-500 transition-colors group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 dark:group-hover:bg-red-900/50 transition-colors">
                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-red-600 transition-colors">Fee Defaulters</h2>
                <p class="text-sm text-muted mt-1">Students with unpaid or overdue invoices. Filter by class, year, or status. Total outstanding shown.</p>
                <p class="text-xs text-red-600 font-medium mt-3">View report →</p>
            </div>
        </div>
    </a>

    {{-- Attendance --}}
    <a href="{{ route('reports.attendance') }}" class="card hover:border-yellow-400 dark:hover:border-yellow-500 transition-colors group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-100 dark:group-hover:bg-yellow-900/50 transition-colors">
                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-yellow-600 transition-colors">Attendance Summary</h2>
                <p class="text-sm text-muted mt-1">Class-by-class attendance rates, overall school rate, and the students with the highest absence counts.</p>
                <p class="text-xs text-yellow-600 font-medium mt-3">View report →</p>
            </div>
        </div>
    </a>

    {{-- Enrollment --}}
    <a href="{{ route('reports.enrollment') }}" class="card hover:border-purple-400 dark:hover:border-purple-500 transition-colors group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 dark:group-hover:bg-purple-900/50 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white group-hover:text-purple-600 transition-colors">Enrollment Report</h2>
                <p class="text-sm text-muted mt-1">Students per class, gender breakdown, enrollment trend across academic years, unenrolled active students.</p>
                <p class="text-xs text-purple-600 font-medium mt-3">View report →</p>
            </div>
        </div>
    </a>

</div>
@endsection

@extends('layouts.portal')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        Welcome, {{ $guardian->first_name }}
    </h1>
    <p class="text-gray-500 text-sm mt-1">Here's a summary of your {{ $childData->count() === 1 ? 'child' : 'children' }}.</p>
</div>

{{-- Children cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-10">
    @foreach ($childData as $data)
    @php $student = $data['student']; $bal = $data['balance']; $att = $data['lastAttendance']; @endphp
    <a href="{{ route('portal.child', $student->id) }}"
       class="block bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-md hover:border-blue-400 dark:hover:border-blue-600 transition-all">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-lg flex-shrink-0">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $student->classroom->name ?? 'No class' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <p class="text-xs text-gray-500 mb-1">Outstanding Balance</p>
                <p class="font-bold {{ $bal > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $school->currency_symbol ?? '₵' }}{{ number_format($bal, 2) }}
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <p class="text-xs text-gray-500 mb-1">Last Attendance</p>
                @if ($att)
                    <p class="font-semibold capitalize
                        {{ $att->status === 'present' ? 'text-green-600' : ($att->status === 'absent' ? 'text-red-600' : 'text-yellow-600') }}">
                        {{ $att->status_label }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $att->date->format('d M Y') }}</p>
                @else
                    <p class="text-gray-400">—</p>
                @endif
            </div>
        </div>
        <div class="mt-3 text-right text-xs text-blue-600 dark:text-blue-400 font-medium">View details →</div>
    </a>
    @endforeach
</div>

{{-- Announcements --}}
@if ($announcements->isNotEmpty())
<div>
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">School Announcements</h2>
    <div class="space-y-3">
        @foreach ($announcements as $ann)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $ann->title }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ strip_tags($ann->content) }}</p>
                </div>
                <p class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">
                    {{ $ann->published_at?->format('d M') }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

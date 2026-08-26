@extends('layouts.app')

@section('title', $module)

@section('breadcrumbs')
<a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
<span class="text-gray-400 mx-1">/</span>
<span class="text-gray-900 font-medium">{{ $module }}</span>
@endsection

@section('content')
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $module }}</h2>
    <p class="text-gray-500 max-w-md">
        This module is part of the School OS and is currently being implemented.
        The full implementation follows the phased development roadmap.
    </p>
    <div class="mt-6 flex gap-3">
        <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm">
            ← Back to Dashboard
        </a>
    </div>
</div>
@endsection

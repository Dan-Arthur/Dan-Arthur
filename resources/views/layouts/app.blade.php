<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'School OS') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 antialiased" x-data="{ sidebarOpen: $persist(true), mobileSidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- ===== SIDEBAR ===== --}}
    <aside
        :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="hidden lg:flex flex-col flex-shrink-0 bg-slate-900 transition-all duration-300 ease-in-out overflow-hidden">

        {{-- Brand --}}
        <div class="flex items-center h-16 px-4 border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-100" class="min-w-0">
                    <p class="text-white font-bold text-sm leading-tight truncate">School OS</p>
                    <p class="text-slate-500 text-xs truncate">
                        {{ auth()->user()?->school?->name ?? 'Platform' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2">
            @include('layouts.partials.sidebar-nav')
        </nav>

        {{-- User profile at bottom --}}
        <div class="border-t border-slate-800 p-3 flex-shrink-0">
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800 transition-colors">
                <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full flex-shrink-0">
                <div x-show="sidebarOpen" class="min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()?->full_name }}</p>
                    <p class="text-slate-500 text-xs truncate">{{ auth()->user()?->getRoleNames()->first() }}</p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Mobile sidebar overlay --}}
    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-40 lg:hidden" @click="mobileSidebarOpen = false">
        <div class="fixed inset-0 bg-gray-900/70"></div>
        <aside class="fixed top-0 left-0 h-full w-64 bg-slate-900 flex flex-col z-50">
            <div class="flex items-center justify-between h-16 px-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">S</span>
                    </div>
                    <p class="text-white font-bold text-sm">School OS</p>
                </div>
                <button @click="mobileSidebarOpen = false" class="text-slate-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-2">
                @include('layouts.partials.sidebar-nav')
            </nav>
        </aside>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Top Navigation Bar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center gap-4 px-4 lg:px-6 flex-shrink-0">

            {{-- Mobile menu toggle --}}
            <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="lg:hidden p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Desktop sidebar toggle --}}
            <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Breadcrumbs --}}
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500 min-w-0">
                @yield('breadcrumbs')
            </div>

            <div class="flex-1"></div>

            {{-- Header actions --}}
            <div class="flex items-center gap-2">

                {{-- Current term badge --}}
                @php $currentTerm = auth()->user()?->school?->currentTerm(); @endphp
                @if($currentTerm)
                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $currentTerm->academicYear->name }} · {{ $currentTerm->name }}
                </span>
                @endif

                {{-- Notifications --}}
                <button class="relative p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </button>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full">
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()?->first_name }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()?->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()?->email }}</p>
                        </div>
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profile Settings
                        </a>
                        <div class="border-t border-gray-100 mt-2 pt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') || session('error') || session('warning') || session('info'))
        <div class="px-4 lg:px-6 pt-4">
            @if(session('success'))
            <div data-flash-dismiss class="alert alert-success mb-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div data-flash-dismiss class="alert alert-error mb-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>

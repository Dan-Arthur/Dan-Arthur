<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Parent Portal') — {{ auth()->user()->school->name ?? 'School' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">

{{-- Top navigation --}}
<header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            @php $school = auth()->user()->school; @endphp
            @if ($school?->logoUrl())
                <img src="{{ $school->logoUrl() }}" alt="Logo" class="h-9 w-9 object-contain rounded">
            @endif
            <div>
                <p class="font-bold text-sm text-gray-900 dark:text-white leading-tight">{{ $school?->name }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Parent Portal</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.dashboard') }}"
               class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium">
                Home
            </a>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 rounded-lg px-2 py-1.5">
                    <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak
                     class="absolute right-0 mt-1 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-1 text-sm">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

<footer class="max-w-5xl mx-auto px-4 sm:px-6 py-6 border-t border-gray-200 dark:border-gray-800 mt-8 text-center text-xs text-gray-400">
    {{ $school?->name }} &bull; Parent Portal &bull; {{ now()->format('Y') }}
</footer>

</body>
</html>

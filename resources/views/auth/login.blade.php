@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<h2 class="text-xl font-bold text-gray-900 mb-1">Welcome back</h2>
<p class="text-gray-500 text-sm mb-6">Sign in to your School OS account</p>

<form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
    @csrf

    @if($errors->any())
    <div class="alert alert-error mb-4">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <div class="space-y-4">
        <div>
            <label for="email" class="form-label">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="form-input @error('email') border-red-500 @enderror"
                placeholder="you@school.edu"
            >
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="form-label !mb-0">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Forgot password?
                </a>
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-input @error('password') border-red-500 @enderror"
                placeholder="••••••••"
            >
        </div>

        <div class="flex items-center">
            <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember" class="ml-2 text-sm text-gray-600">Remember me for 30 days</label>
        </div>
    </div>

    <button
        type="submit"
        class="btn-primary w-full justify-center mt-6 py-3"
        :disabled="loading"
        :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
    >
        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span x-text="loading ? 'Signing in...' : 'Sign In'">Sign In</span>
    </button>
</form>

{{-- Demo credentials --}}
@if(config('app.debug'))
<div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
    <p class="text-xs font-semibold text-amber-800 mb-2">Demo Credentials (Development)</p>
    <div class="space-y-1 text-xs text-amber-700">
        <p><strong>Super Admin:</strong> superadmin@schoolos.com / SchoolOS@2024!</p>
        <p><strong>School Admin:</strong> admin@greenfieldacademy.edu.ng / Admin@2024!</p>
        <p><strong>Principal:</strong> principal@greenfieldacademy.edu.ng / Principal@2024!</p>
        <p><strong>Teacher:</strong> teacher@greenfieldacademy.edu.ng / Teacher@2024!</p>
    </div>
</div>
@endif
@endsection

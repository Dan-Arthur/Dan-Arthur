@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h2 class="text-xl font-bold text-gray-900 mb-1">Reset your password</h2>
<p class="text-gray-500 text-sm mb-6">Enter your email and we'll send you a reset link.</p>

<form method="POST" action="{{ route('password.email') }}">
@csrf
    <div class="space-y-4">
        <div>
            <label class="form-label">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="form-input" placeholder="you@school.edu">
        </div>
    </div>
    <button type="submit" class="btn-primary w-full justify-center mt-6 py-3">Send Reset Link</button>
</form>

<div class="mt-4 text-center">
    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800">← Back to sign in</a>
</div>
@endsection

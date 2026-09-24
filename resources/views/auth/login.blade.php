@extends('layouts.auth')
@section('content')
    <h1 class="text-lg font-semibold mb-1">Hi, Welcome Back</h1>
    <p class="text-sm mb-6 text-gray-500">Log in to manage your gigs, sales, and earnings.</p>

    @if ($errors->any())
    <div class="bg-red-50 text-red-600 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
    <div class="bg-green-50 text-green-600 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs text-gray-500">Email</label>
            <input type="email" name="email" required placeholder="e-mail address"
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-black outline-none text-sm">
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label class="text-xs text-gray-500">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-gray-500">Forgot?</a>
            </div>
            <input type="password" name="password" required placeholder="password"
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-black outline-none text-sm">
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('auth.google') }}" class="auth-google-circle" style="flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
            </a>
            <button type="submit" class="flex-1 py-3 rounded-full bg-black text-white font-semibold text-sm">Log In</button>
        </div>
    </form>

    <p class="text-xs text-gray-400 leading-relaxed mt-6">
        🔒 Your data and payments are protected with verified identity checks and licensed payment partners. We never ask for your PIN outside the app.
    </p>

    <p class="text-sm text-center mt-4 text-gray-500">
        New here? <a href="{{ route('register') }}" class="font-medium" style="color:#111827;">Create an account</a>
    </p>
@endsection

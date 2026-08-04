@extends('layouts.auth')
@section('content')
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">C</div>
        <h1 class="text-lg font-semibold">Campulsy</h1>
    </div>

    <p class="text-sm mb-6 text-gray-500">Welcome back. Log in to manage your gigs, sales, and earnings.</p>

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
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <div>
            <div class="flex justify-between items-center">
                <label class="text-xs text-gray-500">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-gray-500">Forgot?</a>
            </div>
            <input type="password" name="password" required placeholder="password"
                class="w-full mt-1 px-4 py-3 rounded-full border border-gray-300 focus:border-indigo-500 outline-none text-sm">
        </div>
        <button type="submit" class="w-full py-3 rounded-full bg-indigo-600 text-white font-semibold text-sm">Log In</button>
    </form>

    <p class="text-xs text-gray-400 leading-relaxed mt-6">
        🔒 Your data and payments are protected with verified identity checks and licensed payment partners. We never ask for your PIN outside the app.
    </p>

    <p class="text-sm text-center mt-4 text-gray-500">
        New here? <a href="{{ route('register') }}" class="text-indigo-600 font-medium">Create an account</a>
    </p>
@endsection

@extends('layouts.landing')
@section('content')
<div class="max-w-sm mx-auto mt-24 p-6">
    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Reset Password</h1>
        <p class="text-muted text-sm mb-6">Enter your email and we'll send you a reset link.</p>

        @if (session('status'))
        <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <input type="email" name="email" required placeholder="Your email" class="input-field !mt-0">
            <button type="submit" class="btn-primary w-full justify-center">Send Reset Link</button>
        </form>
        <a href="{{ route('login') }}" class="text-muted text-sm mt-4 block text-center">Back to login</a>
    </div>
</div>
@endsection

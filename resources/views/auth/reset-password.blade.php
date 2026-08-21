@extends('layouts.landing')
@section('content')
<div class="max-w-sm mx-auto mt-24 p-6">
    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Set New Password</h1>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="email" name="email" value="{{ $email }}" required placeholder="Email" class="input-field !mt-0">
            <input type="password" name="password" required placeholder="New password" class="input-field !mt-0">
            <input type="password" name="password_confirmation" required placeholder="Confirm password" class="input-field !mt-0">
            <button type="submit" class="btn-primary w-full justify-center">Reset Password</button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.landing')
@section('content')
<div class="auth-page-wrap">
    <div class="auth-watermark"></div>
    <div class="auth-card">
        <div class="logo-badge auth-card-logo"></div>
        <h1 class="auth-card-title">Set New Password</h1>
        <p class="auth-card-subtitle">Choose a new password for your account.</p>

        @if ($errors->any())
        <div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="email" name="email" value="{{ $email }}" required placeholder="Email" class="auth-field">
            <input type="password" name="password" required placeholder="New password" class="auth-field">
            <input type="password" name="password_confirmation" required placeholder="Confirm password" class="auth-field">
            <button type="submit" class="auth-submit-btn">Reset Password</button>
        </form>
        <a href="{{ route('login') }}" class="auth-back-link">← Back to login</a>
    </div>
</div>
@endsection

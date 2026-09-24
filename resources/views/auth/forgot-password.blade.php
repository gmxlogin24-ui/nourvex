@extends('layouts.landing')
@section('content')
<div class="auth-page-wrap">
    <div class="auth-watermark"></div>
    <div class="auth-card">
        <div class="logo-badge auth-card-logo"></div>
        <h1 class="auth-card-title">Reset Password</h1>
        <p class="auth-card-subtitle">Enter your email and we'll send you a link to get back in.</p>

        @if (session('status'))
        <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
        <div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <input type="email" name="email" required placeholder="Your email" class="auth-field" value="{{ old('email') }}">
            <button type="submit" class="auth-submit-btn">Send Reset Link</button>
        </form>
        <a href="{{ route('login') }}" class="auth-back-link">← Back to login</a>
    </div>
</div>
@endsection

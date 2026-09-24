@extends('layouts.app-authenticated')
@section('content')

<main class="profile-shell p-6 {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;max-width:1240px;' : '' }}">
    <a href="{{ route('profile.index') }}" class="profile-back-link"><i data-lucide="arrow-left" class="w-4 h-4"></i> Profile</a>

    <div class="profile-detail-grid">
        <div class="profile-detail-main">
            <h1 class="text-2xl font-semibold mb-1 hoverfade-text">Account Details</h1>
            <p class="text-muted text-sm mb-6">Your name, email, and password</p>

            @if (session('status'))
            <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
            <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
            @endif

            <div class="glass-card p-6 mb-6 reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
                <h2 class="font-semibold mb-4">Basic Info</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-muted">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-field !mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-muted">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input-field !mt-1">
                    </div>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </form>
            </div>

            <div class="glass-card p-6 reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
                <h2 class="font-semibold mb-4">Change Password</h2>
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm text-muted">Current Password</label>
                        <input type="password" name="current_password" required class="input-field !mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-muted">New Password</label>
                        <input type="password" name="password" required class="input-field !mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-muted">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="input-field !mt-1">
                    </div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </form>
            </div>
        </div>

        <div class="profile-detail-aside profile-shape-account">
            <div class="profile-shape-inner">
                <div class="profile-shape-avatar">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}">
                    @else
                    <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <p class="profile-shape-title">{{ $user->name }}</p>
                <p class="profile-shape-sub">{{ $user->email }}</p>
            </div>
        </div>
    </div>
</main>
@endsection

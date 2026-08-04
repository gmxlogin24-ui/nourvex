@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1 hoverfade-text">Profile</h1>
    <p class="text-muted text-sm mb-6">Manage your account details</p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="glass-card p-6 mb-6 reveal-on-scroll">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-16 h-16 rounded-full bg-card flex items-center justify-center text-xl font-semibold overflow-hidden" id="avatarPreviewWrap" data-face-cursor>
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" id="avatarPreview" class="w-full h-full object-cover">
                    @else
                    <span id="avatarPreview">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <label for="avatarInput" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center cursor-pointer" style="background:var(--color-accent);">
                    <i data-lucide="camera" class="w-3 h-3 text-white"></i>
                </label>
            </div>
            <div>
                <p class="font-semibold">{{ $user->name }} @if($user->tier !== 'free')<span class="merchant-badge">Merchant</span>@endif</p>
                <p class="text-muted text-sm">{{ $user->tier === 'free' ? 'Free account' : ($user->tier === 'tier1' ? 'Plus' : ($user->tier === 'tier2' ? 'Pro' : strtoupper($user->tier))) }}
                    @if($user->is_verified_seller) · <span class="text-accent">Verified</span> @endif
                </p>
                <p class="text-muted text-xs mt-1">Referral code: {{ $user->referral_code }}</p>
            </div>
        </div>
    </div>

    <div class="glass-card p-6 mb-6 reveal-on-scroll" id="repCard">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Reputation</h2>
            <span class="trust-pill" style="gap:0.4rem;">
                <i data-lucide="{{ $workerLevel['icon'] }}" class="w-4 h-4" style="color:var(--color-accent);"></i>
                <span class="font-semibold text-sm">{{ $workerLevel['label'] }}</span>
                @if($user->badge_tier === 'bronze')
                <span class="member-badge member-badge-bronze"><i data-lucide="medal"></i> Bronze Member</span>
                @endif
                @if($user->tier === 'tier3')
                <span class="member-badge member-badge-gold"><i data-lucide="crown"></i> Premium</span>
                @endif
            </span>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <p class="text-xl font-bold">{{ $completedGigsCount }}</p>
                <p class="text-muted text-xs">Jobs Completed</p>
            </div>
            <div>
                <p class="text-xl font-bold flex items-center justify-center gap-1">
                    @if($averageRating)<i data-lucide="star" class="w-4 h-4" style="color:#fbbf24;fill:#fbbf24;"></i>{{ $averageRating }}@else —@endif
                </p>
                <p class="text-muted text-xs">Avg Rating</p>
            </div>
            <div>
                <p class="text-xl font-bold">{{ $reviewsReceivedCount }}</p>
                <p class="text-muted text-xs">Reviews</p>
            </div>
        </div>
    </div>
    <div class="glass-card p-6 mb-6 reveal-on-scroll" id="quickLinksCard">
        <h2 class="font-semibold mb-4">Quick Links</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('wallet.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="wallet" class="w-5 h-5"></i> Wallet
            </a>
            <a href="{{ route('referrals.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="users" class="w-5 h-5"></i> Referrals
            </a>
            <a href="{{ route('subscriptions.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i class="ph-fill ph-crown" style="font-size:1.1rem;"></i> Upgrade
            </a>
            <a href="{{ route('money-tracker.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="line-chart" class="w-5 h-5"></i> Money Tracker
            </a>
            <a href="{{ route('posts.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="file-text" class="w-5 h-5"></i> My Posts
            </a>
            <a href="{{ route('leaderboard.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="trophy" class="w-5 h-5"></i> Leaderboard
            </a>
            <a href="{{ route('services.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="lightbulb" class="w-5 h-5"></i> Services
            </a>
            <a href="{{ route('pulse.index') }}" class="flex flex-col items-center gap-2 p-3 rounded-xl text-center text-sm" style="background:var(--color-card);">
                <i data-lucide="zap" class="w-5 h-5"></i> Pulse
            </a>
        </div>
    </div>
    <div class="glass-card p-6 mb-6 reveal-on-scroll">
        <h2 class="font-semibold mb-4">Account Details</h2>
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

    <div class="glass-card p-6 mb-6 reveal-on-scroll">
        <h2 class="font-semibold mb-4">Appearance</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">

            <div>
                <p class="text-sm font-medium mb-1">Profile Picture</p>
                <input type="file" name="avatar" id="avatarInput" accept="image/*" class="input-field !mt-1" onchange="previewImage(this, 'avatarPreview', true)">
            </div>

            <div>
                <p class="text-sm font-medium mb-1">Personal Background</p>
                <p class="text-muted text-xs mb-2">This replaces the default background across the whole site, just for you.</p>
                @if($user->background_url)
                <img src="{{ $user->background_url }}" class="w-full h-28 object-cover rounded-xl mb-2">
                @endif
                <input type="file" name="background" accept="image/*" class="input-field !mt-1">
            </div>

            <div>
                <p class="text-sm font-medium mb-2">Accent Color</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <input type="color" name="accent_color" id="accentColorInput" value="{{ $user->accent_color ?? '#38D9E8' }}" style="width:3rem;height:2.6rem;border-radius:0.75rem;border:1px solid var(--border-color);background:none;cursor:pointer;">
                    <div class="flex gap-2">
                        @foreach(['#38D9E8','#F472B6','#A78BFA','#FB923C','#4ADE80','#F43F5E'] as $swatch)
                        <button type="button" onclick="document.getElementById('accentColorInput').value='{{ $swatch }}'; document.getElementById('accentColorInput').dispatchEvent(new Event('input'));" class="w-7 h-7 rounded-full" style="background:{{ $swatch }};border:2px solid var(--border-color);"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Appearance</button>
        </form>

        <div class="flex items-center justify-between mt-5 pt-5" style="border-top:1px solid var(--border-color);">
            <div>
                <p class="text-sm font-medium">Dark Mode</p>
                <p class="text-muted text-xs mt-1">Switch between light and dark</p>
            </div>
            <button onclick="toggleTheme()" class="theme-toggle"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
        </div>
    </div>

    <script>
    function previewImage(input, targetId, isAvatar) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.getElementById('avatarPreviewWrap');
            if (isAvatar && wrap) {
                wrap.innerHTML = '<img src="' + e.target.result + '" id="avatarPreview" class="w-full h-full object-cover">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
    document.getElementById('accentColorInput')?.addEventListener('input', function () {
        document.body.style.setProperty('--user-accent', this.value);
    });
    </script>

    <div class="glass-card p-6">
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
</main>
@endsection

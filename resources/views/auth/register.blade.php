@extends('layouts.auth')
@section('content')
    <div class="flex items-center gap-3 mb-5">
        <div class="logo-badge"></div>
        <h1 class="text-lg font-semibold">Qelvo</h1>
    </div>

    <p class="text-sm mb-6" style="opacity:0.75;">Create your account. Free to join — you only earn or spend when you're ready.</p>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <a href="{{ route('auth.google') }}" class="w-full py-3 rounded-full border border-gray-300 text-sm font-medium flex items-center justify-center gap-2 mb-4" style="text-decoration:none;color:inherit;">
        <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
        Continue with Google
    </a>
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs" style="opacity:0.5;">or</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="intended_plan" value="{{ request('plan', 'tier1') }}">
        @if(request('plan') && request('plan') !== 'free')
        <div class="bg-accent/10 text-sm rounded-lg p-3" style="background:color-mix(in srgb, var(--color-accent) 12%, transparent);">
            You're signing up for the <strong>{{ \App\Models\Subscription::PLANS[request('plan')]['label'] ?? '' }}</strong> plan — you'll pay right after this step.
        </div>
        @endif
        <div>
            <label class="text-xs" style="opacity:0.7;">Full Name</label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="Your full name" class="glass-input">
        </div>
        <div>
            <label class="text-xs" style="opacity:0.7;">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}" placeholder="e-mail address" class="glass-input">
        </div>
        <div>
            <label class="text-xs" style="opacity:0.7;">Password</label>
            <input type="password" name="password" required placeholder="password" class="glass-input">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs" style="opacity:0.7;">Gender</label>
                <select name="gender" required class="glass-input">
                    <option value="" disabled selected>Select</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Spoken Language</label>
                <select name="spoken_language" required class="glass-input">
                    <option value="English" {{ old('spoken_language', 'English') == 'English' ? 'selected' : '' }}>English</option>
                    <option value="Pidgin" {{ old('spoken_language') == 'Pidgin' ? 'selected' : '' }}>Pidgin</option>
                    <option value="Hausa" {{ old('spoken_language') == 'Hausa' ? 'selected' : '' }}>Hausa</option>
                    <option value="Yoruba" {{ old('spoken_language') == 'Yoruba' ? 'selected' : '' }}>Yoruba</option>
                    <option value="Igbo" {{ old('spoken_language') == 'Igbo' ? 'selected' : '' }}>Igbo</option>
                    <option value="Other" {{ old('spoken_language') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs" style="opacity:0.7;">Referral Code (optional)</label>
            <input type="text" name="referral_code" value="{{ old('referral_code') }}" placeholder="optional" class="glass-input">
        </div>

        <div class="border-t border-base pt-4 mt-2">
            <p class="text-xs font-medium mb-1">Get a personal virtual account (optional)</p>
            <p class="text-xs mb-3" style="opacity:0.6;">Skip this and add it later from your wallet if you prefer — you can still send and receive money by manual transfer either way.</p>

            <label class="text-xs" style="opacity:0.7;">Phone Number</label>
            <input type="text" name="phonenumber" value="{{ old('phonenumber') }}" placeholder="optional" class="glass-input">

            <label class="text-xs mt-3 block" style="opacity:0.7;">BVN</label>
            <input type="text" name="bvn" value="{{ old('bvn') }}" maxlength="11" placeholder="optional, 11 digits" class="glass-input">
            <p class="text-xs mt-1" style="opacity:0.55;">Required by Flutterwave to issue a permanent account number. We never store your BVN.</p>
        </div>

        <div class="glass-btn-wrap mt-2">
            <button type="submit" class="glass-btn">Create Account</button>
        </div>
    </form>

    <p class="auth-footnote mt-6">
        🔒 Registration is free. We verify identities to keep the community safe from fraud and scams.
    </p>

    <p class="text-sm text-center mt-4" style="opacity:0.75;">
        Already have an account? <a href="{{ route('login') }}" class="text-accent">Log in</a>
    </p>
@endsection

@extends('layouts.auth')
@section('content')
    <div class="flex items-center gap-3 mb-5">
        <div class="logo-badge"></div>
        <h1 class="text-lg font-semibold">NAURVEX</h1>
    </div>

    <p class="text-sm mb-6" style="opacity:0.75;">Create your account. Free to join — upgrade any time.</p>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        @php
            $allPlans = array_merge(['free' => ['label' => 'Free', 'price' => 0]], \App\Models\Subscription::PLANS);
            $pickPlan = array_key_exists((string) request('plan'), $allPlans) ? request('plan') : (old('intended_plan') ?: 'free');
        @endphp
        <div>
            <label class="text-xs" style="opacity:0.7;">Choose your plan (paid plans billed yearly)</label>
            <div class="reg-plans">
                @foreach ($allPlans as $k => $p)
                    <label class="reg-plan">
                        <input type="radio" name="intended_plan" value="{{ $k }}" {{ $pickPlan === $k ? 'checked' : '' }}>
                        <span class="reg-plan-body"><strong>{{ $p['label'] }}</strong><span>{{ $p['price'] ? '₦' . number_format($p['price']) : 'Free' }}</span></span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs" style="opacity:0.65;margin-top:.4rem;">Paid plans: you pay after setting up your account. Skip it and you stay on Free.</p>
            <style>.reg-plans{display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;margin-top:.4rem}.reg-plan{position:relative}.reg-plan input{position:absolute;opacity:0}.reg-plan-body{display:flex;flex-direction:column;align-items:center;gap:.15rem;padding:.7rem .3rem;border:1.5px solid rgba(128,128,128,.35);border-radius:.8rem;font-size:.8rem;cursor:pointer}.reg-plan input:checked+.reg-plan-body{border-color:var(--color-accent);background:color-mix(in srgb,var(--color-accent) 12%,transparent)}</style>
        </div>
        <div>
            <label class="text-xs" style="opacity:0.7;">Full Name</label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="Your full name" class="glass-input">
    </div>
    <div>
        <label class="text-xs" style="opacity:0.7;">Username (this is what people see)</label>
        <input type="text" name="username" required value="{{ old('username') }}" maxlength="20" autocapitalize="none" autocomplete="off" placeholder="e.g. emmanuel_k" class="glass-input">
        <p class="text-xs" style="opacity:0.6;margin-top:.3rem;">3 to 20 characters: lowercase letters, numbers, underscores.</p>
        </div>
        <div>
            <label class="text-xs" style="opacity:0.7;">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}" placeholder="e-mail address" class="glass-input">
        </div>
        <div>
            <label class="text-xs" style="opacity:0.7;">Password</label>
            <input type="password" name="password" required placeholder="password" class="glass-input">
        </div>

        <div>
            <label class="text-xs" style="opacity:0.7;">Where are you based?</label>
            <select name="area" required class="glass-input">
                <option value="" disabled selected>Select your area</option>
                @foreach (config('areas.list') as $k => $label)
                    <option value="{{ $k }}" {{ old('area') == $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
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

        <div class="flex items-center gap-3 mt-2">
            <a href="{{ route('auth.google') }}" class="auth-google-circle" style="flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.71v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.61z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
            </a>
            <div class="glass-btn-wrap flex-1">
                <button type="submit" class="glass-btn" style="width:100%;">Create Account</button>
            </div>
        </div>
    </form>

    <p class="auth-footnote mt-6">
        🔒 Registration is free. We verify identities to keep the community safe from fraud and scams.
    </p>

    <p class="text-sm text-center mt-4" style="opacity:0.75;">
        Already have an account? <a href="{{ route('login') }}" class="text-accent">Log in</a>
    </p>
@endsection

@extends('layouts.auth')
@section('content')
    <div class="flex items-center gap-3 mb-5">
        <div class="logo-badge"></div>
        <h1 class="text-lg font-semibold">NAURVEX</h1>
    </div>

    <p class="text-sm mb-6" style="opacity:0.75;">You've been invited to join the NAURVEX Customer Care team.</p>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.invite.store', $code) }}" class="space-y-4">
        @csrf
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

        <div class="glass-btn-wrap mt-2">
            <button type="submit" class="glass-btn">Create Customer Care Account</button>
        </div>
    </form>

    <p class="auth-footnote mt-6">
        🔒 This link is single-use and will expire. It was generated specifically for you by a NAURVEX admin.
    </p>

    <p class="text-sm text-center mt-4" style="opacity:0.75;">
        Already have an account? <a href="{{ route('login') }}" class="text-accent">Log in</a>
    </p>
@endsection

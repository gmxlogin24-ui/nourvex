@extends('layouts.app')
@section('content')
<div class="max-w-lg mx-auto mt-16 p-6 reveal-on-scroll">
    <div class="glass-card p-8">
        <h1 class="text-xl font-semibold mb-1">What are you interested in?</h1>
        <p class="text-muted text-sm mb-6">Pick 2-5 categories. We'll highlight matching gigs for you on your dashboard.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('onboarding.interests.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3 mb-6">
                @foreach($categories as $cat)
                <label class="tab-btn" style="cursor:pointer;display:flex;align-items:center;gap:0.5rem;justify-content:flex-start;padding:0.75rem 1rem;">
                    <input type="checkbox" name="interests[]" value="{{ $cat }}" class="interest-check">
                    <span class="text-sm">{{ $cat }}</span>
                </label>
                @endforeach
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Continue to Dashboard</button>
        </form>
    </div>
</div>
@endsection

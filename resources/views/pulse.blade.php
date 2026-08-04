@extends('layouts.app')
@section('content')


<link rel="stylesheet" href="{{ asset('css/pulse.css') }}?v={{ time() }}">

<canvas id="pulseBgCanvas"></canvas>
<div class="pulse-rail" id="pulseRail"></div>
<div class="autoplay-indicator" id="autoplayIndicator"><span class="ring"></span>auto-playing</div>
<button class="motion-permit" id="motionPermit">enable motion tilt</button>

<div class="pulse-feed" id="pulseFeed">

    @forelse($posts as $post)
    @php
        $isArt = $post->type === 'ad';
        $fallbackTint = $isArt ? '#5c1745' : '#0d3a4c';
    @endphp
    <section class="pulse-card {{ $isArt ? 'pulse-card--art' : 'pulse-card--news' }}" data-type="{{ $isArt ? 'art' : 'news' }}">
        <div class="pulse-card__bg">
            <div class="media-grid count-1">
                @if($post->video_url)
                <div class="tile video">
                    <video src="{{ $post->video_url }}" autoplay muted loop playsinline></video>
                    <div class="play"><svg viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg></div>
                </div>
                @elseif($post->image_url)
                <div class="tile" style="background-image:url('{{ $post->image_url }}')"></div>
                @else
                <div class="tile" style="background:radial-gradient(120% 90% at 30% 20%, {{ $fallbackTint }} 0%, #0b0b0f 70%)"></div>
                @endif
            </div>
        </div>
        <div class="pulse-card__content">
            <div class="pulse-card__eyebrow"><span class="dot"></span>{{ $isArt ? ($post->category ?? 'Original') : 'Campus News' }}</div>
            <h1 class="pulse-card__title">{{ $post->title }}</h1>
            <p class="pulse-card__body">{{ Str::limit($post->body, 140) }}</p>
            <div class="pulse-card__meta">
                <span>{{ $post->published_at?->diffForHumans() ?? 'Just now' }}</span>
                @if($post->source_url)
                <span><a href="{{ $post->source_url }}" target="_blank" style="color:inherit;">{{ $isArt ? 'Learn more' : 'Read source' }} &rarr;</a></span>
                @endif
            </div>
        </div>
    </section>
    @empty
    <section class="pulse-card pulse-card--news" data-type="news">
        <div class="pulse-card__bg"><div class="media-grid count-1"><div class="tile" style="background:radial-gradient(120% 90% at 60% 20%,#0d3a4c 0%,#0b0b0f 70%)"></div></div></div>
        <div class="pulse-card__content">
            <div class="pulse-card__eyebrow"><span class="dot"></span>Pulse</div>
            <h1 class="pulse-card__title">Nothing here yet</h1>
            <p class="pulse-card__body">Posts from the Buzz page will show up here as art and news, mixed together.</p>
        </div>
    </section>
    @endforelse

</div>

<div class="pulse-rail-actions">
    <div><button aria-label="like"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7.5-4.6-10-9.3C.6 8.1 2.4 4.5 6 4.1c2-.2 3.6.9 6 3.4 2.4-2.5 4-3.6 6-3.4 3.6.4 5.4 4 3 7.6C19.5 16.4 12 21 12 21z"/></svg></button></div>
    <div><button aria-label="comment"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4A9 9 0 0 1 3 15.4 8.4 8.4 0 0 1 11.4 3 8.4 8.4 0 0 1 21 11.5z"/></svg></button></div>
    <button id="pulsePostBtn" aria-label="post"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg></button>
</div>

<div class="pulse-composer-trigger" id="pulseComposerTrigger">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
    Post something
</div>

<div class="pulse-gate" id="pulseGate">
    <div class="pulse-gate__card" id="pulseGateCard">
        <div class="pulse-gate__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.6 6.6L22 9l-5.5 4.4L18 21l-6-4-6 4 1.5-7.6L2 9l7.4-.4z"/></svg></div>
        <h3>Posting needs an account</h3>
        <p>Create a free account and pick a plan to publish into Pulse.</p>
        <div class="pulse-gate__note">Photo(s) and video both work. One image or clip today &mdash; Pulse will tile multiple photos automatically once you add more per post.</div>
        <a href="{{ route('register') }}" class="pulse-gate__primary">Create account &amp; choose a plan</a>
        <button class="pulse-gate__ghost" id="pulseGateClose">Not now</button>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/ScrollToPlugin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.15.0/SplitText.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="{{ asset('js/pulse.js') }}?v={{ time() }}"></script>
@endsection

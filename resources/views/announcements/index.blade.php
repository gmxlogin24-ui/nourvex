@extends('layouts.app-authenticated')

@section('content')
@php
  $apGrad = [
    'orange' => 'linear-gradient(135deg,#F97316,#C2410C)',
    'teal'   => 'linear-gradient(135deg,#0EA5A4,#115E59)',
    'green'  => 'linear-gradient(135deg,#16A34A,#14532D)',
    'purple' => 'linear-gradient(135deg,#7C5CF0,#3B2A8A)',
    'blue'   => 'linear-gradient(135deg,#2563EB,#1E3A8A)',
    'rose'   => 'linear-gradient(135deg,#E11D48,#881337)',
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/ov-announce2.css') }}?v={{ time() }}">
<main class="ap">
  <div class="ap-top">
    <a href="{{ route('dashboard') }}" class="ap-back" aria-label="Back"><i data-lucide="arrow-left"></i></a>
    <h1>Announcements</h1>
  </div>

  @forelse($live as $oa)
    @php
      $bg = $oa->image_url
        ? "linear-gradient(180deg,rgba(0,0,0,.05) 0%,rgba(0,0,0,.55) 100%),url('" . $oa->image_url . "')"
        : ($apGrad[$oa->color] ?? $apGrad['orange']);
    @endphp
    <article class="ap-c {{ $openId === $oa->id ? 'hl' : '' }}" id="ann-{{ $oa->id }}">
      <div class="ap-banner" style="background-image:{{ $bg }}">
        <span class="an-ic"><i data-lucide="megaphone"></i></span>
        @if($oa->created_at && $oa->created_at->gt(now()->subDays(3)))<span class="an-new">New</span>@endif
      </div>
      <div class="ap-body">
        <h2>{{ $oa->title }}</h2>
        @if($oa->body)<p>{{ $oa->body }}</p>@endif
        <div class="ap-meta">
          <span><i data-lucide="clock"></i>{{ $oa->created_at ? $oa->created_at->diffForHumans() : '' }}</span>
          @if($oa->expires_at)<span><i data-lucide="calendar"></i>Ends {{ $oa->expires_at->format('M j') }}</span>@endif
        </div>
        @if($oa->link_url)
          <a href="{{ $oa->link_url }}" class="ap-btn">Open<i data-lucide="external-link"></i></a>
        @endif
      </div>
    </article>
  @empty
    <div class="ap-empty"><i data-lucide="megaphone"></i><p>No announcements right now.</p></div>
  @endforelse

  @if($earlier->count())
    <h3 class="ap-sub">Earlier</h3>
    @foreach($earlier as $oa)
      <article class="ap-c ap-old" id="ann-{{ $oa->id }}">
        <div class="ap-body">
          <h2>{{ $oa->title }}</h2>
          @if($oa->body)<p>{{ $oa->body }}</p>@endif
          <div class="ap-meta">
            <span><i data-lucide="clock"></i>{{ $oa->created_at ? $oa->created_at->format('M j, Y') : '' }}</span>
            <span><i data-lucide="calendar"></i>Ended {{ $oa->expires_at->format('M j') }}</span>
          </div>
        </div>
      </article>
    @endforeach
  @endif
</main>
<script>(function(){var e=document.getElementById('ann-{{ $openId }}');if(e){setTimeout(function(){e.scrollIntoView({behavior:'smooth',block:'center'});},250);}})();</script>
@endsection

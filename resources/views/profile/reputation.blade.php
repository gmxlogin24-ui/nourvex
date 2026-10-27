@extends('layouts.world-shell')

@section('world', 'profile')
@section('title', 'Reputation')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile-redesign.css') }}?v=1">
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">

<style>
  .pf2-back { display:inline-flex; align-items:center; gap:6px; color:var(--mute); font-size:12.5px; font-weight:600; text-decoration:none; margin-bottom:14px; }
  .pf2-back svg { width:15px; height:15px; }
  .pf2-h1 { font-size:21px; font-weight:800; letter-spacing:-.02em; margin:0; color:var(--ink); }
  .pf2-sub { font-size:12px; color:var(--mute); margin:3px 0 16px; }
  .pf2-card { background:#fff; border-radius:18px; padding:16px; box-shadow:var(--sh); margin-bottom:10px; }
  .pf2-rank-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px; }
  .pf2-rank-pill { display:inline-flex; align-items:center; gap:7px; padding:6px 12px; border-radius:99px; background:var(--w-tint); color:var(--w-dark); font-weight:700; font-size:12.5px; }
  .pf2-rank-pill svg { width:15px; height:15px; }
  .pf2-badges { display:flex; gap:6px; flex-wrap:wrap; }
  .pf2-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 9px; border-radius:99px; font-size:10.5px; font-weight:700; }
  .pf2-badge svg { width:11px; height:11px; }
  .pf2-badge.bronze { background:#FDEEDC; color:#B5651D; }
  .pf2-badge.gold { background:#FFF7DB; color:#946800; }
  .pf2-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
  .pf2-stat { padding:14px 6px; border-radius:14px; background:var(--w-tint); text-align:center; }
  .pf2-stat b { display:flex; align-items:center; justify-content:center; gap:4px; font-size:18px; font-weight:800; color:var(--w-dark); }
  .pf2-stat b svg { width:14px; height:14px; }
  .pf2-stat span { font-size:9.5px; color:var(--mute); font-weight:600; }
  .pf2-hero { background:linear-gradient(135deg, var(--w), var(--w-dark)); border-radius:18px; padding:22px 18px; text-align:center; color:#fff; box-shadow:0 14px 26px -14px var(--w); }
  .pf2-hero svg { width:30px; height:30px; margin-bottom:6px; }
  .pf2-hero b { display:block; font-size:16px; font-weight:800; }
  .pf2-hero span { font-size:11.5px; opacity:.85; }
</style>

<div class="wp wp-profile">
  <a href="{{ route('profile.index') }}" class="pf2-back"><i data-lucide="arrow-left"></i> Profile</a>
  <h1 class="pf2-h1">Reputation</h1>
  <p class="pf2-sub">How you look to other users</p>

  <div class="pf2-hero" style="margin-bottom:10px;">
    <i data-lucide="{{ $workerLevel['icon'] }}"></i>
    <b>{{ $workerLevel['label'] }}</b>
    <span>{{ $completedGigsCount }} jobs · {{ $averageRating ?? '—' }}★</span>
  </div>

  <div class="pf2-card">
    <div class="pf2-rank-row">
      <span class="pf2-rank-pill"><i data-lucide="{{ $workerLevel['icon'] }}"></i>{{ $workerLevel['label'] }}</span>
      <div class="pf2-badges">
        @if($user->badge_tier === 'bronze')
        <span class="pf2-badge bronze"><i data-lucide="medal"></i>Bronze</span>
        @endif
        @if($user->tier === 'tier3')
        <span class="pf2-badge gold"><i data-lucide="crown"></i>Premium</span>
        @endif
      </div>
    </div>
    <div class="pf2-stats">
      <div class="pf2-stat"><b>{{ $completedGigsCount }}</b><span>Jobs Completed</span></div>
      <div class="pf2-stat"><b>@if($averageRating)<i data-lucide="star"></i>{{ $averageRating }}@else —@endif</b><span>Avg Rating</span></div>
      <div class="pf2-stat"><b>{{ $reviewsReceivedCount }}</b><span>Reviews</span></div>
    </div>
  </div>
</div>
@endsection

@extends('layouts.app-authenticated')
@section('title', 'Profile')
@section('content')
@php
  $pu = auth()->user();
  $pHandle = $pu->username ?? $pu->name ?? 'user';
  $pPhoto = $pu->avatar_url ?? null;
  $pInit = strtoupper(substr($pu->name ?? 'U', 0, 1));
  $pBio = $pu->bio ?? '';
  $pRole = $pu->occupation ?? $pu->headline ?? '';
  $pNote = $pu->status_note ?? 'Obsessed with...';
  $pViews = (int) ($pu->profile_views ?? 0);
  $cnt = function ($rel) use ($pu) {
    try { return method_exists($pu, $rel) ? (int) $pu->$rel()->count() : 0; } catch (\Throwable $e) { return 0; }
  };
  $pPosts = $cnt('posts'); $pFollowers = $cnt('followers'); $pFollowing = $cnt('following');
  $has = fn ($n) => \Illuminate\Support\Facades\Route::has($n);
  $tabs = [
    ['all', 'grid', null, 'All Posts'],
    ['market', null, 'Market Post', 'Market Posts'],
    ['invest', null, 'Investments', 'Investments'],
    ['hustles', null, 'Hustles', 'Hustles'],
    ['games', null, 'Game Activities', 'Game Activities'],
    ['media', 'camera', null, 'Photos'],
  ];
@endphp
<link rel="stylesheet" href="{{ asset('css/profile-layout.css') }}?v={{ time() }}">
<div class="pf">
  <header class="pf-top">
    <a href="{{ $has('posts.index') ? route('posts.index') : '#' }}" class="pf-ic" aria-label="New"><i data-lucide="plus"></i></a>
    <b class="pf-handle">{{ $pHandle }}</b>
    <a href="{{ $has('notifications.index') ? route('notifications.index') : '#' }}" class="pf-ic" aria-label="Alerts"><i data-lucide="bell"></i></a>
    <a href="{{ $has('settings.appearance') ? route('settings.appearance') : '#' }}" class="pf-ic" aria-label="Menu"><i data-lucide="menu"></i></a>
  </header>

  <section class="pf-head">
    <div class="pf-info">
      <div class="pf-name">{{ $pu->name }} <i data-lucide="badge-check"></i></div>
      <div class="pf-stats">
        <div><b>{{ number_format($pPosts) }}</b><span>posts</span></div>
        <div><b>{{ number_format($pFollowers) }}</b><span>followers</span></div>
        <div><b>{{ number_format($pFollowing) }}</b><span>following</span></div>
      </div>
    </div>
    <div class="pf-av">
      <span class="pf-note">{{ $pNote }}</span>
      <span class="pf-pic">@if($pPhoto)<img src="{{ $pPhoto }}" alt="">@else{{ $pInit }}@endif</span>
    </div>
  </section>

  @if($pRole)<div class="pf-role">{{ $pRole }}</div>@endif
  @if($pBio)<p class="pf-bio">{{ $pBio }}</p>@endif

  <a href="{{ $has('dashboard') ? route('dashboard') : '#' }}" class="pf-dash dg-glass">
    <b>Your dashboard</b>
    <small>{{ number_format($pViews) }} views in the last 30 days.</small>
  </a>

  <div class="pf-btns">
    <a href="{{ $has('profile.edit') ? route('profile.edit') : ($has('profile.index') ? route('profile.index') : '#') }}" class="pf-btn dg-glass">Edit profile</a>
    <button type="button" class="pf-btn dg-glass" id="pfShare">Share profile</button>
  </div>

  <nav class="pf-tabs" id="pfTabs">
    @foreach($tabs as $i => $t)
      <a href="#{{ $t[0] }}" data-pf="{{ $t[0] }}" class="{{ $i === 0 ? 'on' : '' }}" aria-label="{{ $t[3] }}">
        @if($t[1])<i data-lucide="{{ $t[1] === 'grid' ? 'layout-grid' : 'camera' }}"></i>@else{{ $t[2] }}@endif
      </a>
    @endforeach
  </nav>

  @foreach($tabs as $i => $t)
    <section class="pf-panel" id="pfP-{{ $t[0] }}" @if($i) hidden @endif>
      <div class="pf-empty">{{ $t[3] }}</div>
    </section>
  @endforeach
</div>
<script>
(function () {
  var tabs = [].slice.call(document.querySelectorAll('#pfTabs a'));
  function show(k) {
    var ok = tabs.some(function (a) { return a.dataset.pf === k; }); if (!ok) k = 'all';
    tabs.forEach(function (a) { a.classList.toggle('on', a.dataset.pf === k); });
    [].slice.call(document.querySelectorAll('.pf-panel')).forEach(function (p) { p.hidden = (p.id !== 'pfP-' + k); });
  }
  tabs.forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.pf); }); });
  var s = document.getElementById('pfShare');
  if (s) s.addEventListener('click', function () {
    var u = location.href.split('#')[0];
    if (navigator.share) { navigator.share({ title: document.title, url: u }).catch(function () {}); }
    else if (navigator.clipboard) { navigator.clipboard.writeText(u); s.textContent = 'Link copied'; }
  });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

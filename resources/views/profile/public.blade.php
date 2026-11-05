@extends('layouts.world-shell')
@section('world', 'profile')
@section('title', $user->name)
@section('content')
@php
  $go = fn ($r, $p = []) => \Illuminate\Support\Facades\Route::has($r) ? route($r, $p) : '#';
  $lvl = data_get($level, 'label');
  $role = implode(' · ', array_filter([$user->business_name ?? null, $lvl]));
  $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
  $img = fn ($l) => $l->image_url ?: optional($l->images->first())->image_url;
@endphp
<link rel="stylesheet" href="{{ asset('css/pfn-public.css') }}?v={{ time() }}">
<main class="pu">
  <div class="pu-cover" @if($user->background_url) style="background-image:url('{{ $user->background_url }}')" @endif>
    <a href="{{ $isMe ? $go('profile.index') : 'javascript:history.back()' }}" class="pu-back" aria-label="Back"><i data-lucide="arrow-left"></i></a>
  </div>
  <section class="pu-head">
    <span class="pu-av">@if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="">@else{{ $initial }}@endif</span>
    <div class="pu-id">
      <h1>{{ $user->username ? '@'.$user->username : $user->name }}@if($user->is_verified_seller)<i data-lucide="badge-check"></i>@endif</h1>
      @if($role)<p>{{ $role }}</p>@endif
      <small>Member since {{ $user->created_at->format('M Y') }}</small>
    </div>
  </section>
  @if(!empty($user->bio))<p class="pu-bio">{{ $user->bio }}</p>@endif

  <div class="pu-stats">
    <div><b>{{ $listings->count() + $gigs->count() }}</b><span>posts</span></div>
    <div><b>{{ $completed }}</b><span>completed</span></div>
    <div><b>{{ $rating ? $rating . ' ★' : '—' }}</b><span>{{ $reviewCount }} reviews</span></div>
    <div><b>{{ number_format($views) }}</b><span>views</span></div>
  </div>

  <div class="pu-btns">
    @if($isMe)
      <a href="{{ $go('profile.account') }}" class="pu-btn">Edit profile</a>
      <button type="button" class="pu-btn" id="puShare">Share profile</button>
    @else
      <a href="{{ $go('cx.index') }}" class="pu-btn main"><i data-lucide="send"></i>Message</a>
      <button type="button" class="pu-btn" id="puShare">Share</button>
    @endif
  </div>

  <nav class="pu-tabs" id="puTabs">
    <a href="#listings" data-t="listings" class="on">Market ({{ $listings->count() }})</a>
    <a href="#hustles" data-t="hustles">Hustles ({{ $gigs->count() }})</a>
    <a href="#reviews" data-t="reviews">Reviews ({{ $reviews->count() }})</a>
    @if($isMe)<a href="#viewers" data-t="viewers">Viewers</a>@endif
  </nav>

  <section class="pu-p" id="puP-listings">
    @if($listings->count())
      <div class="pu-grid">
        @foreach($listings as $l)
          <a href="{{ route('marketplace.show', $l->id) }}" class="pu-card">
            <span class="pu-ci">@if($img($l))<img src="{{ $img($l) }}" alt="" loading="lazy">@else<i data-lucide="shopping-bag"></i>@endif</span>
            <b>{{ $l->title }}</b><em>₦{{ number_format($l->amount) }}</em>
          </a>
        @endforeach
      </div>
    @else<div class="pu-empty">No items for sale right now.</div>@endif
  </section>

  <section class="pu-p" id="puP-hustles" hidden>
    @forelse($gigs as $g)
      <a href="{{ route('gigs.show', $g->id) }}" class="pu-row"><b>{{ $g->title }}</b><span>{{ $g->category }}</span><em>₦{{ number_format($g->amount) }}</em></a>
    @empty<div class="pu-empty">No open hustles right now.</div>@endforelse
  </section>

  <section class="pu-p" id="puP-reviews" hidden>
    @forelse($reviews as $r)
      <div class="pu-rv"><b>{{ optional($r->reviewer)->name ?? 'User' }}</b><span>{{ str_repeat('★', (int) $r->rating) }}{{ str_repeat('☆', 5 - (int) $r->rating) }}</span>@if($r->comment)<p>{{ $r->comment }}</p>@endif</div>
    @empty<div class="pu-empty">No reviews yet.</div>@endforelse
  </section>

  @if($isMe)
    <section class="pu-p" id="puP-viewers" hidden>
      @forelse($viewers as $v)
        <a href="{{ route('profile.public', $v['user']->id) }}" class="pu-row"><b>{{ $v['user']->name }}</b><span>{{ $v['at']->diffForHumans() }}</span></a>
      @empty<div class="pu-empty">Nobody has viewed your profile yet.</div>@endforelse
    </section>
  @endif
</main>
<script>
(function () {
  var tabs = [].slice.call(document.querySelectorAll('#puTabs a'));
  function show(k) {
    if (!tabs.some(function (a) { return a.dataset.t === k; })) k = 'listings';
    tabs.forEach(function (a) { a.classList.toggle('on', a.dataset.t === k); });
    [].forEach.call(document.querySelectorAll('.pu-p'), function (p) { p.hidden = p.id !== 'puP-' + k; });
  }
  tabs.forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.t); history.replaceState(null, '', '#' + a.dataset.t); }); });
  show((location.hash || '').replace('#', ''));
  var sh = document.getElementById('puShare');
  if (sh) sh.addEventListener('click', function () {
    var u = location.href.split('#')[0];
    if (navigator.share) { navigator.share({ title: document.title, url: u }).catch(function () {}); }
    else if (navigator.clipboard) { navigator.clipboard.writeText(u); sh.textContent = 'Link copied'; }
  });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

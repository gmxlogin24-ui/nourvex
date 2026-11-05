@extends('layouts.world-shell')

@section('world', 'profile')
@section('title', 'Profile')

@section('content')
@php
  $u = $user;
  $go = function ($r, $p = []) { try { return \Illuminate\Support\Facades\Route::has($r) ? route($r, $p) : '#'; } catch (\Throwable $e) { return '#'; } };
  $safe = function (callable $f) { try { return $f(); } catch (\Throwable $e) { return collect(); } };
  $tierLabel = $u->tier === 'tier1' ? 'Plus' : ($u->tier === 'tier2' ? 'Pro' : ($u->tier === 'tier3' ? 'Premium' : 'Free'));
  $qBal = (int) floor((float) ($u->wallet_balance ?? 0));
  $handle = $u->username ?? $u->name;
  $initial = strtoupper(substr($u->name ?? 'U', 0, 1));
  $note = $u->status_note ?? ($currentLevel?->name ?? ($tierLabel . ' member'));
  $roleLine = implode(' · ', array_filter([$u->business_name ?? null, $tierLabel . ' member', $currentLevel?->name ?? null]));
  $postsCount = (int) ($myListingsPosted ?? 0) + (int) ($myGigsPosted ?? 0);
  try { $unread = (int) $u->unreadNotifications()->count(); } catch (\Throwable $e) { $unread = 0; }

  $pfListings = $safe(fn () => \App\Models\Listing::where('user_id', $u->id)->latest()->take(20)->get());
  $pfGigs = $safe(fn () => \App\Models\Gig::where('user_id', $u->id)->latest()->take(20)->get());
  $pfInv = $safe(fn () => \App\Models\Investment::where('user_id', $u->id)->latest()->take(20)->get());
  $pfProj = $safe(fn () => \App\Models\InvestmentProject::whereIn('id', $pfInv->pluck('investment_project_id'))->get()->keyBy('id'));
  $pfActs = $safe(fn () => \App\Models\UserActivity::where('user_id', $u->id)->latest()->take(60)->get());

  $market = []; $hustles = []; $invest = []; $games = []; $photos = [];
  foreach ($pfListings as $l) {
    $market[] = ['icon' => 'shopping-bag', 'title' => $l->title, 'meta' => implode(' · ', array_filter([$l->category ?? null, $l->status ?? null])), 'amt' => '₦' . number_format((float) $l->amount), 'img' => $l->image_url ?? null, 'url' => $go('marketplace.show', $l), 'at' => $l->created_at];
    if (!empty($l->image_url)) $photos[] = ['img' => $l->image_url, 'url' => $go('marketplace.show', $l)];
  }
  foreach ($pfGigs as $g) {
    $hustles[] = ['icon' => 'briefcase', 'title' => $g->title, 'meta' => implode(' · ', array_filter([$g->category ?? null, $g->status ?? null])), 'amt' => '₦' . number_format((float) $g->amount), 'url' => $go('gigs.show', $g), 'at' => $g->created_at];
  }
  foreach ($pfInv as $i) {
    $p = $pfProj[$i->investment_project_id] ?? null;
    $invest[] = ['icon' => 'trending-up', 'title' => $p->title ?? $p->name ?? 'Investment', 'meta' => $i->status ?? '', 'amt' => '₦' . number_format((float) $i->amount_q), 'url' => $go('investment.index'), 'at' => $i->created_at];
  }
  foreach ($pfActs as $a) {
    if (!preg_match('/game|golf|play/i', ($a->category ?? '') . ' ' . ($a->action ?? ''))) continue;
    $games[] = ['icon' => 'gamepad-2', 'title' => ucfirst(str_replace(['_', '-'], ' ', $a->action ?? 'Game activity')), 'meta' => implode(' · ', array_filter([$a->category ?? null, optional($a->created_at)->diffForHumans()])), 'url' => $go('games.index'), 'at' => $a->created_at];
  }
  $all = array_merge($market, $hustles, $invest);
  usort($all, fn ($a, $b) => strtotime((string) ($b['at'] ?? 0)) <=> strtotime((string) ($a['at'] ?? 0)));

  $tabs = [
    ['all', 'layout-grid', null, 'All Posts', $all],
    ['market', null, 'Market Post', 'Market Posts', $market],
    ['invest', null, 'Investments', 'Investments', $invest],
    ['hustles', null, 'Hustles', 'Hustles', $hustles],
    ['games', null, 'Game Activities', 'Game Activities', $games],
    ['media', 'camera', null, 'Photos', $photos],
  ];
  $cta = ['all' => [$go('marketplace.index'), 'Explore the marketplace'], 'market' => [$go('marketplace.index'), 'Open marketplace'], 'invest' => [$go('investment.index'), 'See investments'], 'hustles' => [$go('gigs.index'), 'Find hustles'], 'games' => [$go('games.index'), 'Play games'], 'media' => [$go('marketplace.index'), 'Open marketplace']];
  $isAdmin = ($u->is_admin ?? false) || (($u->role ?? '') === 'admin');
@endphp
<link rel="stylesheet" href="{{ asset('css/profile-new.css') }}?v={{ @filemtime(public_path('css/profile-new.css')) }}">
<link rel="stylesheet" href="{{ asset('css/pfn-chat.css') }}?v={{ time() }}">

<div class="pfn">
  @if (session('status'))<div class="pfn-empty" style="padding:10px 0">{{ session('status') }}</div>@endif

  <header class="pfn-top">
    <button type="button" class="pfn-ic" data-sheet="pfnCreate" aria-label="Create"><i data-lucide="plus"></i></button>
    <b class="pfn-handle">{{ $u->name }}</b>
    <a href="{{ $go('cx.index') }}" class="pfn-chat" aria-label="Messages"><i data-lucide="send"></i>@if(($navUnreadChats ?? 0) > 0)<em>{{ $navUnreadChats > 99 ? '99+' : $navUnreadChats }}</em>@endif</a>
    <a href="{{ $go('notifications.index') }}" class="pfn-ic" aria-label="Notifications"><i data-lucide="bell"></i>@if($unread)<em>{{ $unread > 99 ? '99+' : $unread }}</em>@endif</a>
    <button type="button" class="pfn-ic" data-sheet="pfnMenu" aria-label="Menu"><i data-lucide="menu"></i></button>
  </header>

  <section class="pfn-head">
    <div class="pfn-info">
      <div class="pfn-name">{{ $u->username ? '@'.$u->username : '' }}@if($u->is_verified_seller ?? false)<i data-lucide="badge-check"></i>@endif</div>
      <div class="pfn-stats">
        <div><b>{{ number_format($postsCount) }}</b><span>posts</span></div>
        <div><b>{{ number_format(\Illuminate\Support\Facades\Schema::hasTable('profile_views') ? \App\Models\ProfileView::where('profile_user_id', $u->id)->count() : 0) }}</b><span>profile views</span></div>
        <div><b>{{ number_format($reviewsReceivedCount ?? 0) }}</b><span>reviews</span></div>
      </div>
    </div>
    <div class="pfn-av">
      <span class="pfn-note">{{ $note }}</span>
      <span class="pfn-pic">@if($u->avatar_url)<img src="{{ $u->avatar_url }}" alt="">@else{{ $initial }}@endif</span>
    </div>
  </section>

  @if($roleLine)<div class="pfn-role">{{ $roleLine }}</div>@endif
  @if(!empty($u->bio))<p class="pfn-bio">{{ $u->bio }}</p>@endif

  @if(empty($u->bio))<a href="{{ $go('profile.account') }}" class="pfn-addbio">Add a bio</a>@endif

  @php
    $pdV30 = 0; $pdVAll = 0;
    try {
      if (\Illuminate\Support\Facades\Schema::hasTable('profile_views')) {
        $pdVAll = (int) \App\Models\ProfileView::where('profile_user_id', $u->id)->count();
        if (\Illuminate\Support\Facades\Schema::hasColumn('profile_views', 'created_at')) {
          $pdV30 = (int) \App\Models\ProfileView::where('profile_user_id', $u->id)->where('created_at', '>=', now()->subDays(30))->count();
        }
      }
    } catch (\Throwable $e) { $pdV30 = 0; $pdVAll = 0; }
  @endphp
  <link rel="stylesheet" href="{{ asset('css/pfn-dash.css') }}?v={{ time() }}">
  <a href="{{ $go('insights.dashboard') }}" class="pfn-dash">
    <div class="pfn-dash-hd"><b><i data-lucide="trending-up"></i>Your dashboard</b><i data-lucide="chevron-right"></i></div>
    <div class="pfn-dash-sub">{{ number_format($pdV30) }} {{ $pdV30 === 1 ? 'view' : 'views' }} in the last 30 days.</div>
  </a>

  <div class="pfn-btns">
    <a href="{{ $go('profile.account') }}" class="pfn-btn">Edit profile</a>
    <button type="button" class="pfn-btn" id="pfnShare" data-url="{{ $go('profile.preview') }}">Share profile</button>
  </div>

  <nav class="pfn-tabs" id="pfnTabs">
    @foreach($tabs as $i => $t)
      <a href="#{{ $t[0] }}" data-pf="{{ $t[0] }}" class="{{ $i === 0 ? 'on' : '' }}" aria-label="{{ $t[3] }}">@if($t[1])<i data-lucide="{{ $t[1] }}"></i>@else{{ $t[2] }}@endif</a>
    @endforeach
  </nav>

  @foreach($tabs as $i => $t)
    <section class="pfn-panel" id="pfnP-{{ $t[0] }}" @if($i) hidden @endif>
      @if(count($t[4]) === 0)
        <div class="pfn-empty"><b>{{ $t[3] }}</b>Nothing here yet.<br><a href="{{ $cta[$t[0]][0] }}">{{ $cta[$t[0]][1] }}</a></div>
      @elseif($t[0] === 'media')
        <div class="pfn-grid">@foreach($t[4] as $ph)<a href="{{ $ph['url'] }}"><img src="{{ $ph['img'] }}" alt="" loading="lazy"></a>@endforeach</div>
      @else
        <div class="pfn-list">@foreach($t[4] as $it)@include('profile.row', ['it' => $it])@endforeach</div>
      @endif
    </section>
  @endforeach
</div>

<div class="pfn-sheet" id="pfnCreate" hidden>
  <div class="pfn-back" data-close></div>
  <div class="pfn-pane" role="dialog" aria-modal="true">
    <div class="pfn-grab"></div>
    <h3>Create</h3>
    @if($go('marketplace.create') !== '#')<a class="pfn-mi" href="{{ $go('marketplace.create') }}"><i data-lucide="shopping-bag"></i><span>Sell an item</span></a>@endif
    @if($go('gigs.create') !== '#')<a class="pfn-mi" href="{{ $go('gigs.create') }}"><i data-lucide="briefcase"></i><span>Post a hustle</span></a>@endif
    @if($go('posts.index') !== '#')<a class="pfn-mi" href="{{ $go('posts.index') }}"><i data-lucide="file-text"></i><span>My posts</span></a>@endif
    <a class="pfn-mi" href="{{ $go('marketplace.index') }}"><i data-lucide="store"></i><span>Open marketplace</span></a>
    <a class="pfn-mi" href="{{ $go('gigs.index') }}"><i data-lucide="search"></i><span>Find hustles</span></a>
  </div>
</div>

<div class="pfn-sheet" id="pfnMenu" hidden>
  <div class="pfn-back" data-close></div>
  <div class="pfn-pane" role="dialog" aria-modal="true">
    <div class="pfn-grab"></div>
    <h3>Menu</h3>
    @if($isAdmin)<a class="pfn-mi" href="{{ \Illuminate\Support\Facades\Route::has('admin.index') ? route('admin.index') : url('/admin') }}"><i data-lucide="shield-check"></i><span>Admin panel</span></a>@endif
    <a class="pfn-mi" href="{{ $go('profile.account') }}"><i data-lucide="user-cog"></i><span>Account</span></a>
    <a class="pfn-mi" href="{{ $go('profile.appearance') }}"><i data-lucide="palette"></i><span>Appearance</span></a>
    <a class="pfn-mi" href="{{ $go('profile.reputation') }}"><i data-lucide="star"></i><span>Reputation</span><small>{{ $averageRating ?: '—' }} · {{ $reviewsReceivedCount ?? 0 }} reviews</small></a>
    <a class="pfn-mi" href="{{ $go('profile.public', ['user' => $u->id]) }}#viewers"><i data-lucide="eye"></i><span>Who viewed you</span></a>
    <a class="pfn-mi" href="{{ $go('levels-badges.show') }}"><i data-lucide="award"></i><span>Level and badges</span><small>{{ $currentLevel?->name ?? 'Unranked' }}</small></a>
    <a class="pfn-mi" href="{{ $go('subscriptions.index') }}"><i data-lucide="crown"></i><span>Plan</span><small>{{ $tierLabel }}@if($u->subscription_expires_at) · {{ $u->subscription_expires_at->format('M j, Y') }}@endif</small></a>
    <a class="pfn-mi" href="{{ $go('referrals.index') }}"><i data-lucide="users"></i><span>Referrals</span><small>{{ $allReferralsCount ?? 0 }} · {{ $activeReferralsCount ?? 0 }} active</small></a>
    @if(!empty($u->referral_code))<button type="button" class="pfn-mi" id="pfnCode" data-code="{{ $u->referral_code }}"><i data-lucide="copy"></i><span>Copy referral code</span><small>{{ $u->referral_code }}</small></button>@endif
    <a class="pfn-mi" href="{{ $go('money-tracker.index') }}"><i data-lucide="line-chart"></i><span>Money tracker</span></a>
    <a class="pfn-mi" href="{{ $go('qcoin.index') }}"><i data-lucide="gem"></i><span>Q-Coin</span><small>{{ number_format($qBal) }} Q</small></a>
    <a class="pfn-mi" href="{{ $go('wallet.index') }}"><i data-lucide="wallet"></i><span>Wallet</span></a>
    <a class="pfn-mi" href="{{ $go('leaderboard.index') }}"><i data-lucide="trophy"></i><span>Rankings</span><small>{{ ($profileLeaderboardRank ?? null) ? '#' . $profileLeaderboardRank : 'Join the board' }}</small></a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="pfn-mi out"><i data-lucide="log-out"></i><span>Log out</span></button></form>
  </div>
</div>

<script>
(function () {
  var bar = document.querySelector('.pb-bar'); if (bar) bar.style.setProperty('display', 'none', 'important');
  document.body.style.setProperty('padding-top', '0', 'important');
  var tabs = [].slice.call(document.querySelectorAll('#pfnTabs a'));
  function show(k) {
    if (!tabs.some(function (a) { return a.dataset.pf === k; })) k = 'all';
    tabs.forEach(function (a) { a.classList.toggle('on', a.dataset.pf === k); });
    [].forEach.call(document.querySelectorAll('.pfn-panel'), function (p) { p.hidden = (p.id !== 'pfnP-' + k); });
  }
  tabs.forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.pf); }); });
  show((location.hash || '').replace('#', ''));

  var open = null;
  [].forEach.call(document.querySelectorAll('.pfn-sheet'), function (s) { document.body.appendChild(s); });
  function openS(id) { var s = document.getElementById(id); if (!s) return; open = s; s.hidden = false; void s.offsetWidth; s.classList.add('open'); document.body.style.overflow = 'hidden'; if (window.lucide) lucide.createIcons(); }
  function closeS() { if (!open) return; var s = open; open = null; s.classList.remove('open'); document.body.style.overflow = ''; setTimeout(function () { s.hidden = true; }, 230); }
  [].forEach.call(document.querySelectorAll('[data-sheet]'), function (b) { b.addEventListener('click', function () { openS(b.dataset.sheet); }); });
  [].forEach.call(document.querySelectorAll('.pfn-sheet [data-close]'), function (b) { b.addEventListener('click', closeS); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeS(); });

  var sh = document.getElementById('pfnShare');
  if (sh) sh.addEventListener('click', function () {
    var u = sh.dataset.url && sh.dataset.url !== '#' ? sh.dataset.url : location.href.split('#')[0];
    if (navigator.share) { navigator.share({ title: document.title, url: u }).catch(function () {}); }
    else if (navigator.clipboard) { navigator.clipboard.writeText(u); sh.textContent = 'Link copied'; }
  });
  var cc = document.getElementById('pfnCode');
  if (cc) cc.addEventListener('click', function () { if (navigator.clipboard) { navigator.clipboard.writeText(cc.dataset.code); cc.querySelector('span').textContent = 'Copied'; } });
  [].forEach.call(document.querySelectorAll('.pfn-hl [data-goto]'), function (b) { b.addEventListener('click', function () { var t = document.querySelector('#pfnTabs a[data-pf="' + b.dataset.goto + '"]'); if (t) { t.click(); t.scrollIntoView({ behavior: 'smooth', block: 'center' }); } }); });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

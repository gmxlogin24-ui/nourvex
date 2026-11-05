@extends('layouts.app-authenticated')
@section('title', 'Messages')
@section('content')
@php
  $cfg = [
    'profile' => ['title' => 'Messages', 'sub' => 'Your personal chats', 'back' => route('profile.index'), 'add' => 'New', 'addUrl' => null, 't1' => null, 't2' => null, 'empty' => 'No personal chats yet. Tap New to message someone.'],
    'hustles' => ['title' => 'Hustle chats', 'sub' => 'Jobs you posted or applied for', 'back' => route('gigs.index'), 'add' => 'Jobs', 'addUrl' => route('gigs.index'), 't1' => 'Posted', 't2' => 'Applied', 'empty' => 'No hustle chats yet. Apply for a job or post one.'],
    'market' => ['title' => 'Market chats', 'sub' => 'Buyers and sellers', 'back' => route('marketplace.index'), 'add' => 'Shop', 'addUrl' => route('marketplace.index'), 't1' => 'Selling', 't2' => 'Buying', 'empty' => 'No market chats yet. Ask a seller about an item.'],
  ][$world];
@endphp
<link rel="stylesheet" href="{{ asset('css/inbox-worlds.css') }}?v={{ time() }}">
<div class="ibx ibx-{{ $world }}" id="ibx">
<main class="ib ib-{{ $world }}">
  <header class="ib-hd">
    <a href="{{ $cfg['back'] }}" class="ib-back" aria-label="Back"><i data-lucide="arrow-left"></i></a>
    <div class="ib-ttl"><h1>{{ $cfg['title'] }}</h1><span>{{ $cfg['sub'] }}</span></div>
    <span class="ib-cnt">{{ $rows->count() }} {{ $rows->count() === 1 ? 'chat' : 'chats' }}</span>
  </header>

  @if($world === 'profile')
  <form method="POST" action="{{ route('chat.start') }}" class="ib-new" id="ibNew" hidden>
    @csrf
    <i data-lucide="at-sign"></i>
    <input type="text" name="to" placeholder="username to message" autocomplete="off" required>
    <button type="submit" aria-label="Start chat"><i data-lucide="arrow-right"></i></button>
  </form>
  @endif

  <div class="ib-st" id="ibSt">
    @if($world === 'profile')
      <button type="button" class="ib-s ib-s-add" data-ibnew><span class="ib-ring ib-ring-add"><i data-lucide="plus"></i></span><small>{{ $cfg['add'] }}</small></button>
    @else
      <a href="{{ $cfg['addUrl'] }}" class="ib-s ib-s-add"><span class="ib-ring ib-ring-add"><i data-lucide="plus"></i></span><small>{{ $cfg['add'] }}</small></a>
    @endif
    @foreach($stories as $s)
      <a href="{{ $s['link'] }}" class="ib-s">
        <span class="ib-ring {{ $s['unread'] ? 'on' : '' }}"><span class="ib-av">@if($s['avatar'])<img src="{{ $s['avatar'] }}" alt="">@else{{ strtoupper(substr(ltrim($s['name'], '@'), 0, 1)) }}@endif</span></span>
        <small>{{ \Illuminate\Support\Str::limit(ltrim($s['name'], '@'), 9, '') }}</small>
      </a>
    @endforeach
  </div>

  <div class="ib-tabs" id="ibTabs">
    <button type="button" class="on" data-f="all">All</button>
    <button type="button" data-f="unread">Unread @if($unreadTotal)<em>{{ $unreadTotal }}</em>@endif</button>
    @if($cfg['t1'])<button type="button" data-f="mine">{{ $cfg['t1'] }}</button>@endif
    @if($cfg['t2'])<button type="button" data-f="other">{{ $cfg['t2'] }}</button>@endif
  </div>

  <label class="ib-q"><i data-lucide="search"></i><input type="text" id="ibQ" placeholder="Search chats" autocomplete="off"></label>

  <div id="ibBody">
    <div class="ib-list" id="ibList">
      @foreach($rows as $r)
        <a href="{{ $r['link'] }}" class="ib-row {{ $r['unread'] ? 'unread' : '' }}" data-unread="{{ $r['unread'] ? 1 : 0 }}" data-role="{{ $r['role'] }}" data-text="{{ mb_strtolower($r['name'] . ' ' . $r['title'] . ' ' . $r['body']) }}">
          <span class="ib-pic {{ $world === 'market' ? 'sq' : '' }}">
            @if($world === 'market' && $r['thumb'])<img src="{{ $r['thumb'] }}" alt="">
            @elseif($r['avatar'])<img src="{{ $r['avatar'] }}" alt="">
            @else{{ strtoupper(substr(ltrim($r['name'], '@'), 0, 1)) }}@endif
          </span>
          <span class="ib-bd">
            <span class="ib-t"><b>{{ $r['name'] }}</b>@if($r['at'])<small>{{ $r['at']->isToday() ? $r['at']->format('g:ia') : $r['at']->format('M j') }}</small>@endif</span>
            @if($r['title'] !== '')<span class="ib-sj"><i data-lucide="{{ $world === 'market' ? 'tag' : 'briefcase' }}"></i>{{ \Illuminate\Support\Str::limit($r['title'], 34) }}</span>@endif
            <span class="ib-p"><span>{{ $r['body'] !== '' ? \Illuminate\Support\Str::limit($r['body'], 60) : 'No messages yet' }}</span>@if($r['unread'] > 0)<i class="ib-n">{{ $r['unread'] > 99 ? '99+' : $r['unread'] }}</i>@endif</span>
          </span>
        </a>
      @endforeach
    </div>
    @if($rows->isEmpty())
      <div class="ib-empty"><i data-lucide="message-circle"></i><p>{{ $cfg['empty'] }}</p></div>
    @endif
  </div>
  <p class="ib-none" id="ibNone" hidden>Nothing matches.</p>
</main>
</div>
<script>
(function () {
  var ibx = document.getElementById('ibx');
  if (ibx && ibx.parentNode !== document.body) document.body.appendChild(ibx);
  var f = 'all';
  var q = document.getElementById('ibQ');
  var none = document.getElementById('ibNone');
  function apply() {
    var rows = [].slice.call(document.querySelectorAll('.ib-row'));
    var t = (q.value || '').toLowerCase().trim(), n = 0;
    rows.forEach(function (r) {
      var ok = (f === 'all' || (f === 'unread' && r.dataset.unread === '1') || (f === 'mine' && r.dataset.role === 'mine') || (f === 'other' && r.dataset.role === 'other')) && (!t || r.dataset.text.indexOf(t) > -1);
      r.hidden = !ok; if (ok) n++;
    });
    none.hidden = n > 0 || rows.length === 0;
  }
  document.getElementById('ibTabs').addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b) return;
    [].forEach.call(this.querySelectorAll('button'), function (x) { x.classList.remove('on'); });
    b.classList.add('on'); f = b.dataset.f; apply();
  });
  q.addEventListener('input', apply);
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-ibnew]'), nf = document.getElementById('ibNew');
    if (!b || !nf) return;
    nf.hidden = !nf.hidden;
    if (!nf.hidden) { var i = nf.querySelector('input'); if (i) i.focus(); }
  });
  function swap(d, id) {
    var a = document.getElementById(id), b = d.getElementById(id);
    if (a && b) a.innerHTML = b.innerHTML;
  }
  function refresh() {
    if (document.hidden) return;
    fetch(location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.text(); })
      .then(function (h) {
        var d = new DOMParser().parseFromString(h, 'text/html');
        swap(d, 'ibBody'); swap(d, 'ibSt');
        var nu = d.querySelector('#ibTabs [data-f="unread"]'), cu = document.querySelector('#ibTabs [data-f="unread"]');
        if (nu && cu) cu.innerHTML = nu.innerHTML;
        var nc = d.querySelector('.ib-cnt'), cc = document.querySelector('.ib-cnt');
        if (nc && cc) cc.textContent = nc.textContent;
        if (window.lucide) lucide.createIcons();
        apply();
      })
      .catch(function () {});
  }
  setInterval(refresh, 20000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

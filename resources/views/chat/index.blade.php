@extends('layouts.app-authenticated')
@section('title', 'Messages')
@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboard-glass.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dg-desktop-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-desktop.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/ovm-layout.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mono.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/grid-fit.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/dash-shade.css') }}?v={{ @filemtime(public_path('css/dash-shade.css')) }}">
<link rel="stylesheet" href="{{ asset('css/chat-inbox.css') }}?v={{ time() }}">
<div class="dg" id="dg"><div class="dg-in ovm">
<div class="ct">
  <header class="ct-head"><h1>Messages</h1></header>
  @if($errors->any())<div class="ct-err">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route('chat.start') }}" class="ct-new dg-glass">
    @csrf
    <i data-lucide="pencil"></i>
    <input type="text" name="to" value="{{ old('to') }}" placeholder="Message someone: &#64;username" autocomplete="off" required>
    <button type="submit" class="btn-primary ct-go" aria-label="Start chat"><i data-lucide="arrow-right"></i></button>
  </form>
  <div class="ct-chips" id="ctChips">
    <button type="button" class="on" data-f="all">All</button>
    <button type="button" data-f="unread">Unread @if($unreadTotal)<em>{{ $unreadTotal }}</em>@endif</button>
    <button type="button" data-f="hustle">Hustles</button>
    <button type="button" data-f="market">Market</button>
    <button type="button" data-f="direct">Direct</button>
    <button type="button" data-f="people">People</button>
  </div>
  <label class="ct-search dg-glass"><i data-lucide="search"></i><input type="text" id="ctQ" placeholder="Search" autocomplete="off"></label>
  <div class="ct-list" id="ctList">
    @foreach($conversations as $c)
      @php $ctTag = ['hustle' => 'Hustle', 'market' => 'Market', 'direct' => 'Direct'][$c['kind']] ?? 'Direct'; @endphp
      <a href="{{ $c['link'] }}" class="ct-row {{ $c['unread'] ? 'unread' : '' }}" data-kind="{{ $c['kind'] }}" data-unread="{{ $c['unread'] ? 1 : 0 }}" data-text="{{ mb_strtolower($c['other_name'] . ' ' . $c['last_body'] . ' ' . $c['subject_title']) }}">
        <span class="ct-av">@if($c['other_avatar'])<img src="{{ $c['other_avatar'] }}" alt="">@else{{ strtoupper(substr($c['other_name'], 0, 1)) }}@endif</span>
        <span class="ct-bd">
          <span class="ct-t"><b>{{ $c['other_name'] }}</b>@if($c['last_at'])<small>{{ $c['last_at']->isToday() ? $c['last_at']->format('g:ia') : $c['last_at']->format('M j') }}</small>@endif</span>
          <span class="ct-p"><em class="ct-tag">{{ $ctTag }}</em><span>{{ $c['subject_title'] !== '' ? $c['subject_title'] . ($c['last_body'] !== '' ? ' · ' : '') : '' }}{{ $c['last_body'] }}</span>@if($c['unread'] > 0)<i class="ct-un">{{ $c['unread'] }}</i>@endif</span>
        </span>
      </a>
    @endforeach
  </div>
  @if($conversations->isEmpty())<p class="ct-empty" id="ctEmpty">No chats yet. Open the People tab to start one.</p>@endif
  <div class="ct-people" id="ctPeople" hidden>
    @foreach($people as $p)
      <form method="POST" action="{{ route('chat.start') }}" class="ct-person" data-text="{{ mb_strtolower($p['name']) }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $p['id'] }}">
        <button type="submit" class="ct-prow">
          <span class="ct-av">@if($p['avatar'])<img src="{{ $p['avatar'] }}" alt="">@else{{ strtoupper(substr($p['name'], 0, 1)) }}@endif</span>
          <span class="ct-bd"><span class="ct-t"><b>{{ $p['name'] }}</b></span><span class="ct-p"><em class="ct-tag">{{ $p['tag'] }}</em></span></span>
        </button>
      </form>
    @endforeach
    @if(count($people) === 0)<p class="ct-empty">No one here yet. Share your referral link to build your team.</p>@endif
    <a href="{{ route('subscriptions.index') }}" class="ct-up dg-glass"><b>Who you can message</b><span>Free: your contacts and upline. Silver: plus your direct team. Gold: plus your team's team. Premium: anyone.</span></a>
  </div>
  <p class="ct-empty" id="ctNone" hidden>Nothing matches.</p>
</div>
</div></div>
<script>
(function () {
  var rows = [].slice.call(document.querySelectorAll('.ct-row'));
  var persons = [].slice.call(document.querySelectorAll('.ct-person'));
  var chips = [].slice.call(document.querySelectorAll('#ctChips button'));
  var q = document.getElementById('ctQ');
  var none = document.getElementById('ctNone');
  var list = document.getElementById('ctList');
  var people = document.getElementById('ctPeople');
  var emp = document.getElementById('ctEmpty');
  var f = 'all';
  function apply() {
    var t = (q.value || '').toLowerCase().trim(), shown = 0, isP = f === 'people';
    list.hidden = isP;
    if (emp) emp.hidden = isP;
    people.hidden = !isP;
    rows.forEach(function (r) {
      var ok = !isP && (f === 'all' || (f === 'unread' ? r.dataset.unread === '1' : r.dataset.kind === f)) && (!t || r.dataset.text.indexOf(t) > -1);
      r.hidden = !ok;
      if (ok) shown++;
    });
    persons.forEach(function (p) {
      var ok = isP && (!t || p.dataset.text.indexOf(t) > -1);
      p.hidden = !ok;
      if (ok) shown++;
    });
    none.hidden = shown > 0 || (isP ? persons.length === 0 : rows.length === 0);
  }
  chips.forEach(function (c) {
    c.addEventListener('click', function () {
      chips.forEach(function (x) { x.classList.remove('on'); });
      c.classList.add('on');
      f = c.dataset.f;
      apply();
    });
  });
  q.addEventListener('input', apply);
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

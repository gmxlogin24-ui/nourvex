@extends('layouts.app-authenticated')
@section('title', 'Chat')
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
  <header class="ct-th-head">
    <a href="{{ route('chat.index') }}" class="ct-back" aria-label="Back"><i data-lucide="chevron-left"></i></a>
    <span class="ct-av">@if($other && $other->avatar_url)<img src="{{ $other->avatar_url }}" alt="">@else{{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}@endif</span>
    <b>{{ ($other->username ?? null) ? '@'.$other->username : ($other->name ?? 'Unknown') }}</b>
  </header>
  <div class="ct-msgs" id="ctMsgs" data-me="{{ auth()->id() }}" data-url="{{ route('chat.messages', $conversation->id) }}"></div>
  <form class="ct-comp dg-glass" id="ctForm" action="{{ route('chat.send', $conversation->id) }}" method="POST">
    @csrf
    <input type="text" id="ctInp" name="body" placeholder="Write a message" maxlength="1000" autocomplete="off" required>
    <button type="submit" class="btn-primary ct-go" aria-label="Send"><i data-lucide="send"></i></button>
  </form>
</div>
</div></div>
<script>
(function () {
  var list = document.getElementById('ctMsgs');
  var form = document.getElementById('ctForm');
  var inp = document.getElementById('ctInp');
  var token = form.querySelector('[name=_token]').value;
  var me = Number(list.dataset.me), url = list.dataset.url, last = -1;
  function render(rows) {
    var key = rows.length ? rows[rows.length - 1].id : 0;
    if (key === last) return;
    var first = last === -1;
    last = key;
    var near = list.scrollHeight - list.scrollTop - list.clientHeight < 140;
    list.textContent = '';
    rows.forEach(function (m) {
      var d = document.createElement('div');
      d.className = 'ct-m ' + (m.sender_id === me ? 'me' : 'them');
      var b = document.createElement('div');
      b.className = 'ct-b';
      if (m.message_type === 'image' && m.meta && m.meta.url) {
        var im = document.createElement('img');
        im.src = m.meta.url;
        b.appendChild(im);
      }
      if (m.body) {
        var p = document.createElement('p');
        p.textContent = m.body;
        b.appendChild(p);
      }
      var t = document.createElement('small');
      t.textContent = m.created_at;
      d.appendChild(b);
      d.appendChild(t);
      list.appendChild(d);
    });
    if (first || near) list.scrollTop = list.scrollHeight;
  }
  function load() {
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); }).then(render).catch(function () {});
  }
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var body = inp.value.trim();
    if (!body) return;
    inp.value = '';
    fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      body: JSON.stringify({ body: body })
    }).then(function () { last = -1; load(); }).catch(function () {});
  });
  setInterval(load, 4000);
  load();
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection

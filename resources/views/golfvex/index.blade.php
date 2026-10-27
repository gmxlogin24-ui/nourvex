@extends('layouts.app-authenticated')
@section('title', 'NaGolf')
@section('content')
@php
  $first = array_key_first($clubs);
  $clubsJson = json_encode($clubs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  $initial = strtoupper(substr(auth()->user()->name, 0, 1));
@endphp
<link rel="stylesheet" href="{{ asset('css/golfvex-stage.css') }}?v={{ time() }}">

<div class="gx">
  <div class="gx-stage">
    <div class="gx-bg" aria-hidden="true"></div>

    <header class="gx-head">
      <div class="gx-av">{{ $initial }}</div>
      <div class="gx-brand">
        <svg viewBox="0 0 40 40" aria-hidden="true">
          <defs>
            <pattern id="gxDm" width="4" height="4" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r=".7" fill="#9aa79a"/></pattern>
          </defs>
          <path d="M3 30c9 7 24 6 34-4" fill="none" stroke="#C6F135" stroke-width="3" stroke-linecap="round"/>
          <ellipse cx="27" cy="29" rx="4.4" ry="1.6" fill="#C6F135"/>
          <path d="M27 29V6" stroke="#C6F135" stroke-width="2.4" stroke-linecap="round"/>
          <path d="M27 6l10 4-10 4z" fill="#C6F135"/>
          <circle cx="14" cy="24" r="7.5" fill="#fff"/>
          <circle cx="14" cy="24" r="7.5" fill="url(#gxDm)"/>
          <circle cx="11.8" cy="21.6" r="2.4" fill="#fff" opacity=".75"/>
        </svg>
        <span>GOLF<b>VEX</b></span>
      </div>
      <div class="gx-bal"><i>Q</i><span id="gvBal">{{ number_format($balance) }}</span></div>
    </header>

    <aside class="gx-left gx-glass">
      <div class="gx-title">Clubs</div>
      @foreach($clubs as $key => $c)
        <button type="button" class="gx-club {{ $key === $first ? 'on' : '' }}" data-club="{{ $key }}">
          <span class="gx-club-n">{{ $c['name'] }}</span>
          <span class="gx-club-s"><b>{{ $c['stake'] }}</b> stake · <b>{{ $c['chance'] }}%</b> · <b>{{ $c['payout'] }}</b> win</span>
        </button>
      @endforeach
      <div class="gx-limit"><span>Daily limit</span><b>{{ $cap }} swings</b></div>
    </aside>

    <main class="gx-hero" id="gvHero">
      <div class="gx-target" id="gvTarget"></div>
      <div class="gx-ball" id="gvBall"></div>
      <div class="gx-result" id="gvResult"><b id="gvResB"></b><span id="gvResS"></span></div>
    </main>

    <section class="gx-sheet gx-glass" id="gvSheet">
      <div class="gx-tabs" role="tablist">
        <button type="button" data-t="play" class="on">Play</button>
        <button type="button" data-t="rules">Rules</button>
        <button type="button" data-t="history">History</button>
      </div>
      <div class="gx-body">
        <div class="gx-panel on" data-p="play">
          <div class="gx-ph" id="gvClubName">{{ $clubs[$first]['name'] }}</div>
          <p class="gx-note" id="gvClubNote">{{ $clubs[$first]['note'] }}</p>
          <p>Stake <b id="gvT1">{{ $clubs[$first]['stake'] }} Q</b>, <b id="gvT2">{{ $clubs[$first]['chance'] }}%</b> chance to win <b id="gvT3">{{ $clubs[$first]['payout'] }} Q</b>.</p>
          <p>Tap the big ball to swing. Use the arrows or the club list to change club.</p>
          <p class="gx-msg" id="gvMsg">{{ $played }} of {{ $cap }} swings used today</p>
        </div>

        <div class="gx-panel" data-p="rules">
          <p>Pay the stake, swing, and the ball lands on the flag or in a hazard. The result is decided on the server.</p>
          @foreach($clubs as $c)
            <p><b>{{ $c['name'] }}</b>: pay <b>{{ $c['stake'] }} Q</b>, <b>{{ $c['chance'] }}%</b> chance to win <b>{{ $c['payout'] }} Q</b>.</p>
          @endforeach
          <p>Up to <b>{{ $cap }} swings</b> a day. Q Coins are test coins right now and are not redeemable.</p>
        </div>

        <div class="gx-panel" data-p="history">
          <div id="gvList">
            @forelse($history as $h)
              <div class="gx-row">
                <span>{{ $h->description }}<small>{{ $h->created_at->diffForHumans() }}</small></span>
                <b class="{{ $h->amount >= 0 ? 'in' : 'out' }}">{{ $h->amount >= 0 ? '+' : '' }}{{ $h->amount }} Q</b>
              </div>
            @empty
              <p class="gx-msg" id="gvNoHist">No swings yet.</p>
            @endforelse
          </div>
        </div>
      </div>
    </section>

    <nav class="gx-nav">
      <button type="button" class="gx-arrow" id="gvPrev" aria-label="Previous club">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
      </button>
      <div class="gx-gb"><b id="gvStake">{{ $clubs[$first]['stake'] }}</b><span>STAKE Q</span></div>
      <button type="button" class="gx-gb gx-hole" id="gvHoleBtn" aria-label="Swing">
        <b id="gvChance">{{ $clubs[$first]['chance'] }}%</b><span>CHANCE</span>
      </button>
      <div class="gx-gb"><b id="gvPay">{{ $clubs[$first]['payout'] }}</b><span>WIN Q</span></div>
      <button type="button" class="gx-arrow" id="gvNext" aria-label="Next club">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
      </button>
    </nav>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  if (window.lucide) lucide.createIcons();

  var PLAY_URL = "{{ route('golfvex.play') }}";
  var CLUBS = {!! $clubsJson !!};
  var KEYS = Object.keys(CLUBS);
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var selected = KEYS[0], busy = false, balance = {{ (int) $balance }};

  var TEE = { x: .5, y: .86 };
  var HOLE = { x: .5, y: .3 };
  var LIES = {
    bunker: [.62, .56, 'Bunker'],
    water: [.26, .5, 'Water hazard'],
    rough: [.8, .32, 'Rough'],
    short: [.46, .62, 'Short of the green']
  };

  var hero = document.getElementById('gvHero');
  var ball = document.getElementById('gvBall');
  var target = document.getElementById('gvTarget');
  var holeBtn = document.getElementById('gvHoleBtn');
  var msg = document.getElementById('gvMsg');
  var balEl = document.getElementById('gvBal');
  var res = document.getElementById('gvResult');
  var resB = document.getElementById('gvResB');
  var resS = document.getElementById('gvResS');
  var list = document.getElementById('gvList');

  target.style.left = (HOLE.x * 100) + '%';
  target.style.top = (HOLE.y * 100) + '%';
  ball.style.left = (TEE.x * 100) + '%';
  ball.style.top = (TEE.y * 100) + '%';

  function setText(id, v) { document.getElementById(id).textContent = v; }

  function pick(id) {
    selected = id;
    var c = CLUBS[id];
    setText('gvStake', c.stake);
    setText('gvChance', c.chance + '%');
    setText('gvPay', c.payout);
    setText('gvClubName', c.name);
    setText('gvClubNote', c.note);
    setText('gvT1', c.stake + ' Q');
    setText('gvT2', c.chance + '%');
    setText('gvT3', c.payout + ' Q');
    [].forEach.call(document.querySelectorAll('.gx-club'), function (b) {
      b.classList.toggle('on', b.dataset.club === id);
    });
  }

  function step(n) {
    if (busy) return;
    var i = KEYS.indexOf(selected);
    pick(KEYS[(i + n + KEYS.length) % KEYS.length]);
  }

  document.getElementById('gvPrev').addEventListener('click', function () { step(-1); });
  document.getElementById('gvNext').addEventListener('click', function () { step(1); });
  [].forEach.call(document.querySelectorAll('.gx-club'), function (b) {
    b.addEventListener('click', function () { if (!busy) pick(b.dataset.club); });
  });

  [].forEach.call(document.querySelectorAll('.gx-tabs button'), function (b) {
    b.addEventListener('click', function () {
      [].forEach.call(document.querySelectorAll('.gx-tabs button'), function (x) { x.classList.toggle('on', x === b); });
      [].forEach.call(document.querySelectorAll('.gx-panel'), function (p) { p.classList.toggle('on', p.dataset.p === b.dataset.t); });
    });
  });

  function setBalance(to) {
    balance = to;
    balEl.textContent = Number(to).toLocaleString('en-US');
  }

  function showResult(title, sub) {
    resB.textContent = title;
    resS.textContent = sub;
    res.classList.add('show');
    clearTimeout(showResult.t);
    showResult.t = setTimeout(function () { res.classList.remove('show'); }, 2400);
  }

  function fly(r) {
    var h = hero.getBoundingClientRect();
    var to = r.won ? HOLE : (LIES[r.lie] ? { x: LIES[r.lie][0], y: LIES[r.lie][1] } : { x: .46, y: .62 });
    ball.style.setProperty('--dx', ((to.x - TEE.x) * h.width) + 'px');
    ball.style.setProperty('--dy', ((to.y - TEE.y) * h.height) + 'px');
    ball.classList.remove('go');
    void ball.offsetWidth;
    if (!reduce) ball.classList.add('go');
  }

  function addHistory(won, stake, payout) {
    var none = document.getElementById('gvNoHist');
    if (none) none.remove();
    var row = document.createElement('div');
    row.className = 'gx-row';
    var left = document.createElement('span');
    left.textContent = 'NaGolf ' + CLUBS[selected].name + (won ? ' hole out' : ' swing');
    var small = document.createElement('small');
    small.textContent = 'just now';
    left.appendChild(small);
    var amt = document.createElement('b');
    amt.className = won ? 'in' : 'out';
    amt.textContent = (won ? '+' + payout : '-' + stake) + ' Q';
    row.appendChild(left);
    row.appendChild(amt);
    list.insertBefore(row, list.firstChild);
  }

  holeBtn.addEventListener('click', function () {
    if (busy) return;
    busy = true;
    holeBtn.disabled = true;
    msg.textContent = 'Swinging…';

    fetch(PLAY_URL, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ club: selected })
    })
    .then(function (r) { return r.json().then(function (j) { return { s: r.status, j: j }; }); })
    .then(function (o) {
      var r = o.j;
      if (!r.ok) {
        msg.textContent = r.message || 'Could not swing right now.';
        busy = false;
        holeBtn.disabled = false;
        return;
      }
      fly(r);
      setTimeout(function () {
        if (r.won) {
          showResult('Hole out! +' + r.payout + ' Q', 'You paid ' + r.stake + ' Q');
        } else {
          showResult(LIES[r.lie] ? LIES[r.lie][2] : 'Missed', '-' + r.stake + ' Q. Try again');
        }
        setBalance(r.balance);
        addHistory(r.won, r.stake, r.payout);
        msg.textContent = r.played + ' of ' + r.cap + ' swings used today';
        busy = false;
        holeBtn.disabled = (r.played >= r.cap);
      }, reduce ? 0 : 900);
    })
    .catch(function () {
      msg.textContent = 'Network problem. Try again.';
      busy = false;
      holeBtn.disabled = false;
    });
  });

  pick(selected);
})();
</script>
@endsection

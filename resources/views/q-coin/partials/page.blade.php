@php
    $qlScale = round(.18 * 64 / .48, 1);
    $qlLo = (int) round(255 * .02);
    $qlHi = (int) round(255 * .98);
    $qlMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,128,0)'/><stop offset='.3' stop-color='rgb(0,{$qlLo},0)'/><stop offset='.7' stop-color='rgb(0,{$qlHi},0)'/><stop offset='1' stop-color='rgb(0,128,0)'/></linearGradient></defs><rect width='100' height='100' fill='rgb(128,0,0)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <filter id="qLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
        <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($qlMap) }}" preserveAspectRatio="none" result="m"/>
        <feDisplacementMap in="SourceGraphic" in2="m" scale="{{ $qlScale }}" xChannelSelector="R" yChannelSelector="G"/>
    </filter>
</svg>
@php
  $qrUser = auth()->user();
  $qrHay = strtolower(implode(' ', [(string) ($qrUser->tier ?? ''), (string) $qrUser->planKey(), (string) $qrUser->planLabel()]));
  $qrTier = (str_contains($qrHay, 'gold') || str_contains($qrHay, 'tier3')) ? 'gold'
      : ((str_contains($qrHay, 'silver') || str_contains($qrHay, 'tier2')) ? 'silver' : 'bronze');
  $qrPlan = ['gold' => 'Gold plan', 'silver' => 'Silver plan', 'bronze' => 'Standard plan'][$qrTier];
  $qrInCycle = ((int) ($streakDays ?? 0)) % 7;
  $qrDark = ($qrUser->theme_style ?? '') === 'qelvo-dark';
@endphp
<link rel="stylesheet" href="{{ asset('css/qcoin-redesign.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/qcoin-glass.css') }}?v={{ time() }}">
<div class="qr qr-tier-{{ $qrTier }} {{ $qrDark ? 'qr-dark' : '' }}">
  @if(config("qcoin.mode", "test") !== "live")<div class="qr-flash ok" style="margin:0 0 4px;">Test Q Coins. Not redeemable yet.</div>@endif
  <div class="qr-col-a">
    @if(session('success'))<div class="qr-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="qr-flash bad">{{ session('error') }}</div>@endif

    <section class="qr-hero">
      <div class="qr-top">
        <span class="qr-plan">{{ $qrPlan }}</span>
        <span class="qr-earn">Base +{{ (int) $dailyBase }} Q a day</span>
      </div>
      @include('q-coin.partials.coin', ['qcTier' => $qrTier, 'qcSize' => 'lg'])
      <div class="qr-bal">
        <p class="qr-lbl">Your balance</p>
        <p class="qr-balance">{{ number_format($balance) }} <span>Q</span></p>
        <p class="qr-sub">{{ $streakDays }} day streak · {{ number_format($todayEarned) }} earned today</p>
      </div>
    </section>

    @include('q-coin.partials.hold-button')
    

    @if($qcoinLocked)
      <a href="{{ route('subscriptions.index') }}" class="qr-link">See plans to unlock daily claims →</a>
    @endif
  </div>

  <div class="qr-col-b">
    <section class="qr-card">
      <div class="qr-streak-head"><span>Streak</span><span>{{ $qrInCycle }}/7 to next bonus</span></div>
      <div class="qr-days">
        @for($d = 1; $d <= 7; $d++)
          <span class="qr-day {{ $d <= $qrInCycle ? 'on' : '' }}">{{ $d }}</span>
        @endfor
      </div>
      <p class="qr-bonus">Base +{{ (int) $dailyBase }} Q · streak bonus +{{ (int) $nextStreakBonus }} Q</p>
    </section>

    <section class="qr-stats">
      <div><span class="v">{{ number_format($todayEarned) }}</span><span class="l">Today</span></div>
      <div><span class="v">{{ number_format($totalEarned) }}</span><span class="l">Earned</span></div>
      <div><span class="v">{{ number_format($totalSpent) }}</span><span class="l">Spent</span></div>
    </section>

    <h2 class="qr-h">Activity</h2>
    <div class="qr-list">
      @forelse($history->take(5) as $h)
        <div class="qr-row">
          <span class="qr-ic {{ $h->amount >= 0 ? 'in' : 'out' }}">{{ $h->amount >= 0 ? '+' : '−' }}</span>
          <span class="qr-rb">
            <span class="qr-rt">{{ $h->description ?? ucfirst(str_replace('_', ' ', $h->type)) }}</span>
            <span class="qr-rs">{{ $h->created_at->diffForHumans() }}</span>
          </span>
          <span class="qr-amt {{ $h->amount >= 0 ? 'in' : 'out' }}">{{ $h->amount >= 0 ? '+' : '' }}{{ number_format($h->amount) }}</span>
        </div>
      @empty
        <p class="qr-empty">No Q Coin activity yet. Claim your first bonus on the left.</p>
      @endforelse
    </div>
  </div>
</div>

<script>
/* coin claim moment: reads the hold button's state, never touches the claim request */
(function () {
  var btn = document.querySelector('[data-qc-hold]');
  var coin = document.querySelector('.qr .qc');
  if (!btn || !coin) return;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var txt = btn.querySelector('.qh-txt');
  var balEl = document.querySelector('.qr-balance');
  var balNode = balEl && balEl.firstChild && balEl.firstChild.nodeType === 3 ? balEl.firstChild : null;
  var base = balNode ? (parseInt(balNode.nodeValue.replace(/[^\d]/g, ''), 10) || 0) : 0;
  var done = false;

  function ring(i) {
    var el = document.createElement('span');
    el.className = 'qx-ring';
    coin.appendChild(el);
    var a = el.animate([
      { transform: 'translate(-50%,-50%) scale(.3)', opacity: .95 },
      { transform: 'translate(-50%,-50%) scale(2.8)', opacity: 0 }
    ], { duration: 1100, delay: i * 200, easing: 'cubic-bezier(.2,.7,.3,1)', fill: 'both' });
    a.onfinish = function () { el.remove(); };
  }

  function discs(n) {
    for (var i = 0; i < n; i++) {
      var el = document.createElement('span');
      el.className = 'qx-disc';
      el.textContent = 'Q';
      coin.appendChild(el);
      var ang = -Math.PI / 2 + (Math.random() - .5) * Math.PI * 1.9;
      var dist = 90 + Math.random() * 110;
      var dx = Math.cos(ang) * dist, dy = Math.sin(ang) * dist - 20;
      var a = el.animate([
        { transform: 'translate(0,0) scale(.3) rotate(0deg)', opacity: 1 },
        { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(1.1) rotate(220deg)', opacity: 1, offset: .55 },
        { transform: 'translate(' + dx + 'px,' + (dy + 90) + 'px) scale(.8) rotate(400deg)', opacity: 0 }
      ], { duration: 1100 + Math.random() * 400, delay: Math.random() * 120, easing: 'cubic-bezier(.2,.7,.3,1)', fill: 'both' });
      (function (node, anim) { anim.onfinish = function () { node.remove(); }; })(el, a);
    }
  }

  function celebrate() {
    coin.classList.remove('qx-charging');
    setTimeout(function () { coin.style.setProperty('--ch', 0); }, 450);
    if (reduce) return;
    coin.animate([{ transform: 'scale(1.1)' }, { transform: 'scale(.9)' }, { transform: 'scale(1.06)' }, { transform: 'scale(1)' }], { duration: 700, easing: 'ease-out' });
    ring(0); ring(1);
    discs(16);
    coin.click();
    if (balEl) setTimeout(function () {
      balEl.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.12)' }, { transform: 'scale(1)' }], { duration: 600, easing: 'ease-out' });
    }, 950);
  }

  new MutationObserver(function () {
    var p = parseFloat(btn.style.getPropertyValue('--p')) || 0;
    coin.style.setProperty('--ch', (p / 100).toFixed(3));
    coin.classList.toggle('qx-charging', btn.classList.contains('qh-holding') && !reduce);
    if (btn.classList.contains('qh-done') && !done) { done = true; celebrate(); }
  }).observe(btn, { attributes: true, attributeFilter: ['style', 'class'] });

  if (txt && balNode) new MutationObserver(function () {
    if (!done) return;
    var m = /\+([\d,]+)/.exec(txt.textContent);
    if (m) balNode.nodeValue = (base + (parseInt(m[1].replace(/,/g, ''), 10) || 0)).toLocaleString('en-US') + ' ';
  }).observe(txt, { childList: true, characterData: true, subtree: true });
})();
</script>

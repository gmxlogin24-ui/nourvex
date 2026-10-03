@php
    $dqmDeckName = auth()->user()?->name ?? 'Guest';
    $dqmDeckCirc = 2 * pi() * 40;
    $dqmDeckMilestone = $nextStreakMilestone ?? 7;
    $dqmDeckPct = $dqmDeckMilestone > 0 ? min(100, round((($streakDays ?? 0) / $dqmDeckMilestone) * 100)) : 0;
    $dqmDeckOffset = $dqmDeckCirc * (1 - $dqmDeckPct / 100);
@endphp
<div class="dqm-balance-wrap" id="dqmBalanceWrap">
    <div class="dqm-balance-side-actions">
        @if(auth()->user()->planAllows('gigs_post'))<a href="{{ Route::has('gigs.create') ? route('gigs.create') : '#' }}" class="dqm-side-btn">Post a Hustle</a>@endif
        @if($qCoinClaimedToday ?? false)
            <span class="dqm-side-btn dqm-side-btn-static">Claimed</span>
        @else
            <form method="POST" action="{{ Route::has('qcoin.claim') ? route('qcoin.claim') : '#' }}">
                @csrf
                <button type="submit" class="dqm-side-btn dqm-side-btn-ghost">Claim</button>
            </form>
        @endif
    </div>
    <div class="dqm-deck-collapsed" id="dqmDeckCollapsed">
        <div class="dqm-deck-peek dqm-deck-peek-top" id="dqmPeekTop"></div>
        <div class="dqm-deck-peek dqm-deck-peek-bottom" id="dqmPeekBottom"></div>
        <div class="dqm-deck-card dqm-deck-next" id="dqmDeckNext" data-card="qcoin">
            <div class="dqm-bal-card-inner">
                <span class="dqm-hero-label">Q Coin Balance</span>
                <span class="dqm-hero-value dqm-countup" data-value="{{ $qCoinBalance ?? 0 }}" data-suffix=" Q">0 Q</span>
                <div class="dqm-hero-ring">
                    <svg viewBox="0 0 96 96">
                        <circle class="dqm-ring-track" cx="48" cy="48" r="40"></circle>
                        <circle class="dqm-ring-fill" cx="48" cy="48" r="40" stroke-dasharray="{{ $dqmDeckCirc }}" stroke-dashoffset="{{ $dqmDeckOffset }}"></circle>
                    </svg>
                    <div class="dqm-ring-center"><span>{{ $streakDays ?? 0 }}</span><small>day streak</small></div>
                </div>
            </div>
        </div>
        <div class="dqm-deck-card dqm-deck-front" id="dqmDeckFront" data-card="qcoin">
            <div class="dqm-bal-card-inner">
                <span class="dqm-hero-label">Q Coin Balance</span>
                <span class="dqm-hero-value dqm-countup" data-value="{{ $qCoinBalance ?? 0 }}" data-suffix=" Q">0 Q</span>
                <div class="dqm-hero-ring">
                    <svg viewBox="0 0 96 96">
                        <circle class="dqm-ring-track" cx="48" cy="48" r="40"></circle>
                        <circle class="dqm-ring-fill" cx="48" cy="48" r="40" stroke-dasharray="{{ $dqmDeckCirc }}" stroke-dashoffset="{{ $dqmDeckOffset }}"></circle>
                    </svg>
                    <div class="dqm-ring-center"><span>{{ $streakDays ?? 0 }}</span><small>day streak</small></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="dqm-deck-overlay" id="dqmDeckOverlay">
    <button type="button" class="dqm-deck-overlay-back" id="dqmDeckOverlayBack"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back</button>
    <div class="dqm-deck-center-wrap">
    <div class="dqm-deck-expanded" id="dqmDeckExpanded">
        <div class="dqm-deck-fan dqm-deck-fan-far dqm-deck-fan-purple dqm-fan-topcard" id="dqmFanTopFar"><div class="dqm-fan-chip"></div><div class="dqm-fan-dot"></div></div>
        <div class="dqm-deck-fan dqm-deck-fan-near dqm-deck-fan-teal dqm-fan-topcard" id="dqmFanTopNear" data-card="gigs">
            <div class="dqm-fan-chip"></div><div class="dqm-fan-dot"></div>
            <div class="dqm-fan-label" data-role="label">Active Gigs</div>
            <div class="dqm-fan-value" data-role="value">{{ $myOpenGigs ?? 0 }}</div>
        </div>
        <div class="dqm-deck-fan-center" id="dqmFanCenter" data-card="qcoin">
            <div class="dqm-bal-card-inner" id="dqmFanCenterInner">
                <span class="dqm-hero-label" data-role="label">Q Coin Balance</span>
                <span class="dqm-hero-value" data-role="value">{{ number_format($qCoinBalance ?? 0) }} Q</span>
            </div>
            <div class="dqm-fan-masked">•••• •••• •••• 3123</div>
        </div>
        <div class="dqm-deck-fan dqm-deck-fan-near dqm-deck-fan-coral dqm-fan-bottomcard" id="dqmFanBottomNear" data-card="earnings">
            <div class="dqm-fan-label" data-role="label">Earnings</div>
            <div class="dqm-fan-value" data-role="value">₦{{ number_format($earnings ?? 0) }}</div>
            <span class="dqm-fan-masked-sm">•••• 3123</span>
        </div>
        <div class="dqm-deck-fan dqm-deck-fan-far dqm-deck-fan-amber dqm-fan-bottomcard" id="dqmFanBottomFar"><span class="dqm-fan-masked-sm">•••• 3123</span></div>
    </div>
    </div>
    <div class="dqm-deck-detail" id="dqmDeckDetail">
        <div><p class="dqm-deck-detail-label">Streak</p><p class="dqm-deck-detail-value" id="dqmDeckDetailA">{{ $streakDays ?? 0 }} days</p></div>
        <div><p class="dqm-deck-detail-label">Holder</p><p class="dqm-deck-detail-value">{{ explode(' ', $dqmDeckName)[0] }}</p></div>
    </div>
</div>
<script>
(function () {
    var overlay = document.getElementById('dqmDeckOverlay');
    var overlayBack = document.getElementById('dqmDeckOverlayBack');
    var front = document.getElementById('dqmDeckFront');
    var next = document.getElementById('dqmDeckNext');
    var peekTop = document.getElementById('dqmPeekTop');
    var peekBottom = document.getElementById('dqmPeekBottom');
    var fanTopFar = document.getElementById('dqmFanTopFar');
    var fanTopNear = document.getElementById('dqmFanTopNear');
    var fanCenter = document.getElementById('dqmFanCenter');
    var fanBottomNear = document.getElementById('dqmFanBottomNear');
    var fanBottomFar = document.getElementById('dqmFanBottomFar');
    var detailA = document.getElementById('dqmDeckDetailA');
    if (!overlay || !front || !next || !fanCenter) return;
    var MAX_DRAG = 50, SWAP_DISTANCE = 80, COMMIT_RATIO = 0.5, DOUBLE_TAP_MS = 320;
    var CARD_HTML = { qcoin: front.innerHTML };
    function htmlFor(key) { return CARD_HTML[key]; }
    function otherKey(key) { return key; }
    function setFrontCard(key) { front.innerHTML = htmlFor(key); front.dataset.card = key; if (window.lucide) lucide.createIcons(); }
    function resetLayers(instant) {
        if (instant) { front.style.transition = 'none'; next.style.transition = 'none'; }
        front.style.opacity = 1; front.style.transform = '';
        next.style.opacity = 0; next.style.transform = 'translateY(0)';
        if (instant) requestAnimationFrame(function () { front.style.transition = ''; next.style.transition = ''; });
    }
    function flipFrom(el, startRect) {
        var endRect = el.getBoundingClientRect();
        var dx = startRect.left - endRect.left, dy = startRect.top - endRect.top;
        var sx = startRect.width / endRect.width, sy = startRect.height / endRect.height;
        el.style.transition = 'none';
        el.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(' + sx + ',' + sy + ')';
        requestAnimationFrame(function () { el.style.transition = 'transform 0.38s ease'; el.style.transform = ''; });
    }
    function slideUp(el, delay) {
        el.style.transition = 'none'; el.style.transform = 'translateY(140px)'; el.style.opacity = '0';
        requestAnimationFrame(function () {
            el.style.transition = 'transform 0.4s ease ' + delay + 's, opacity 0.4s ease ' + delay + 's';
            el.style.transform = ''; el.style.opacity = '1';
        });
    }
    function openExpanded() {
        var rTop = peekTop.getBoundingClientRect(), rFront = front.getBoundingClientRect(), rBottom = peekBottom.getBoundingClientRect();
        if (detailA) detailA.textContent = front.dataset.card === 'qcoin' ? '{{ $streakDays ?? 0 }} days' : '₦{{ number_format($earnings ?? 0) }}';
        overlay.classList.add('open');
        requestAnimationFrame(function () {
            flipFrom(fanTopNear, rTop); flipFrom(fanCenter, rFront); flipFrom(fanBottomNear, rBottom);
            slideUp(fanTopFar, 0.08); slideUp(fanBottomFar, 0.16);
        });
    }
    function closeExpanded() { overlay.classList.remove('open'); }
    if (overlayBack) overlayBack.addEventListener('click', closeExpanded);
    function flipSwap(a, b) {
        var ra = a.getBoundingClientRect(), rb = b.getBoundingClientRect();
        var la = a.querySelector('[data-role="label"]'), va = a.querySelector('[data-role="value"]');
        var lb = b.querySelector('[data-role="label"]'), vb = b.querySelector('[data-role="value"]');
        if (!la || !va || !lb || !vb) return;
        var tl = la.textContent, tv = va.textContent, tc = a.dataset.card;
        la.textContent = lb.textContent; va.textContent = vb.textContent; a.dataset.card = b.dataset.card;
        lb.textContent = tl; vb.textContent = tv; b.dataset.card = tc;
        flipFrom(a, rb); flipFrom(b, ra);
    }
    if (fanTopNear) fanTopNear.addEventListener('click', function () { flipSwap(fanCenter, fanTopNear); });
    if (fanBottomNear) fanBottomNear.addEventListener('click', function () { flipSwap(fanCenter, fanBottomNear); });
    var startY = 0, dragging = false, moved = false, lastTap = 0;
    front.addEventListener('pointerdown', function (e) {
        dragging = true; moved = false; startY = e.clientY;
        front.classList.add('dragging'); front.style.transition = 'none'; next.style.transition = 'none';
        next.innerHTML = htmlFor(otherKey(front.dataset.card)); next.dataset.card = otherKey(front.dataset.card);
        front.setPointerCapture(e.pointerId);
    });
    front.addEventListener('pointermove', function (e) {
        if (!dragging) return;
        var dy = e.clientY - startY;
        if (Math.abs(dy) > 6) moved = true;
        var cdy = Math.max(Math.min(dy, MAX_DRAG), -MAX_DRAG);
        var progress = Math.max(Math.min(dy / SWAP_DISTANCE, 1), -1);
        front.style.transform = 'translateX(20%) translateY(' + cdy + 'px)'; front.style.opacity = 1 - Math.abs(progress);
        next.style.transform = 'translateX(20%) translateY(' + (cdy - (progress >= 0 ? SWAP_DISTANCE : -SWAP_DISTANCE) * 0.4) + 'px)'; next.style.opacity = Math.abs(progress);
    });
    front.addEventListener('pointerup', function (e) {
        if (!dragging) return;
        dragging = false; front.classList.remove('dragging');
        var dy = e.clientY - startY;
        var progress = Math.max(Math.min(dy / SWAP_DISTANCE, 1), -1);
        front.style.transition = 'transform 0.18s ease, opacity 0.18s ease'; next.style.transition = 'transform 0.18s ease, opacity 0.18s ease';
        if (Math.abs(progress) >= COMMIT_RATIO) { setFrontCard(next.dataset.card); resetLayers(true); }
        else if (!moved) {
            resetLayers(false);
            var now = Date.now();
            if (now - lastTap < DOUBLE_TAP_MS) { lastTap = 0; openExpanded(); } else { lastTap = now; }
        } else { resetLayers(false); }
    });
    front.addEventListener('pointercancel', function () {
        dragging = false; front.classList.remove('dragging');
        front.style.transition = 'transform 0.18s ease, opacity 0.18s ease'; next.style.transition = 'transform 0.18s ease, opacity 0.18s ease';
        resetLayers(false);
    });
})();
</script>

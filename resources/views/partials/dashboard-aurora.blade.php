@php
    $auAvatar = auth()->user()?->avatar_url;
    $auName = auth()->user()?->name ?? 'Guest';
    $auInitial = strtoupper(substr($auName, 0, 1));

    $auDeckCards = [
        ['label' => 'Q Coin Balance', 'value' => number_format($qCoinBalance) . ' Q', 'sub' => ($streakDays ?? 0) . ' day streak', 'c1' => '#8b5cf6', 'c2' => '#3730a3', 'claim' => true],
        ['label' => 'Total Earnings', 'value' => number_format($earnings * config('qcoin.q_per_naira_display', 10)) . ' Q', 'sub' => 'from completed gigs', 'c1' => '#22c55e', 'c2' => '#14532d', 'claim' => false],
        ['label' => 'Active Gigs', 'value' => (string) $myOpenGigs, 'sub' => 'currently open', 'c1' => '#3b82f6', 'c2' => '#1e3a8a', 'claim' => false],
        ['label' => 'Rating', 'value' => ($avgRating ? number_format($avgRating,1) : '—') . ' ★', 'sub' => $reviewCount . ' reviews', 'c1' => '#fb923c', 'c2' => '#7c2d12', 'claim' => false],
    ];
@endphp

<div class="au-page">

    <div class="au-topbar">
        <a href="{{ route('profile.index') }}" class="au-avatar-btn">
            @if($auAvatar)<img src="{{ $auAvatar }}" alt="">@else<span>{{ $auInitial }}</span>@endif
        </a>
        <a href="{{ Route::has('chat.index') ? route('chat.index') : '#' }}" class="au-icon-btn" title="Chat">
            <i class="bi bi-chat-dots-fill"></i>
            @if(($navUnreadChats ?? 0) > 0)<span class="au-badge">{{ $navUnreadChats }}</span>@endif
        </a>
        <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="au-icon-btn" title="Notifications">
            <i class="bi bi-bell-fill"></i>
            @if(($unreadNotifications ?? 0) > 0)<span class="au-badge">{{ $unreadNotifications }}</span>@endif
        </a>
        <span class="au-topbar-spacer"></span>
        <span class="au-topbar-pill-chip"><i class="bi bi-gem"></i> {{ number_format($qCoinBalance) }} Q</span>
    </div>

    <div class="au-greeting">
        <span class="au-greeting-welcome">Welcome back</span>
        <span class="au-greeting-name">Hi, {{ explode(' ', $auName)[0] }}</span>
    </div>

    <div class="au-card-deck" id="auCardDeck">
        <div class="au-deck-back" id="auDeckBack"></div>
        <div class="au-deck-front" id="auDeckFront" style="background: linear-gradient(150deg, {{ $auDeckCards[0]['c1'] }} 0%, {{ $auDeckCards[0]['c2'] }} 100%);">
            <div class="au-balance-top">
                <span class="au-balance-label" id="auFrontLabel">{{ $auDeckCards[0]['label'] }}</span>
                <a href="{{ route('profile.index') }}" class="au-balance-edit"><i class="bi bi-pencil"></i></a>
            </div>
            <span class="au-balance-value" id="auFrontValue">{{ $auDeckCards[0]['value'] }}</span>
            <div class="au-balance-bottom">
                <span class="au-balance-streak" id="auFrontSub">{{ $auDeckCards[0]['sub'] }}</span>
                <span id="auFrontAction">
                    @if($qCoinClaimedToday)
                        <span class="au-claim-btn au-claim-done">Claimed</span>
                    @else
                        <form method="POST" action="{{ route('qcoin.claim') }}">
                            @csrf
                            <button type="submit" class="au-claim-btn">Claim</button>
                        </form>
                    @endif
                </span>
            </div>
        </div>
        <div class="au-deck-dots" id="auDeckDots">
            @foreach($auDeckCards as $i => $card)
                <span class="au-dot @if($i === 0) active @endif"></span>
            @endforeach
        </div>
    </div>
    <script id="auCardsData" type="application/json">{!! json_encode($auDeckCards) !!}</script>

    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose"><span>Explore</span></div>
        <div class="au-tile-grid">
            <a href="{{ route('gigs.index') }}" class="au-tile au-tile-purple">
                <i class="bi bi-briefcase-fill au-tile-icon"></i>
                <span class="au-tile-title">Gigs</span>
                <span class="au-tile-sub">Find and complete tasks, get paid.</span>
                <span class="au-tile-arrow"><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="{{ route('marketplace.index') }}" class="au-tile au-tile-teal">
                <i class="bi bi-shop au-tile-icon"></i>
                <span class="au-tile-title">Marketplace</span>
                <span class="au-tile-sub">Buy, sell, and discover items.</span>
                <span class="au-tile-arrow"><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="{{ route('services.index') }}" class="au-tile au-tile-blue">
                <i class="bi bi-broadcast au-tile-icon"></i>
                <span class="au-tile-title">VTU Services</span>
                <span class="au-tile-sub">Top up airtime, data, TV & more.</span>
                <span class="au-tile-arrow"><i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="{{ Route::has('concierge.show') ? route('concierge.show') : '#' }}" class="au-tile au-tile-amber">
                <i class="bi bi-stars au-tile-icon"></i>
                <span class="au-tile-title">LEVI AI</span>
                <span class="au-tile-sub">Your assistant, always with you.</span>
                <span class="au-tile-arrow"><i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>

    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose"><span>Quick Actions</span></div>
        <div class="au-quick-row">
@if(auth()->user()->planAllows('gigs_post'))
            <a href="{{ route('gigs.create') }}" class="au-quick-item">
                <span class="au-quick-icon au-vibrant-green"><i class="bi bi-briefcase-fill"></i></span>
                <span>Post Gig</span>
            </a>
@endif
@if(auth()->user()->planAllows('market_sell'))
            <a href="{{ route('marketplace.create') }}" class="au-quick-item">
                <span class="au-quick-icon au-vibrant-orange"><i class="bi bi-tag-fill"></i></span>
                <span>List Item</span>
            </a>
@endif
            <a href="{{ route('services.index') }}" class="au-quick-item">
                <span class="au-quick-icon au-vibrant-blue"><i class="bi bi-phone-fill"></i></span>
                <span>Airtime</span>
            </a>
            <a href="{{ route('gigs.index') }}" class="au-quick-item">
                <span class="au-quick-icon au-vibrant-purple"><i class="bi bi-compass-fill"></i></span>
                <span>Browse</span>
            </a>
        </div>
    </div>

    <div class="au-page-section">
        @include('partials.levi-inline')
    </div>

    <div class="au-page-section">
        <div class="au-section-header au-section-header-loose">
            <span>Recent Gigs</span>
            <a href="{{ route('gigs.index') }}">View all</a>
        </div>
        <div class="au-dark-card">
            @forelse($topGigs as $gig)
                <a href="{{ route('gigs.show', $gig->id) }}" class="au-list-row">
                    <span class="au-list-icon au-vibrant-green"><i class="bi bi-briefcase-fill"></i></span>
                    <span class="au-list-text">
                        <span class="au-list-title">{{ $gig->title }}</span>
                        <span class="au-list-sub">{{ ucfirst($gig->status ?? '') }}</span>
                    </span>
                    <span class="au-list-amount">{{ number_format(($gig->amount) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($gig->amount) }}</small></span>
                </a>
            @empty
                <p class="au-empty-note">No gigs yet.</p>
            @endforelse
        </div>
    </div>

    <div class="au-two-col">
        <div class="au-col-main">
            <div class="au-page-section">
                <div class="au-dark-card">
                    <div class="au-flow-header">
                        <span>Money Flow</span>
                        <span>{{ number_format(($netFlow ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($netFlow ?? 0) }}</small> net</span>
                    </div>
                    <div class="au-flow-track">
                        <div class="au-flow-fill" style="width: {{ ($totalIn ?? 0) > 0 ? min(100, round((($netFlow ?? 0) / $totalIn) * 100)) : 0 }}%;"></div>
                    </div>
                    <div class="au-flow-meta">
                        <span>In: {{ number_format(($totalIn ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalIn ?? 0) }}</small></span>
                        <span>Out: {{ number_format(($totalOut ?? 0) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($totalOut ?? 0) }}</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="au-col-side">
            <div class="au-profile-card">
                @if($auAvatar)
                    <img src="{{ $auAvatar }}" alt="" class="au-profile-photo">
                @else
                    <div class="au-profile-photo au-profile-photo-fallback">{{ $auInitial }}</div>
                @endif
                <div class="au-profile-name">{{ $auName }}</div>
                <div class="au-profile-desc">{{ auth()->user()->workerLevel()['label'] }}</div>
                <div class="au-profile-stats">
                    <span>{{ $myCompletedGigs }} Gigs</span>
                    <span>{{ $avgRating ? number_format($avgRating, 1) : '—' }} ({{ $reviewCount }})</span>
                </div>
                <a href="{{ route('profile.index') }}" class="au-profile-btn">Edit Profile</a>
                <a href="{{ route('kyc.show') }}" class="au-profile-btn">Verify account</a>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    var dataEl = document.getElementById('auCardsData');
    if (!dataEl) return;
    var cards = JSON.parse(dataEl.textContent);
    var idx = 0;
    var front = document.getElementById('auDeckFront');
    var back = document.getElementById('auDeckBack');
    var label = document.getElementById('auFrontLabel');
    var value = document.getElementById('auFrontValue');
    var sub = document.getElementById('auFrontSub');
    var action = document.getElementById('auFrontAction');
    var dots = document.querySelectorAll('#auDeckDots .au-dot');
    if (!front || !back || cards.length < 2) return;

    function render(i) {
        var c = cards[i];
        label.textContent = c.label;
        value.textContent = c.value;
        sub.textContent = c.sub;
        action.style.display = c.claim ? '' : 'none';
        front.style.background = 'linear-gradient(150deg, ' + c.c1 + ' 0%, ' + c.c2 + ' 100%)';
        dots.forEach(function (d, di) { d.classList.toggle('active', di === i); });
    }

    function next() {
        var nextIdx = (idx + 1) % cards.length;
        front.style.transition = 'transform .3s ease, opacity .3s ease';
        back.style.transition = 'transform .3s ease, opacity .3s ease';
        front.style.transform = 'translateX(-26px)';
        front.style.opacity = '0';
        back.style.transform = 'rotate(-4deg) scale(1.04) translateX(20px)';
        back.style.opacity = '0.45';

        setTimeout(function () {
            idx = nextIdx;
            render(idx);
            front.style.transition = 'none';
            back.style.transition = 'none';
            front.style.transform = 'translateX(26px)';
            front.style.opacity = '0';
            back.style.transform = 'rotate(-4deg) scale(1.04) translateX(-20px)';
            back.style.opacity = '0.45';
            void front.offsetWidth;
            front.style.transition = 'transform .3s ease, opacity .3s ease';
            back.style.transition = 'transform .3s ease, opacity .3s ease';
            front.style.transform = 'translateX(0)';
            front.style.opacity = '1';
            back.style.transform = 'rotate(-4deg) scale(1.04) translateX(0)';
            back.style.opacity = '1';
        }, 300);
    }

    var timer = setInterval(next, 4500);
    front.addEventListener('click', function (e) {
        if (e.target.closest('form') || e.target.closest('a')) return;
        clearInterval(timer);
        next();
        timer = setInterval(next, 4500);
    });
})();
</script>

@php
    $dqmAvatar = auth()->user()?->avatar_url;
    $dqmName = auth()->user()?->name ?? 'Guest';
    $dqmInitial = strtoupper(substr($dqmName, 0, 1));
    $dqmHour = now()->hour;
    $dqmGreeting = $dqmHour < 12 ? 'Good morning' : ($dqmHour < 17 ? 'Good afternoon' : 'Good evening');
    $dqmMilestone = $nextStreakMilestone ?? 7;
    $dqmCirc = 2 * pi() * 40;
    $dqmPct = $dqmMilestone > 0 ? min(100, round((($streakDays ?? 0) / $dqmMilestone) * 100)) : 0;
    $dqmOffset = $dqmCirc * (1 - $dqmPct / 100);
    $dqmStackItems = ($topGigs ?? collect())->take(6)->map(function ($g) {
        return ['title' => $g->title, 'image_url' => $g->image_url ?? null];
    });
@endphp


<div class="dqm-page">


    @if(!($isVerified ?? true))
    <div class="dqm-verify-banner">
        <span>Unverified wallet limit — up to ₦{{ number_format($walletCapRemaining ?? 0) }} more before verifying your BVN.</span>
        <a href="{{ route('wallet.virtual-account') }}">Verify</a>
    </div>
    @else
    <div class="dqm-verify-banner" style="border-color:var(--color-accent, #7c5cf0);" id="dqmVaBanner">
        <span>Your virtual account: <b>{{ auth()->user()->virtual_account_number }}</b> ({{ auth()->user()->virtual_account_bank }})</span>
        <button type="button" id="dqmVaCopyBtn" data-account="{{ auth()->user()->virtual_account_number }}" style="background:none;border:none;color:var(--color-accent, #7c5cf0);font-weight:600;cursor:pointer;">Copy</button>
    </div>
    <script>
    (function(){
        var btn = document.getElementById('dqmVaCopyBtn');
        if (!btn) return;
        btn.addEventListener('click', function(){
            navigator.clipboard.writeText(btn.dataset.account).then(function(){
                var original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function(){ btn.textContent = original; }, 1500);
            });
        });
    })();
    </script>
    @endif

    <div class="dqm-grid">
        <div class="dqm-main">

            <script>
            (function () {
                var els = document.querySelectorAll('.dqm-countup');
                els.forEach(function (el) {
                    var target = parseFloat(el.dataset.value) || 0;
                    var prefix = el.dataset.prefix || '';
                    var suffix = el.dataset.suffix || '';
                    var duration = 900;
                    var start = null;
                    function easeOutQuad(t) { return t * (2 - t); }
                    function step(ts) {
                        if (!start) start = ts;
                        var progress = Math.min((ts - start) / duration, 1);
                        var current = Math.round(target * easeOutQuad(progress));
                        el.textContent = prefix + current.toLocaleString() + suffix;
                        if (progress < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                });
            })();
            </script>
            <div class="dqm-hero-row">
            <div class="dqm-balance-card dqm-balance-compact dqm-gsap-in" data-gsap-order="1">
                <div class="dqm-balance-top">
                    <span class="dqm-balance-label">Total Balance</span>
                    <div class="dqm-balance-icon"><i data-lucide="wallet" class="w-4 h-4"></i></div>
                </div>
                <div class="dqm-balance-amount">₦<span class="dqm-countup" data-value="{{ ($qCoinBalance ?? 0) + ($earnings ?? 0) }}">0</span></div>
                <div class="dqm-balance-actions">
                    <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqm-bal-btn dqm-bal-btn-primary"><i data-lucide="arrow-up-right" class="w-4 h-4"></i> Send</a>
                    <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqm-bal-btn dqm-bal-btn-outline">Withdraw</a>
                    <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="dqm-bal-btn dqm-bal-btn-icon" title="More"><i data-lucide="more-horizontal" class="w-4 h-4"></i></a>
                </div>
                <div class="dqm-seg-strip" id="dqmSegStrip">
                    <div class="dqm-seg-item dqm-seg-active" data-seg="earnings">
                        <div class="dqm-seg-icon dqm-seg-icon-green"><i class="ph-fill ph-chart-line-up"></i></div>
                        <span class="dqm-seg-v">₦<span class="dqm-countup" data-value="{{ $earnings ?? 0 }}">0</span></span>
                        <span class="dqm-seg-l">Earnings</span>
                    </div>
                    <div class="dqm-seg-div"></div>
                    <div class="dqm-seg-item" data-seg="gigs">
                        <div class="dqm-seg-icon dqm-seg-icon-purple"><i class="ph-fill ph-briefcase"></i></div>
                        <span class="dqm-seg-v dqm-countup" data-value="{{ $myOpenGigs ?? 0 }}">0</span>
                        <span class="dqm-seg-l">Gigs</span>
                    </div>
                    <div class="dqm-seg-div"></div>
                    <div class="dqm-seg-item" data-seg="qcoin">
                        <div class="dqm-seg-icon dqm-seg-icon-gold"><i class="ph-fill ph-coins"></i></div>
                        <span class="dqm-seg-v dqm-countup" data-value="{{ $qCoinBalance ?? 0 }}" data-suffix=" Q">0 Q</span>
                        <span class="dqm-seg-l">Q Coin</span>
                    </div>
                </div>
                <div class="dqm-seg-detail" id="dqmSegDetail">
                    <span>Total In <b class="dqm-seg-up">₦{{ number_format($totalIn ?? 0) }}</b></span>
                    <span>Total Out <b>₦{{ number_format($totalOut ?? 0) }}</b></span>
                </div>
                <script>
                (function () {
                    var strip = document.getElementById('dqmSegStrip');
                    var detail = document.getElementById('dqmSegDetail');
                    if (!strip || !detail) return;
                    var data = {
                        earnings: '<span>Total In <b class="dqm-seg-up">₦{{ number_format($totalIn ?? 0) }}</b></span><span>Total Out <b>₦{{ number_format($totalOut ?? 0) }}</b></span>',
                        gigs: '<span>Completed <b>{{ $myCompletedGigs ?? 0 }}</b></span><span>Rating <b>{{ $avgRating ? number_format($avgRating,1) : "—" }}</b></span>',
                        qcoin: '<span>Streak <b>{{ $streakDays ?? 0 }} days</b></span>'
                    };
                    strip.querySelectorAll('.dqm-seg-item').forEach(function (item) {
                        item.addEventListener('click', function () {
                            strip.querySelectorAll('.dqm-seg-item').forEach(function (i) { i.classList.remove('dqm-seg-active'); });
                            item.classList.add('dqm-seg-active');
                            detail.innerHTML = data[item.dataset.seg] || '';
                            if (window.gsap) {
                                gsap.fromTo(detail, { opacity: 0, y: -4 }, { opacity: 1, y: 0, duration: 0.25, ease: 'power1.out' });
                            }
                        });
                    });
                })();
                </script>
            </div>
                <div class="dqm-hero-vactions">
                    <a href="{{ route('services.index') }}" class="dqm-vaction"><div class="dqm-vaction-icon dqm-vaction-blue"><i data-lucide="smartphone" class="w-4 h-4"></i></div><span>Airtime</span></a>
                    <a href="{{ route('services.index') }}" class="dqm-vaction"><div class="dqm-vaction-icon dqm-vaction-green"><i data-lucide="wifi" class="w-4 h-4"></i></div><span>Data</span></a>
                    <a href="{{ route('services.index') }}" class="dqm-vaction"><div class="dqm-vaction-icon dqm-vaction-purple"><i data-lucide="tv" class="w-4 h-4"></i></div><span>Cable TV</span></a>
                    <a href="{{ route('services.index') }}" class="dqm-vaction"><div class="dqm-vaction-icon dqm-vaction-ai"><i data-lucide="plug-zap" class="w-4 h-4"></i></div><span>Electricity</span></a>
                </div>
            </div>

            <div class="dqm-page-card dqm-gsap-in" data-gsap-order="3">
            <div class="dqm-activity-head"><span>Your Activity</span><a href="{{ route('gigs.index') }}">View all</a></div>
            <div class="dqm-arow-list">
                <div class="dqm-arow dqm-arow-green">
                    <div class="dqm-arow-icon"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                    <div class="dqm-arow-body">
                        <span class="dqm-arow-l">Active Gigs</span>
                        <span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myOpenGigs ?? 0 }}">0</span> <small>this week</small></span>
                    </div>
                </div>
                <div class="dqm-arow dqm-arow-purple">
                    <div class="dqm-arow-icon"><i data-lucide="store" class="w-5 h-5"></i></div>
                    <div class="dqm-arow-body">
                        <span class="dqm-arow-l">Marketplace</span>
                        <span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myListings ?? 0 }}">0</span> <small>listings</small></span>
                    </div>
                </div>
            </div>


            <div class="dqm-card dqm-marketplace">
                <div class="dqm-card-head"><span>Marketplace</span><a href="{{ route('marketplace.index') }}">Open</a></div>
                <div class="dqm-mp-row">
                    <div class="dqm-mp-item"><span class="v">{{ $myListings }}</span><span class="l">Active Listings</span></div>
                    <div class="dqm-mp-item dqm-mp-soon"><span class="v">Soon</span><span class="l">Sales</span></div>
                    <div class="dqm-mp-item dqm-mp-soon"><span class="v">Soon</span><span class="l">Orders</span></div>
                </div>
                <div class="dqm-mp-detail">
                    <p>You have <strong>{{ $myListings }}</strong> active listing{{ $myListings === 1 ? '' : 's' }} on the marketplace. Sales and order tracking are launching soon.</p>
                </div>
            </div>

            <div class="dqm-card dqm-gigs">
                <div class="dqm-card-head"><span>Recent Gigs</span><a href="{{ route('gigs.index') }}">View all</a></div>
                @forelse($topGigs as $gig)
                    <a href="{{ route('gigs.show', $gig->id) }}" class="dqm-gig-row">
                        <span class="dqm-gig-title">{{ $gig->title }}</span>
                        <span class="dqm-gig-status">{{ ucfirst($gig->status ?? '') }}</span>
                        <span class="dqm-gig-amount">₦{{ number_format($gig->amount) }}</span>
                    </a>
                @empty
                    <p class="dqm-empty">No gigs yet.</p>
                @endforelse
            </div>

            <div class="dqm-card dqm-flow">
                <div class="dqm-card-head"><span>Money Flow</span><span class="dqm-flow-net">₦{{ number_format($netFlow ?? 0) }} net</span></div>
                <div class="dqm-flow-track"><div class="dqm-flow-fill" style="width: {{ ($totalIn ?? 0) > 0 ? min(100, round((($netFlow ?? 0) / $totalIn) * 100)) : 0 }}%;"></div></div>
                <div class="dqm-flow-meta"><span>In: ₦{{ number_format($totalIn ?? 0) }}</span><span>Out: ₦{{ number_format($totalOut ?? 0) }}</span></div>
            </div>
            </div>

        </div>

        <div class="dqm-side dqm-desktop-only">
            <div class="dqm-profile-card">
                <div class="dqm-profile-photo">
                    @if($dqmAvatar)<img src="{{ $dqmAvatar }}" alt="">@else<span>{{ $dqmInitial }}</span>@endif
                </div>
                <div class="dqm-profile-scrim"></div>
                <div class="dqm-profile-info">
                    <p class="dqm-profile-name">{{ $dqmName }}</p>
                    <p class="dqm-profile-level">{{ auth()->user()->workerLevel()['label'] }}</p>
                    <div class="dqm-profile-stats">
                        <span>{{ $myCompletedGigs }} Gigs</span>
                        <span>{{ $avgRating ? number_format($avgRating, 1) : '—' }} ({{ $reviewCount }})</span>
                    </div>
                    <a href="{{ route('profile.index') }}" class="dqm-profile-edit">Edit Profile</a>
                </div>
            </div>

            <div class="dqm-card dqm-activity">
                <div class="dqm-card-head"><span>Activity</span></div>
                @forelse($todayFeed->take(5) as $item)
                    <a href="{{ $item['url'] }}" class="dqm-activity-row">
                        <span class="dqm-dot dqm-dot-{{ $item['type'] }}"></span>
                        <span class="dqm-activity-title">{{ $item['title'] }}</span>
                        <span class="dqm-activity-time">{{ \Carbon\Carbon::parse($item['time'])->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="dqm-empty">Nothing new yet.</p>
                @endforelse

                <div class="dqm-activity-msg-head"><span>Messages</span><a href="{{ route('chat.index') }}">Open chat</a></div>
                @forelse(($recentConversations ?? collect())->take(4) as $conv)
                    <a href="{{ route('chat.index') }}" class="dqm-chat-row">
                        <div class="dqm-chat-avatar">
                            @if($conv['other_avatar'])<img src="{{ $conv['other_avatar'] }}" alt="">@else<span>{{ strtoupper(substr($conv['other_name'],0,1)) }}</span>@endif
                        </div>
                        <div class="dqm-chat-body">
                            <span class="dqm-chat-name">{{ $conv['other_name'] }}</span>
                            <span class="dqm-chat-snippet">{{ \Illuminate\Support\Str::limit($conv['last_body'], 42) }}</span>
                        </div>
                        <div class="dqm-chat-meta">
                            <span class="dqm-chat-time">{{ $conv['last_at'] ? \Carbon\Carbon::parse($conv['last_at'])->diffForHumans(null, true) : '' }}</span>
                            @if($conv['unread'] > 0)<span class="dqm-chat-unread">{{ $conv['unread'] }}</span>@endif
                        </div>
                    </a>
                @empty
                    <p class="dqm-empty">No conversations yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
    (function () {
        if (!window.gsap) return;

        // Entrance: fade + slide up, staggered by data-gsap-order
        var items = Array.prototype.slice.call(document.querySelectorAll('.dqm-gsap-in'));
        items.sort(function (a, b) {
            return (parseInt(a.dataset.gsapOrder) || 0) - (parseInt(b.dataset.gsapOrder) || 0);
        });
        gsap.set(items, { opacity: 0, y: 24 });
        gsap.to(items, {
            opacity: 1,
            y: 0,
            duration: 0.55,
            ease: 'power2.out',
            stagger: 0.12,
            delay: 0.05
        });

        // Interaction: balance action buttons press feedback
        document.querySelectorAll('.dqm-bal-btn').forEach(function (btn) {
            btn.addEventListener('pointerdown', function () {
                gsap.to(btn, { scale: 0.95, duration: 0.12, ease: 'power1.out' });
            });
            btn.addEventListener('pointerup', function () {
                gsap.to(btn, { scale: 1, duration: 0.25, ease: 'back.out(2)' });
            });
            btn.addEventListener('pointerleave', function () {
                gsap.to(btn, { scale: 1, duration: 0.25, ease: 'back.out(2)' });
            });
        });

        // Interaction: activity row press feedback
        document.querySelectorAll('.dqm-arow').forEach(function (row) {
            row.addEventListener('pointerdown', function () {
                gsap.to(row, { scale: 0.97, duration: 0.12, ease: 'power1.out' });
            });
            row.addEventListener('pointerup', function () {
                gsap.to(row, { scale: 1, duration: 0.3, ease: 'back.out(2)' });
            });
            row.addEventListener('pointerleave', function () {
                gsap.to(row, { scale: 1, duration: 0.3, ease: 'back.out(2)' });
            });
        });

    })();
    </script>

</div>

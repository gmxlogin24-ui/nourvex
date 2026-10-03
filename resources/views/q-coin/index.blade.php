@extends('layouts.app-authenticated')
@section('content')
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
@include('q-coin.partials.q-coin-minimal')
@else
<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1 flex items-center gap-2"><i data-lucide="gem" class="w-5 h-5"></i> Q Coin</h1>
    <p class="text-muted text-sm mb-6">Earn coins daily, build a streak, and redeem for rewards.</p>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <div class="stat-card p-6 mb-4 text-center" style="background:linear-gradient(135deg,#9b7cf0,#7c5cf0); color:#fff;">
        <p class="text-xs opacity-80 uppercase tracking-wide font-semibold">Balance</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($balance) }} <span class="text-lg font-medium">Q</span></p>
        <p class="text-xs opacity-80 mt-2">{{ $streakDays }} day streak</p>
    </div>

    <div class="mb-6" id="qcClaimWrap" style="position:relative;">
        <button type="button" id="qcClaimBtn" class="btn-primary w-full flex items-center justify-center gap-2" data-claiming="0" {{ ($canClaimToday && ! $qcoinLocked) ? '' : 'disabled' }}>
            <i data-lucide="gift" class="w-4 h-4"></i>
            <span id="qcClaimLabel">{{ $qcoinLocked ? 'Upgrade to Silver to claim daily Q' : ($canClaimToday ? 'Claim Daily Bonus (+' . ($dailyBase + $nextStreakBonus) . ' Q)' : 'Already Claimed Today') }}</span>
        </button>
        <div id="qcBurstLayer" style="position:absolute;inset:0;pointer-events:none;overflow:visible;"></div>
    </div>

    <style>
    #qcClaimBtn { transition: transform 0.15s ease; }
    .qc-coin {
        position: absolute; top: 50%; left: 50%;
        width: 14px; height: 14px; border-radius: 50%;
        background: radial-gradient(circle at 35% 35%, #ffe98a, #f5b93b 70%);
        box-shadow: 0 0 8px rgba(245,185,59,0.9);
        font-size: 9px; font-weight: 800; color: #6b4a00;
        display: flex; align-items: center; justify-content: center;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    #qcStreakFlame {
        display: inline-block;
        filter: drop-shadow(0 0 0px rgba(255,140,0,0));
        transition: filter 0.3s ease;
    }
    #qcStreakFlame.qc-flame-pulse {
        filter: drop-shadow(0 0 14px rgba(255,140,0,0.9));
    }
    </style>

    <script>
    (function () {
        var btn = document.getElementById('qcClaimBtn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (btn.disabled || btn.dataset.claiming === '1') return;
            btn.dataset.claiming = '1';

            var burstLayer = document.getElementById('qcBurstLayer');
            var label = document.getElementById('qcClaimLabel');

            // Button press feedback
            if (window.gsap) {
                gsap.to(btn, { scale: 0.95, duration: 0.1, ease: 'power1.out', onComplete: function () {
                    gsap.to(btn, { scale: 1, duration: 0.35, ease: 'back.out(3)' });
                }});
            }

            fetch(btn.closest('#qcClaimWrap') ? "{{ route('qcoin.claim') }}" : "", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(function () {
                // Coin burst — a dozen coins fired outward with GSAP
                if (window.gsap && burstLayer) {
                    var coinCount = 14;
                    for (var i = 0; i < coinCount; i++) {
                        var coin = document.createElement('div');
                        coin.className = 'qc-coin';
                        coin.textContent = 'Q';
                        burstLayer.appendChild(coin);

                        var angle = (Math.PI * 2 * i) / coinCount + (Math.random() * 0.4);
                        var distance = 70 + Math.random() * 60;
                        var dx = Math.cos(angle) * distance;
                        var dy = Math.sin(angle) * distance - 40;

                        gsap.fromTo(coin,
                            { opacity: 1, scale: 0.4, x: 0, y: 0 },
                            {
                                opacity: 0, scale: 1.1, x: dx, y: dy,
                                duration: 0.9 + Math.random() * 0.3,
                                ease: 'power2.out',
                                onComplete: function () { this.targets()[0].remove(); }
                            }
                        );
                    }
                }

                // Balance counter tick-up
                var balanceEl = document.querySelector('.stat-card .text-3xl');
                if (balanceEl && window.gsap) {
                    var current = parseInt((balanceEl.textContent || '0').replace(/[^0-9]/g, '')) || 0;
                    var target = current + 50;
                    var obj = { val: current };
                    gsap.to(obj, {
                        val: target,
                        duration: 1.1,
                        ease: 'power2.out',
                        onUpdate: function () {
                            balanceEl.innerHTML = Math.round(obj.val).toLocaleString() + ' <span class="text-lg font-medium">Q</span>';
                        }
                    });
                }

                // Streak flame pulse
                var streakEl = document.querySelector('.stat-card .text-xs.opacity-80.mt-2');
                if (streakEl) {
                    var flame = document.createElement('span');
                    flame.id = 'qcStreakFlame';
                    flame.textContent = ' 🔥';
                    streakEl.appendChild(flame);
                    requestAnimationFrame(function () {
                        flame.classList.add('qc-flame-pulse');
                    });
                    if (window.gsap) {
                        gsap.fromTo(streakEl, { scale: 1 }, { scale: 1.08, duration: 0.25, ease: 'power1.out', yoyo: true, repeat: 1 });
                    }
                }

                label.textContent = 'Already Claimed Today';
                btn.disabled = true;
            }).catch(function () {
                btn.dataset.claiming = '0';
            });
        });
    })();
    </script>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Today</p>
            <p class="text-lg font-semibold">{{ number_format($todayEarned) }}</p>
        </div>
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Total Earned</p>
            <p class="text-lg font-semibold">{{ number_format($totalEarned) }}</p>
        </div>
        <div class="stat-card p-4 text-center">
            <p class="text-xs text-muted">Total Spent</p>
            <p class="text-lg font-semibold">{{ number_format($totalSpent) }}</p>
        </div>
    </div>

    <h2 class="text-sm font-semibold mb-3 text-muted uppercase tracking-wide">History</h2>
    <div class="flex flex-col gap-2">
        @forelse($history as $h)
            <div class="stat-card p-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">{{ $h->description ?? ucfirst(str_replace('_', ' ', $h->type)) }}</p>
                    <p class="text-xs text-muted">{{ $h->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-sm font-semibold" style="color:{{ $h->amount >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $h->amount >= 0 ? '+' : '' }}{{ number_format($h->amount) }}
                </span>
            </div>
        @empty
            <p class="text-muted text-sm text-center py-8">No Q Coin activity yet.</p>
        @endforelse
    </div>
</main>
@endif
@endsection

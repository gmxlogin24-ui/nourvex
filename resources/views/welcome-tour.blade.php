@extends('layouts.app-authenticated')

@section('title', 'Welcome')

@section('content')
<div class="wtx-page">

    <button type="button" class="wtx-skip" id="wtxSkip">Skip</button>

    <div class="wtx-slides">

        <div class="wtx-slide wtx-active" data-slide="0">
            <div class="wtx-illustration">
                <div class="wtx-grid">
                    <div class="wtx-tile" style="background:#ffe08a;"><i data-lucide="briefcase" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#a5d8ff;"><i data-lucide="hammer" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#b2f2bb;"><i data-lucide="paintbrush" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#ffc9c9;"><i data-lucide="code" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#d0bfff;"><i data-lucide="camera" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#99e9f2;"><i data-lucide="book-open" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#ffd8a8;"><i data-lucide="megaphone" class="w-7 h-7"></i></div>
                    <div class="wtx-tile" style="background:#eebefa;"><i data-lucide="scissors" class="w-7 h-7"></i></div>
                </div>
            </div>
            <h1 class="wtx-title">Find Gigs & Hustles</h1>
            <p class="wtx-desc">Post a gig or pick one up nearby — start earning from your skills today.</p>
        </div>

        <div class="wtx-slide" data-slide="1">
            <div class="wtx-illustration">
                <div class="wtx-orbit">
                    <div class="wtx-orbit-center"><i data-lucide="shopping-bag" class="w-9 h-9"></i></div>
                    <div class="wtx-orbit-item" style="background:#2ba884;top:6%;left:50%;"><i data-lucide="shirt" class="w-5 h-5"></i></div>
                    <div class="wtx-orbit-item" style="background:#4dabf7;top:28%;left:88%;"><i data-lucide="smartphone" class="w-5 h-5"></i></div>
                    <div class="wtx-orbit-item" style="background:#ff922b;top:72%;left:88%;"><i data-lucide="book" class="w-5 h-5"></i></div>
                    <div class="wtx-orbit-item" style="background:#e64980;top:94%;left:50%;"><i data-lucide="tag" class="w-5 h-5"></i></div>
                    <div class="wtx-orbit-item" style="background:#845ef7;top:72%;left:12%;"><i data-lucide="headphones" class="w-5 h-5"></i></div>
                    <div class="wtx-orbit-item" style="background:#20c997;top:28%;left:12%;"><i data-lucide="package" class="w-5 h-5"></i></div>
                </div>
            </div>
            <h1 class="wtx-title">Buy & Sell on Campus</h1>
            <p class="wtx-desc">Shop trusted listings from fellow students — every purchase is protected in escrow.</p>
        </div>

        <div class="wtx-slide" data-slide="2">
            <div class="wtx-illustration">
                <div class="wtx-wallet-card">
                    <i data-lucide="wallet" class="w-8 h-8"></i>
                    <span class="wtx-wallet-amt">₦0.00</span>
                </div>
                <div class="wtx-coin wtx-coin-1"><i data-lucide="gem" class="w-5 h-5"></i></div>
                <div class="wtx-coin wtx-coin-2"><i data-lucide="gem" class="w-5 h-5"></i></div>
                <div class="wtx-coin wtx-coin-3"><i data-lucide="gem" class="w-5 h-5"></i></div>
            </div>
            <h1 class="wtx-title">Your Wallet, Your Rewards</h1>
            <p class="wtx-desc">Send money, top up airtime, and earn QCoins the more you use Campulsy.</p>
        </div>

        <div class="wtx-slide" data-slide="3">
            <div class="wtx-question-icon"><i data-lucide="sparkles" class="w-7 h-7"></i></div>
            <h1 class="wtx-title">What are you into?</h1>
            <p class="wtx-desc">Pick up to 5 — we'll use these to show you gigs and listings you'll actually care about.</p>
            <div class="wtx-chip-grid" id="wtxInterestChips">
                @foreach($categories as $cat)
                    <button type="button" class="wtx-chip" data-value="{{ $cat }}">{{ ucwords(str_replace('_', ' ', $cat)) }}</button>
                @endforeach
            </div>
            <p class="wtx-chip-hint" id="wtxChipHint">Select at least 1</p>
        </div>

        <div class="wtx-slide" data-slide="4">
            <div class="wtx-illustration">
                <div class="wtx-notif-phone">
                    <div class="wtx-notif-card">
                        <span class="wtx-notif-dot"></span>
                        <div>
                            <p class="wtx-notif-title">New gig near you</p>
                            <p class="wtx-notif-sub">Someone just posted a hustle</p>
                        </div>
                    </div>
                    <div class="wtx-notif-card wtx-notif-card-2">
                        <span class="wtx-notif-dot" style="background:#4dabf7;"></span>
                        <div>
                            <p class="wtx-notif-title">Order confirmed</p>
                            <p class="wtx-notif-sub">Your item has shipped</p>
                        </div>
                    </div>
                </div>
            </div>
            <h1 class="wtx-title">Don't miss out</h1>
            <p class="wtx-desc">Turn on notifications for gig updates, messages, and order status.</p>
            <button type="button" class="wtx-btn wtx-btn-secondary" id="wtxNotifBtn">Turn on notifications</button>
            <p class="wtx-notif-status" id="wtxNotifStatus"></p>
        </div>

        <div class="wtx-slide" data-slide="5">
            <div class="wtx-illustration">
                <div class="wtx-logo-circle"><i data-lucide="sparkles" class="w-12 h-12"></i></div>
            </div>
            <h1 class="wtx-title">Welcome to Campulsy</h1>
            <p class="wtx-desc">Everything you need for campus life — gigs, marketplace, and money — in one place.</p>
        </div>

    </div>

    <div class="wtx-dots" id="wtxDots">
        <span class="wtx-dot wtx-dot-active"></span>
        <span class="wtx-dot"></span>
        <span class="wtx-dot"></span>
        <span class="wtx-dot"></span>
        <span class="wtx-dot"></span>
        <span class="wtx-dot"></span>
    </div>

    <button type="button" class="wtx-btn" id="wtxNextBtn">Continue</button>

</div>

<style>
.wtx-page{position:fixed;top:0;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:420px;margin:0;padding:60px 24px 40px;box-sizing:border-box;display:flex;flex-direction:column;background:#fff;overflow-y:auto;z-index:9999;}
.wtx-skip{position:absolute;top:20px;right:20px;background:none;border:none;color:#8a8a8a;font-size:13px;font-weight:600;}
.wtx-slides{flex:1;position:relative;}
.wtx-slide{display:none;flex-direction:column;align-items:center;text-align:center;animation:wtxFadeIn 0.35s ease both;}
.wtx-slide.wtx-active{display:flex;}
@keyframes wtxFadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.wtx-illustration{width:100%;max-width:320px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;position:relative;margin:20px 0 36px;}

.wtx-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;width:100%;background:#f4f4f4;border-radius:26px;padding:22px;}
.wtx-tile{aspect-ratio:1;border-radius:18px;display:flex;align-items:center;justify-content:center;color:#1a1a1a;box-shadow:0 10px 22px rgba(0,0,0,0.12);}

.wtx-orbit{position:relative;width:100%;height:100%;}
.wtx-orbit-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:112px;height:112px;border-radius:50%;background:#141414;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 16px 34px rgba(0,0,0,0.28);z-index:2;}
.wtx-orbit-item{position:absolute;width:46px;height:46px;border-radius:50%;transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 20px rgba(0,0,0,0.22);}

.wtx-wallet-card{width:260px;height:150px;border-radius:22px;background:linear-gradient(160deg,#123b31,#0a1f1a);color:#fff;display:flex;flex-direction:column;justify-content:space-between;padding:22px;box-shadow:0 18px 36px rgba(0,0,0,0.3);}
.wtx-wallet-amt{font-size:20px;font-weight:800;}
.wtx-coin{position:absolute;width:42px;height:42px;border-radius:50%;background:#ffd23f;color:#1a1a1a;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 20px rgba(0,0,0,0.22);}
.wtx-coin-1{top:8%;right:10%;}
.wtx-coin-2{bottom:12%;left:6%;}
.wtx-coin-3{bottom:-2%;right:18%;}

.wtx-logo-circle{width:170px;height:170px;border-radius:50%;background:linear-gradient(145deg,#2ba884,#123b31);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 20px 44px rgba(0,0,0,0.3);}

.wtx-question-icon{width:64px;height:64px;border-radius:50%;background:#141414;color:#fff;display:flex;align-items:center;justify-content:center;margin:20px 0 18px;box-shadow:0 14px 28px rgba(0,0,0,0.2);}

.wtx-title{font-size:22px;font-weight:800;color:#111;margin:0 0 10px;}
.wtx-desc{font-size:13px;color:#8a8a8a;line-height:1.6;max-width:300px;margin:0;}

.wtx-chip-grid{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:22px 0 10px;}
.wtx-chip{background:#f4f4f4;border:2px solid transparent;color:#333;font-size:12.5px;font-weight:600;padding:10px 16px;border-radius:999px;}
.wtx-chip.wtx-chip-selected{background:#eafaf3;border-color:#2ba884;color:#0a7a52;}
.wtx-chip-hint{font-size:11px;color:#b0b0b0;margin:0;}

.wtx-notif-phone{display:flex;flex-direction:column;gap:12px;width:100%;max-width:280px;}
.wtx-notif-card{display:flex;align-items:flex-start;gap:10px;background:#fff;border-radius:16px;padding:14px;box-shadow:0 14px 28px rgba(0,0,0,0.1);text-align:left;}
.wtx-notif-card-2{margin-left:24px;}
.wtx-notif-dot{width:8px;height:8px;border-radius:50%;background:#2ba884;margin-top:5px;flex-shrink:0;}
.wtx-notif-title{font-size:12.5px;font-weight:700;color:#111;margin:0;}
.wtx-notif-sub{font-size:11px;color:#999;margin:2px 0 0;}
.wtx-notif-status{font-size:11px;color:#2ba884;margin:10px 0 0;min-height:14px;}

.wtx-dots{display:flex;justify-content:center;gap:6px;margin:24px 0;}
.wtx-dot{width:6px;height:6px;border-radius:999px;background:#e2e2e2;transition:width 0.25s ease,background 0.25s ease;}
.wtx-dot-active{width:22px;background:#141414;}

.wtx-btn{width:100%;padding:16px;border-radius:999px;border:none;background:#141414;color:#fff;font-size:14px;font-weight:700;}
.wtx-btn-secondary{background:#fff;color:#141414;border:2px solid #141414;margin-top:8px;}
</style>

<script>
(function(){
    var slides = document.querySelectorAll('.wtx-slide');
    var dots = document.querySelectorAll('.wtx-dot');
    var nextBtn = document.getElementById('wtxNextBtn');
    var skipBtn = document.getElementById('wtxSkip');
    var current = 0;
    var selectedInterests = [];

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function render(){
        slides.forEach(function(s, i){ s.classList.toggle('wtx-active', i === current); });
        dots.forEach(function(d, i){ d.classList.toggle('wtx-dot-active', i === current); });
        nextBtn.textContent = (current === slides.length - 1) ? 'Get Started' : 'Continue';
    }

    // Interests chip selection
    var chipContainer = document.getElementById('wtxInterestChips');
    if (chipContainer) {
        chipContainer.querySelectorAll('.wtx-chip').forEach(function(chip){
            chip.addEventListener('click', function(){
                var val = chip.dataset.value;
                var idx = selectedInterests.indexOf(val);
                if (idx > -1) {
                    selectedInterests.splice(idx, 1);
                    chip.classList.remove('wtx-chip-selected');
                } else {
                    if (selectedInterests.length >= 5) return;
                    selectedInterests.push(val);
                    chip.classList.add('wtx-chip-selected');
                }
                var hint = document.getElementById('wtxChipHint');
                if (hint) hint.textContent = selectedInterests.length + ' selected (max 5)';
            });
        });
    }

    function saveInterests(){
        if (selectedInterests.length === 0) return;
        var body = new URLSearchParams();
        selectedInterests.forEach(function(v){ body.append('interests[]', v); });
        fetch("{{ route('onboarding.interests.store') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).catch(function(){});
    }

    // Notifications permission
    var notifBtn = document.getElementById('wtxNotifBtn');
    var notifStatus = document.getElementById('wtxNotifStatus');
    if (notifBtn) {
        notifBtn.addEventListener('click', function(){
            if (!('Notification' in window)) {
                notifStatus.textContent = 'Notifications are not supported on this browser.';
                return;
            }
            Notification.requestPermission().then(function(permission){
                if (permission === 'granted') {
                    notifStatus.textContent = 'Notifications enabled!';
                } else {
                    notifStatus.textContent = 'You can enable this later in settings.';
                }
            });
        });
    }

    function finish(){
        fetch("{{ route('tour.complete') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).finally(function(){
            window.location.href = "{{ route('dashboard') }}";
        });
    }

    nextBtn.addEventListener('click', function(){
        var currentSlideEl = slides[current];
        if (currentSlideEl.dataset.slide === '3') {
            if (selectedInterests.length === 0) {
                var hint = document.getElementById('wtxChipHint');
                if (hint) { hint.textContent = 'Pick at least 1 to continue'; hint.style.color = '#e64980'; }
                return;
            }
            saveInterests();
        }
        if (current === slides.length - 1) {
            finish();
            return;
        }
        current++;
        render();
    });

    skipBtn.addEventListener('click', finish);

    render();
})();
</script>
@endsection

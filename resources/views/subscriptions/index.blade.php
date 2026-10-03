@extends('layouts.app-authenticated')

@section('content')
@php
    $plain = (auth()->user()->theme_style ?? 'glass') === 'plain';
    $cfg = config('plans');
    $rank = ['tier1' => 1, 'tier2' => 2, 'tier3' => 3];
    $currentRank = $current ? ($rank[$current->plan] ?? 0) : 0;
    $startTab = $current ? $current->plan : 'tier2';
@endphp
<style>
.pl-wrap{max-width:34rem;margin:0 auto;padding:1.5rem;}
.pl-title{font-size:1.6rem;font-weight:700;margin:0;}
.pl-sub{font-size:.85rem;margin:.25rem 0 1.25rem;}
.pl-current{display:flex;justify-content:space-between;align-items:center;padding:1rem;margin-bottom:1rem;}
.pl-tabs{display:flex;gap:.3rem;padding:.3rem;border-radius:999px;background:color-mix(in srgb,currentColor 8%,transparent);margin-bottom:1rem;}
.pl-tab{flex:1;border:0;background:transparent;color:inherit;padding:.6rem .3rem;border-radius:999px;font-size:.85rem;font-weight:600;opacity:.7;cursor:pointer;position:relative;}
.pl-tab.is-on{background:var(--color-accent);color:#fff;opacity:1;}
.pl-flag{display:block;font-size:.6rem;font-weight:600;letter-spacing:.02em;opacity:.85;}
.pl-panel{display:none;padding:1.5rem;}
.pl-panel.is-on{display:block;}
.pl-tag{font-size:.8rem;opacity:.65;margin:0;}
.pl-name{font-size:1.8rem;font-weight:700;margin:.15rem 0 0;}
.pl-price{font-size:2rem;font-weight:700;margin:.4rem 0 0;}
.pl-per{font-size:.85rem;font-weight:400;opacity:.65;}
.pl-chip{display:inline-block;margin:.4rem 0 1rem;padding:.2rem .6rem;border-radius:999px;font-size:.75rem;background:color-mix(in srgb,var(--color-accent) 14%,transparent);color:var(--color-accent);}
.pl-intro{font-size:.85rem;font-weight:600;margin:0 0 .4rem;}
.pl-list{list-style:none;margin:0;padding:0;}
.pl-list li{display:flex;gap:.75rem;align-items:center;padding:.55rem 0;font-size:.9rem;}
.pl-ico{width:2rem;height:2rem;border-radius:.6rem;display:grid;place-items:center;flex:none;background:color-mix(in srgb,var(--color-accent) 14%,transparent);color:var(--color-accent);}
.pl-limit{font-size:.78rem;opacity:.65;margin:.5rem 0 0;}
.pl-form{margin-top:1.25rem;}
.pl-form>*+*{margin-top:.6rem;}
.pl-note{margin-top:1.25rem;font-size:.85rem;opacity:.75;text-align:center;}
.pl-note.is-ok{color:var(--color-accent);opacity:1;font-weight:600;}
.pl-more{margin-top:1rem;}
.pl-more summary{cursor:pointer;text-align:center;font-size:.9rem;font-weight:600;padding:.8rem;border-radius:999px;background:color-mix(in srgb,currentColor 8%,transparent);list-style:none;}
.pl-scroll{overflow-x:auto;margin-top:.75rem;}
.pl-table{width:100%;border-collapse:collapse;font-size:.8rem;}
.pl-table th,.pl-table td{padding:.6rem .4rem;text-align:center;border-bottom:1px solid color-mix(in srgb,currentColor 10%,transparent);}
.pl-table th:first-child,.pl-table td:first-child{text-align:left;}
.pl-yes{color:var(--color-accent);font-weight:700;}
.pl-no{opacity:.35;}
.pl-foot{font-size:.75rem;opacity:.6;text-align:center;margin-top:1rem;}
</style>
<main class="pl-wrap {{ $plain ? 'dqm-page' : '' }}" style="{{ $plain ? 'padding-top:0 !important;' : '' }}">
    <h1 class="pl-title">Choose your plan</h1>
    <p class="text-muted pl-sub">Billed once a year. Upgrade any time.</p>

    @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    @if ($current)
        <div class="glass-card pl-current {{ $plain ? 'dqm-card' : '' }}">
            <div>
                <p class="text-sm text-muted">Your plan</p>
                <p class="font-semibold">{{ $plans[$current->plan]['label'] }}</p>
            </div>
            <div style="text-align:right;">
                <p class="text-sm text-muted">Renews / expires</p>
                <p class="font-semibold {{ $current->isExpiringSoon() ? 'text-red-500' : 'text-accent' }}">{{ $current->expires_at->format('M j, Y') }}</p>
            </div>
        </div>
    @endif

    <div class="pl-tabs" role="tablist">
        @foreach ($plans as $key => $plan)
            <button type="button" class="pl-tab {{ $key === $startTab ? 'is-on' : '' }}" data-tab="{{ $key }}">{{ $plan['label'] }}@if ($key === 'tier2')<span class="pl-flag">Best value</span>@endif</button>
        @endforeach
    </div>

    @foreach ($plans as $key => $plan)
        @php $info = $cfg['tiers'][$key] ?? ['tagline' => '', 'intro' => '', 'highlights' => [], 'note' => null]; @endphp
        <section class="glass-card pl-panel {{ $key === $startTab ? 'is-on' : '' }} {{ $plain ? 'dqm-card' : '' }}" data-panel="{{ $key }}">
            <p class="pl-tag">{{ $info['tagline'] }}</p>
            <h2 class="pl-name">{{ $plan['label'] }}</h2>
            <p class="pl-price">₦{{ number_format($plan['price']) }}<span class="pl-per"> / year</span></p>
            <p class="pl-chip">About ₦{{ number_format($plan['price'] / 12) }} a month</p>
            <p class="pl-intro">{{ $info['intro'] }}</p>
            <ul class="pl-list">
                @foreach ($info['highlights'] as $h)
                    <li><span class="pl-ico"><i data-lucide="{{ $h[0] }}" style="width:16px;height:16px"></i></span><span>{{ $h[1] }}</span></li>
                @endforeach
            </ul>
            @if (!empty($info['note']))
                <p class="pl-limit">{{ $info['note'] }}</p>
            @endif

            @if ($current && $current->plan === $key)
                <p class="pl-note is-ok">✓ Active until {{ $current->expires_at->format('M j, Y') }}</p>
            @elseif ($rank[$key] < $currentRank)
                <p class="pl-note">Included in your current plan</p>
            @else
                <form method="POST" action="{{ route('subscriptions.subscribe') }}" class="pl-form">
                    @csrf
                    <input type="hidden" name="target_tier" value="{{ $key }}">
                    <select name="payment_method" class="input-field text-sm">
                        <option value="wallet">Pay from wallet (₦{{ number_format($user->wallet_balance) }} available)</option>
                        <option value="paystack">Pay by card or bank</option>
                    </select>
                    <button type="submit" class="btn-primary w-full justify-center">{{ $current ? 'Upgrade to' : 'Join' }} {{ $plan['label'] }} · ₦{{ number_format($plan['price']) }}</button>
                </form>
            @endif
        </section>
    @endforeach

    <details class="pl-more">
        <summary>See all benefits</summary>
        <div class="pl-scroll">
            <table class="pl-table">
                <thead>
                    <tr>
                        <th></th>
                        @foreach ($plans as $plan)
                            <th>{{ $plan['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cfg['compare'] as $row)
                        <tr>
                            <td>{{ $row[0] }}</td>
                            @foreach ([1, 2, 3] as $c)
                                <td>
                                    @if ($row[$c] === true)
                                        <span class="pl-yes">✓</span>
                                    @elseif ($row[$c] === false)
                                        <span class="pl-no">–</span>
                                    @else
                                        {{ $row[$c] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>

    <p class="pl-foot">Your plan activates right after payment.</p>
</main>
<script>
document.querySelectorAll('.pl-tab').forEach(function (b) {
    b.addEventListener('click', function () {
        var t = b.getAttribute('data-tab');
        document.querySelectorAll('.pl-tab').forEach(function (x) { x.classList.toggle('is-on', x === b); });
        document.querySelectorAll('.pl-panel').forEach(function (p) { p.classList.toggle('is-on', p.getAttribute('data-panel') === t); });
    });
});
if (window.lucide) { lucide.createIcons(); }
</script>
@endsection

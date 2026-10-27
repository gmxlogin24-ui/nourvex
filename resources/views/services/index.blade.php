@extends('layouts.app-authenticated')
@section('content')
<link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
<link rel="stylesheet" href="{{ asset('css/services-redesign.css') }}?v={{ filemtime(public_path('css/services-redesign.css')) }}">

<main class="max-w-2xl mx-auto p-6 svc-wrap {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
    @php
  $svcMeta = [
    'airtime' => ['Buy Airtime', 'Top up any network instantly', 'smartphone', 'linear-gradient(145deg,#38BDF8,#2563EB)'],
    'data' => ['Buy Data', 'Bundles for every network', 'wifi', 'linear-gradient(145deg,#34D399,#059669)'],
    'cable' => ['Cable TV', 'GOTV, DSTV and StarTimes', 'tv', 'linear-gradient(145deg,#FB923C,#EA580C)'],
  ];
  $svcM = $svcMeta[$service];
@endphp
<div class="svcx-head">
    <a href="{{ route('dashboard') }}" class="svcx-back"><i data-lucide="arrow-left"></i> Home</a>
    <div class="svcx-title">
        <span class="svcx-ico" style="--svcx:{{ $svcM[3] }}"><i data-lucide="{{ $svcM[2] }}"></i></span>
        <div><h1>{{ $svcM[0] }}</h1><p>{{ $svcM[1] }}</p></div>
    </div>
</div>
<nav class="svcx-switch" aria-label="Services">
    @foreach(['airtime' => 'Airtime', 'data' => 'Data', 'cable' => 'Cable'] as $k => $l)
    <a href="{{ route('services.show', $k) }}" class="svcx-pill {{ $service === $k ? 'on' : '' }}">{{ $l }}</a>
    @endforeach
    <span class="svcx-pill soon">Electricity</span>
    <span class="svcx-pill soon">Betting</span>
    <span class="svcx-pill soon">Education</span>
    <a href="{{ route('services.index') }}" class="svcx-pill">All</a>
</nav>
<div class="svc-balance-strip">
        <div>
            <div class="svc-balance-label">Wallet Balance</div>
            <div class="svc-balance-amount">₦{{ number_format($balance, 2) }}</div>
        </div>
        <i data-lucide="wallet" class="w-7 h-7" style="opacity:0.6;"></i>
    </div>

    @unless($hasPin)
        <div class="alert alert-error mb-4">
            You need a transaction PIN before you can make purchases.
            <a href="{{ route('wallet.pin.edit') }}" class="underline font-semibold">Set one now</a>.
        </div>
    @endunless

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
    @endif


    

    {{-- AIRTIME --}}
    <div id="airtime-tab" class="stat-card p-6 svcx-card" style="{{ $service === 'airtime' ? '' : 'display:none;' }}">
        <form method="POST" action="{{ route('services.airtime') }}">
            @csrf
            <span class="svc-section-label">Network</span>
            <div class="svc-option-grid" id="airtime-network-grid">
                @foreach($networks as $n)
                    <button type="button" class="svc-option-btn" data-network="{{ $n }}" onclick="selectOption(this, 'airtime-network-grid', 'airtime-network-input')">
                        <div class="svc-option-icon svc-icon-{{ $n }}">{{ strtoupper(substr($n,0,2)) }}</div>
                        <span class="svc-option-label">{{ strtoupper($n) }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="network" id="airtime-network-input" required>

            <label class="svc-field-label">Phone Number</label>
            <input type="text" name="phone" id="airtime-phone" required maxlength="14" placeholder="080XXXXXXXX" class="svc-field" oninput="autoDetectNetwork('airtime-phone','airtime-network-grid','airtime-network-input')">
            @if($savedAirtimeNumbers->isNotEmpty())
                <div class="svc-chip-row">
                    @foreach($savedAirtimeNumbers as $s)
                        <button type="button" class="svc-chip" onclick="document.getElementById('airtime-phone').value='{{ $s->number }}'">{{ $s->number }}</button>
                    @endforeach
                </div>
            @endif

            <span class="svc-section-label">Amount</span>
            <div class="svc-amount-grid" id="airtime-amount-grid">
                @foreach([100, 200, 500, 1000, 2000, 5000] as $amt)
                    <button type="button" class="svc-amount-btn" onclick="selectAmount(this, 'airtime-amount-grid', {{ $amt }})">₦{{ number_format($amt) }}</button>
                @endforeach
            </div>
            <input type="number" name="amount" id="airtime-amount" min="50" required placeholder="Or enter custom amount" class="svc-field" oninput="clearAmountSelection('airtime-amount-grid')">

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="svc-field-label">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="svc-field" style="margin-bottom:16px;">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="svc-submit-btn" {{ $hasPin ? '' : 'disabled' }}>
                <i data-lucide="smartphone" class="w-4 h-4"></i> Buy Airtime
            </button>
        </form>
    </div>

    {{-- DATA --}}
    <div id="data-tab" class="stat-card p-6 svcx-card" style="{{ $service === 'data' ? '' : 'display:none;' }}">
        <form method="POST" action="{{ route('services.data') }}">
            @csrf
            <span class="svc-section-label">Network</span>
            <div class="svc-option-grid" id="data-network-grid">
                @foreach($networks as $n)
                    <button type="button" class="svc-option-btn {{ $loop->first ? 'selected' : '' }}" data-network="{{ $n }}" onclick="selectOption(this, 'data-network-grid', 'data-network-input'); loadPlans();">
                        <div class="svc-option-icon svc-icon-{{ $n }}">{{ strtoupper(substr($n,0,2)) }}</div>
                        <span class="svc-option-label">{{ strtoupper($n) }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="network" id="data-network-input" value="{{ $networks[0] }}" required>

            <label class="svc-field-label">Phone Number</label>
            <input type="text" name="phone" id="data-phone" required maxlength="14" placeholder="080XXXXXXXX" class="svc-field" oninput="autoDetectNetwork('data-phone','data-network-grid','data-network-input', loadPlans)">
            @if($savedDataNumbers->isNotEmpty())
                <div class="svc-chip-row">
                    @foreach($savedDataNumbers as $s)
                        <button type="button" class="svc-chip" onclick="document.getElementById('data-phone').value='{{ $s->number }}'">{{ $s->number }}</button>
                    @endforeach
                </div>
            @endif

            <span class="svc-section-label">Select Plan</span>
            <div class="svc-plan-grid" id="data-plan-grid">
                <p class="text-muted text-xs">Loading plans…</p>
            </div>
            <input type="hidden" name="plan_id" id="data-plan-input" required>
            <p class="text-muted text-xs mb-3" id="data-price-label"></p>

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="svc-field-label">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="svc-field" style="margin-bottom:16px;">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="svc-submit-btn" {{ $hasPin ? '' : 'disabled' }}>
                <i data-lucide="wifi" class="w-4 h-4"></i> Buy Data
            </button>
        </form>
    </div>

    {{-- CABLE --}}
    <div id="cable-tab" class="stat-card p-6 svcx-card" style="{{ $service === 'cable' ? '' : 'display:none;' }}">
        <form method="POST" action="{{ route('services.cable') }}">
            @csrf
            <span class="svc-section-label">Provider</span>
            <div class="svc-option-grid" id="cable-provider-grid" style="grid-template-columns:repeat(3,1fr);">
                <button type="button" class="svc-option-btn selected" data-provider="gotv" onclick="selectOption(this, 'cable-provider-grid', 'cable-provider-input'); loadCablePlans();">
                    <div class="svc-option-icon svc-icon-gotv">GO</div>
                    <span class="svc-option-label">GOTV</span>
                </button>
                <button type="button" class="svc-option-btn" data-provider="dstv" onclick="selectOption(this, 'cable-provider-grid', 'cable-provider-input'); loadCablePlans();">
                    <div class="svc-option-icon svc-icon-dstv">DS</div>
                    <span class="svc-option-label">DSTV</span>
                </button>
                <button type="button" class="svc-option-btn" data-provider="startime" onclick="selectOption(this, 'cable-provider-grid', 'cable-provider-input'); loadCablePlans();">
                    <div class="svc-option-icon svc-icon-startime">ST</div>
                    <span class="svc-option-label">StarTimes</span>
                </button>
            </div>
            <input type="hidden" name="provider" id="cable-provider-input" value="gotv" required>

            <label class="svc-field-label">Smart Card / IUC Number</label>
            <input type="text" name="smart_card_number" id="cable-card" required maxlength="20" placeholder="Smart card number" class="svc-field">
            @if($savedCards->isNotEmpty())
                <div class="svc-chip-row">
                    @foreach($savedCards as $s)
                        <button type="button" class="svc-chip" onclick="document.getElementById('cable-card').value='{{ $s->number }}'">{{ $s->number }} ({{ strtoupper($s->provider) }})</button>
                    @endforeach
                </div>
            @endif

            <span class="svc-section-label">Select Plan</span>
            <div class="svc-plan-grid" id="cable-plan-grid">
                <p class="text-muted text-xs">Loading plans…</p>
            </div>
            <input type="hidden" name="plan_id" id="cable-plan-input" required>

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="svc-field-label">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="svc-field" style="margin-bottom:16px;">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="svc-submit-btn" {{ $hasPin ? '' : 'disabled' }}>
                <i data-lucide="tv" class="w-4 h-4"></i> Subscribe
            </button>
        </form>
    </div>
</main>

<script>
function showTab(tab, evt) {
    document.getElementById('airtime-tab').style.display = tab === 'airtime' ? 'block' : 'none';
    document.getElementById('data-tab').style.display = tab === 'data' ? 'block' : 'none';
    document.getElementById('cable-tab').style.display = tab === 'cable' ? 'block' : 'none';
    document.querySelectorAll('.svc-tab-btn').forEach(b => b.classList.remove('active'));
    evt.target.classList.add('active');
}

// Nigerian mobile prefixes, sourced from NCC-derived public records.
// NOTE: due to Mobile Number Portability, a prefix shows the ORIGINAL
// network only - a user may have ported to a different one. This is why
// auto-detect only ever pre-selects a guess and never removes the manual
// option buttons - the moment a user taps a network themselves, the
// 'manual' flag below stops auto-detect from overriding their choice.
const NETWORK_PREFIXES = {
    mtn: ['0703','0706','0803','0806','0810','0813','0814','0816','0903','0906','0913','0916','0704'],
    glo: ['0705','0805','0807','0811','0815','0905','0915'],
    airtel: ['0701','0708','0802','0808','0812','0901','0902','0904','0907','0911','0912'],
    '9mobile': ['0809','0817','0818','0908','0909']
};

function detectNetworkFromPhone(phone) {
    const prefix = (phone || '').replace(/\D/g, '').slice(0, 4);
    if (prefix.length < 4) return null;
    for (const [network, prefixes] of Object.entries(NETWORK_PREFIXES)) {
        if (prefixes.includes(prefix)) return network;
    }
    return null;
}

function applySelection(value, gridId, inputId) {
    const grid = document.getElementById(gridId);
    const btn = grid.querySelector(`[data-network="${value}"], [data-provider="${value}"]`);
    if (!btn) return;
    grid.querySelectorAll('.svc-option-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById(inputId).value = value;
}

function selectOption(btn, gridId, inputId) {
    // Real user tap - lock out further auto-detect overrides for this grid.
    document.getElementById(gridId).dataset.manual = '1';
    applySelection(btn.dataset.network || btn.dataset.provider, gridId, inputId);
}

function autoDetectNetwork(phoneInputId, gridId, inputId, onDetect) {
    const grid = document.getElementById(gridId);
    if (grid.dataset.manual === '1') return; // user already chose manually - don't override
    const network = detectNetworkFromPhone(document.getElementById(phoneInputId).value);
    if (network) {
        applySelection(network, gridId, inputId);
        if (onDetect) onDetect();
    }
}

function selectAmount(btn, gridId, amount) {
    document.getElementById(gridId).querySelectorAll('.svc-amount-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('airtime-amount').value = amount;
}

function clearAmountSelection(gridId) {
    document.getElementById(gridId).querySelectorAll('.svc-amount-btn').forEach(b => b.classList.remove('selected'));
}

function selectPlan(btn, gridId, inputId, price, labelElId) {
    document.getElementById(gridId).querySelectorAll('.svc-plan-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById(inputId).value = btn.dataset.planId;
    if (labelElId) document.getElementById(labelElId).textContent = price ? `Price: ₦${price}` : '';
}

function loadPlans() {
    const network = document.getElementById('data-network-input').value;
    const grid = document.getElementById('data-plan-grid');
    grid.innerHTML = '<p class="text-muted text-xs">Loading plans…</p>';
    document.getElementById('data-plan-input').value = '';
    document.getElementById('data-price-label').textContent = '';

    fetch(`{{ route('services.data-plans') }}?network=${network}`)
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';
            if (!data.groups || data.groups.length === 0) {
                grid.innerHTML = '<p class="text-muted text-xs">No plans available right now</p>';
                return;
            }
            data.groups.forEach(group => {
                const label = document.createElement('div');
                label.className = 'svc-plan-group-label';
                label.textContent = group.label;
                grid.appendChild(label);
                group.plans.forEach(p => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'svc-plan-btn';
                    btn.dataset.planId = p.id;
                    btn.innerHTML = `<span class="svc-plan-size">${p.label}</span><span class="svc-plan-price">₦${p.price}</span>`;
                    btn.onclick = () => selectPlan(btn, 'data-plan-grid', 'data-plan-input', p.price, 'data-price-label');
                    grid.appendChild(btn);
                });
            });
        })
        .catch(() => { grid.innerHTML = '<p class="text-muted text-xs">Could not load plans — try again</p>'; });
}

function loadCablePlans() {
    const provider = document.getElementById('cable-provider-input').value;
    const grid = document.getElementById('cable-plan-grid');
    grid.innerHTML = '<p class="text-muted text-xs">Loading plans…</p>';
    document.getElementById('cable-plan-input').value = '';

    fetch(`{{ route('services.cable-plans') }}?provider=${provider}`)
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';
            if (!data.plans || data.plans.length === 0) {
                grid.innerHTML = '<p class="text-muted text-xs">No plans available right now</p>';
                return;
            }
            data.plans.forEach(p => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'svc-plan-btn';
                btn.dataset.planId = p.id;
                btn.innerHTML = `<span class="svc-plan-size">${p.label}</span><span class="svc-plan-price">₦${p.price}</span>`;
                btn.onclick = () => selectPlan(btn, 'cable-plan-grid', 'cable-plan-input', p.price, null);
                grid.appendChild(btn);
            });
        })
        .catch(() => { grid.innerHTML = '<p class="text-muted text-xs">Could not load plans — try again</p>'; });
}

document.addEventListener('DOMContentLoaded', () => {
    @if($service === 'data') loadPlans(); @endif
    @if($service === 'cable') loadCablePlans(); @endif
    lucide.createIcons();
});
</script>
<script>
(function () {
    var PLANS_URL = "{{ route('services.data-plans') }}";
    var NETNAMES = { mtn: 'MTN', glo: 'GLO', airtel: 'AIRTEL', '9mobile': '9MOBILE' };
    var planState = { groups: [], gi: 0, type: 'all', selected: '', req: 0 };

    function $(id) { return document.getElementById(id); }
    function digitsOnly(v) { return (v || '').replace(/\D/g, ''); }
    function num(v) { return parseFloat(String(v).replace(/[^0-9.]/g, '')); }

    function normalizePhone(v) {
        var d = digitsOnly(v);
        if (d.indexOf('234') === 0) {
            if (d.length >= 13) d = '0' + d.slice(3, 13); else return d;
        } else if (d.length === 10 && /^[789]/.test(d)) {
            d = '0' + d;
        }
        return d.slice(0, 11);
    }
    function phonePrefix(v) {
        var d = digitsOnly(v);
        if (d.indexOf('234') === 0) d = '0' + d.slice(3);
        return d.slice(0, 4);
    }

    function toast(msg) {
        var t = $('svcToast');
        if (!t) { t = document.createElement('div'); t.id = 'svcToast'; t.className = 'svc-toast'; document.body.appendChild(t); }
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._h);
        t._h = setTimeout(function () { t.classList.remove('show'); }, 2600);
        return null;
    }

    /* ---------- detection ---------- */
    function setBadge(phoneId, text, cls) {
        var el = $(phoneId + '-detect');
        if (!el) {
            el = document.createElement('div');
            el.id = phoneId + '-detect';
            $(phoneId).insertAdjacentElement('afterend', el);
        }
        el.className = 'svc-detect ' + (cls || '');
        el.textContent = text;
        el.style.display = text ? '' : 'none';
    }

    window.detectNetworkFromPhone = function (phone) {
        var p = phonePrefix(phone);
        if (p.length < 4) return null;
        for (var n in NETWORK_PREFIXES) {
            if (NETWORK_PREFIXES[n].indexOf(p) !== -1) return n;
        }
        return null;
    };

    window.autoDetectNetwork = function (phoneId, gridId, inputId, onDetect) {
        var el = $(phoneId), grid = $(gridId);
        if (!el || !grid) return;
        var nv = normalizePhone(el.value);
        if (nv !== el.value) el.value = nv;
        if (digitsOnly(el.value).length === 0) {
            delete grid.dataset.manual; delete grid.dataset.lastPrefix;
            setBadge(phoneId, '');
            return;
        }
        var prefix = phonePrefix(el.value);
        if (prefix.length < 4) { setBadge(phoneId, ''); return; }
        if (grid.dataset.lastPrefix && grid.dataset.lastPrefix !== prefix) delete grid.dataset.manual;
        grid.dataset.lastPrefix = prefix;

        var net = window.detectNetworkFromPhone(el.value);
        var current = $(inputId).value;
        if (!net) { setBadge(phoneId, 'Network not recognised, pick one below', 'warn'); return; }
        if (grid.dataset.manual === '1') {
            if (current && current !== net) {
                setBadge(phoneId, 'Looks like ' + NETNAMES[net] + ', you picked ' + (NETNAMES[current] || current.toUpperCase()) + ' (ported number?)', 'warn');
                return;
            }
        } else if (current !== net) {
            applySelection(net, gridId, inputId);
            if (onDetect) onDetect();
        }
        setBadge(phoneId, '\u2713 ' + NETNAMES[net] + ' detected', 'ok');
    };

    /* ---------- data plans: validity tabs + type chips + cards ---------- */
    function splitLabel(l) {
        l = l || '';
        var i = l.indexOf(' - ');
        var type = '', size = l;
        if (i > -1) { type = l.slice(0, i).trim(); size = l.slice(i + 3).trim(); }
        size = size.replace(/^-\s*/, '');
        return { type: type, size: size };
    }
    function findPlan(id) {
        for (var i = 0; i < planState.groups.length; i++) {
            var g = planState.groups[i];
            for (var j = 0; j < g.plans.length; j++) {
                if (String(g.plans[j].id) === String(id)) return { p: g.plans[j], g: g };
            }
        }
        return null;
    }
    function el(tag, cls, text) {
        var e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text !== undefined) e.textContent = text;
        return e;
    }

    function renderPlans() {
        var grid = $('data-plan-grid');
        grid.innerHTML = '';
        var wrap = el('div', 'svc-plan-wrap');
        wrap.style.cssText = 'grid-column:1/-1;width:100%;flex:0 0 100%;';

        var tabs = el('div', 'svc-plan-tabs');
        planState.groups.forEach(function (g, i) {
            var b = el('button', 'svc-plan-tab' + (i === planState.gi ? ' active' : ''), g.label);
            b.type = 'button';
            b.onclick = function () { planState.gi = i; planState.type = 'all'; renderPlans(); };
            tabs.appendChild(b);
        });
        wrap.appendChild(tabs);

        var g = planState.groups[planState.gi];
        var EXCLUDED_TYPES = ['gifting'];
        var plans = g.plans.filter(function (p) {
            var t = splitLabel(p.label).type.toLowerCase();
            return EXCLUDED_TYPES.indexOf(t) === -1;
        });
        var types = [];
        plans.forEach(function (p) {
            var t = splitLabel(p.label).type;
            if (t && types.indexOf(t) < 0) types.push(t);
        });
        if (planState.type !== 'all' && types.indexOf(planState.type) < 0) planState.type = 'all';

        if (types.length > 1) {
            var row = el('div', 'svc-type-row');
            ['all'].concat(types).forEach(function (t) {
                var c = el('button', 'svc-type-chip' + (planState.type === t ? ' active' : ''), t === 'all' ? 'All' : t);
                c.type = 'button';
                c.onclick = function () { planState.type = t; renderPlans(); };
                row.appendChild(c);
            });
            wrap.appendChild(row);
        }

        var cards = el('div', 'svc-plan-cards');
        var shown = plans.filter(function (p) {
            return planState.type === 'all' || splitLabel(p.label).type === planState.type;
        });
        shown.forEach(function (p) {
            var s = splitLabel(p.label);
            var b = el('button', 'svc-plan-btn svc-plan-card' + (String(p.id) === String(planState.selected) ? ' selected' : ''));
            b.type = 'button';
            b.dataset.planId = p.id;
            if (s.type) b.appendChild(el('span', 'svc-plan-type', s.type));
            b.appendChild(el('span', 'svc-plan-size', s.size));
            b.appendChild(el('span', 'svc-plan-validity', g.label));
            b.appendChild(el('span', 'svc-plan-price', '\u20a6' + p.price));
            b.onclick = function () {
                planState.selected = p.id;
                selectPlan(b, 'data-plan-grid', 'data-plan-input', p.price, 'data-price-label');
            };
            cards.appendChild(b);
        });
        if (!shown.length) cards.appendChild(el('p', 'text-muted text-xs', 'No plans in this category'));
        wrap.appendChild(cards);
        grid.appendChild(wrap);
    }

    window.loadPlans = function () {
        var network = $('data-network-input').value;
        var grid = $('data-plan-grid');
        grid.innerHTML = '<p class="text-muted text-xs">Loading plans\u2026</p>';
        $('data-plan-input').value = '';
        $('data-price-label').textContent = '';
        planState.selected = '';
        var req = ++planState.req;

        fetch(PLANS_URL + '?network=' + encodeURIComponent(network))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (req !== planState.req) return;
                if (!data.groups || data.groups.length === 0) {
                    grid.innerHTML = '<p class="text-muted text-xs">No plans available right now</p>';
                    return;
                }
                planState.groups = data.groups.slice().sort(function (a, b) { return (a.days || 0) - (b.days || 0); });
                planState.gi = 0;
                planState.type = 'all';
                renderPlans();
            })
            .catch(function () {
                if (req === planState.req) grid.innerHTML = '<p class="text-muted text-xs">Could not load plans, try again</p>';
            });
    };

    /* ---------- confirm sheet ---------- */
    var sheetForm = null;
    function ensureSheet() {
        if ($('svcSheet')) return;
        var o = document.createElement('div');
        o.id = 'svcSheet';
        o.className = 'svc-sheet-overlay';
        o.innerHTML =
            '<div class="svc-sheet">' +
            '<div class="svc-sheet-grab"></div>' +
            '<div class="svc-sheet-title" id="svcSheetTitle"></div>' +
            '<div class="svc-sheet-amount" id="svcSheetAmount"></div>' +
            '<div class="svc-sheet-rows" id="svcSheetRows"></div>' +
            '<div class="svc-sheet-warn" id="svcSheetWarn"></div>' +
            '<label class="svc-sheet-label">Transaction PIN</label>' +
            '<input type="password" id="svcSheetPin" class="svc-sheet-pin" inputmode="numeric" maxlength="6" autocomplete="off" placeholder="\u2022\u2022\u2022\u2022">' +
            '<button type="button" id="svcSheetConfirm" class="svc-sheet-confirm">Confirm and Pay</button>' +
            '<button type="button" id="svcSheetCancel" class="svc-sheet-cancel">Cancel</button>' +
            '</div>';
        document.body.appendChild(o);

        function close() { o.classList.remove('open'); }
        o.addEventListener('click', function (e) { if (e.target === o) close(); });
        $('svcSheetCancel').onclick = close;
        $('svcSheetConfirm').onclick = function () {
            var pin = $('svcSheetPin').value.trim();
            if (pin.length < 4) { showWarn('Enter your transaction PIN'); return; }
            var real = sheetForm.querySelector('input[name="pin"]');
            real.value = pin;
            this.disabled = true;
            this.textContent = 'Processing\u2026';
            sheetForm.submit();
        };
    }
    function showWarn(msg) {
        var w = $('svcSheetWarn');
        w.textContent = msg || '';
        w.style.display = msg ? 'block' : 'none';
    }
    function openSheet(form, info) {
        ensureSheet();
        sheetForm = form;
        $('svcSheetTitle').textContent = info.title;
        $('svcSheetAmount').textContent = info.amountText;
        var rows = $('svcSheetRows');
        rows.innerHTML = '';
        info.rows.forEach(function (r) {
            var d = el('div', 'svc-sheet-row');
            d.appendChild(el('span', '', r[0]));
            d.appendChild(el('strong', '', r[1]));
            rows.appendChild(d);
        });
        var confirmBtn = $('svcSheetConfirm');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm and Pay';
        $('svcSheetPin').value = '';
        showWarn('');
        var balEl = document.querySelector('.svc-balance-amount');
        var bal = balEl ? num(balEl.textContent) : NaN;
        if (!isNaN(info.price) && !isNaN(bal) && info.price > bal) {
            showWarn('Insufficient wallet balance. Fund your wallet to continue.');
            confirmBtn.disabled = true;
        }
        $('svcSheet').classList.add('open');
        setTimeout(function () { $('svcSheetPin').focus(); }, 250);
    }

    function validate(kind) {
        var money = function (n) { return '\u20a6' + Number(n).toLocaleString(); };
        if (kind === 'airtime') {
            var net = $('airtime-network-input').value;
            var ph = digitsOnly($('airtime-phone').value);
            var amt = parseFloat($('airtime-amount').value);
            if (!net) return toast('Pick a network');
            if (ph.length !== 11) return toast('Enter a valid 11-digit number');
            if (!(amt >= 50)) return toast('Minimum airtime is \u20a650');
            return { title: 'Airtime', amountText: money(amt), price: amt,
                rows: [['Network', NETNAMES[net] || net], ['Recipient', ph], ['Amount', money(amt)]] };
        }
        if (kind === 'data') {
            var dn = $('data-network-input').value;
            var dp = digitsOnly($('data-phone').value);
            var f = findPlan($('data-plan-input').value);
            if (!dn) return toast('Pick a network');
            if (dp.length !== 11) return toast('Enter a valid 11-digit number');
            if (!f) return toast('Pick a data plan');
            var s = splitLabel(f.p.label);
            return { title: 'Data bundle', amountText: '\u20a6' + f.p.price, price: num(f.p.price),
                rows: [['Network', NETNAMES[dn] || dn], ['Recipient', dp],
                       ['Plan', (s.type ? s.type + ' ' : '') + s.size], ['Validity', f.g.label]] };
        }
        var prov = $('cable-provider-input').value;
        var card = $('cable-card').value.trim();
        var pid = $('cable-plan-input').value;
        if (card.length < 6) return toast('Enter your smart card number');
        if (!pid) return toast('Pick a plan');
        var sel = document.querySelector('#cable-plan-grid .svc-plan-btn.selected');
        var lbl = sel && sel.querySelector('.svc-plan-size') ? sel.querySelector('.svc-plan-size').textContent : '';
        var pr = sel && sel.querySelector('.svc-plan-price') ? sel.querySelector('.svc-plan-price').textContent : '';
        return { title: 'Cable subscription', amountText: pr, price: num(pr),
            rows: [['Provider', prov.toUpperCase()], ['Smart card', card], ['Plan', lbl]] };
    }

    function hookForm(form, kind) {
        if (!form) return;
        var pin = form.querySelector('input[name="pin"]');
        if (pin) {
            pin.removeAttribute('required');
            pin.style.display = 'none';
            var lb = pin.previousElementSibling;
            if (lb && lb.classList.contains('svc-field-label')) lb.style.display = 'none';
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var info = validate(kind);
            if (info) openSheet(form, info);
        });
    }
    hookForm(document.querySelector('#airtime-tab form'), 'airtime');
    hookForm(document.querySelector('#data-tab form'), 'data');
    hookForm(document.querySelector('#cable-tab form'), 'cable');

    /* ---------- saved numbers: detect on tap + prefill most recent ---------- */
    document.querySelectorAll('#airtime-tab .svc-chip, #data-tab .svc-chip').forEach(function (c) {
        c.addEventListener('click', function () {
            var k = c.closest('#airtime-tab') ? 'airtime' : 'data';
            var ph = $(k + '-phone');
            if (ph) ph.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
    ['airtime', 'data'].forEach(function (k) {
        var ph = $(k + '-phone');
        var chip = document.querySelector('#' + k + '-tab .svc-chip');
        if (ph && chip && !ph.value) chip.click();
    });
})();
</script>
@endsection

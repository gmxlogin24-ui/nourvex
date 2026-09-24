@extends('layouts.app-authenticated')
@section('content')
<link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ time() }}">

<main class="max-w-2xl mx-auto p-6 svc-wrap {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
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

    <div class="svc-tabs">
        <button type="button" class="svc-tab-btn active" onclick="showTab('airtime', event)">Airtime</button>
        <button type="button" class="svc-tab-btn" onclick="showTab('data', event)">Data</button>
        <button type="button" class="svc-tab-btn" onclick="showTab('cable', event)">Cable</button>
    </div>

    {{-- AIRTIME --}}
    <div id="airtime-tab" class="stat-card p-6">
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
    <div id="data-tab" class="stat-card p-6" style="display:none;">
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
    <div id="cable-tab" class="stat-card p-6" style="display:none;">
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
    loadPlans();
    loadCablePlans();
    lucide.createIcons();
});
</script>
@endsection

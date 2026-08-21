@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1">Services</h1>
    <p class="text-muted text-sm mb-6">Buy airtime and data — straight from your wallet. Balance: ₦{{ number_format($balance, 2) }}</p>

    @unless($hasPin)
        <div class="alert alert-error mb-4">
            You need a transaction PIN before you can make purchases.
            <a href="{{ route('wallet.pin.edit') }}" class="underline font-semibold">Set one now</a>.
        </div>
    @endunless

    <datalist id="saved-phones">
        @foreach($savedPhones as $s)
            <option value="{{ $s->number }}">{{ $s->label ?? $s->provider }}</option>
        @endforeach
    </datalist>
    <datalist id="saved-cards">
        @foreach($savedCards as $s)
            <option value="{{ $s->number }}">{{ $s->label ?? $s->provider }}</option>
        @endforeach
    </datalist>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="tabs mb-4 flex gap-2">
        <button type="button" class="tab-btn active" onclick="showTab('airtime', event)">Airtime</button>
        <button type="button" class="tab-btn" onclick="showTab('data', event)">Data</button>
        <button type="button" class="tab-btn" onclick="showTab('cable', event)">Cable</button>
    </div>

    <div id="airtime-tab" class="stat-card p-6">
        <form method="POST" action="{{ route('services.airtime') }}">
            @csrf
            <label class="block text-sm mb-1">Network</label>
            <select name="network" required class="w-full mb-3 p-2 border rounded">
                @foreach($networks as $n)
                    <option value="{{ $n }}">{{ strtoupper($n) }}</option>
                @endforeach
            </select>

            <label class="block text-sm mb-1">Phone Number</label>
            <input type="text" name="phone" list="saved-phones" required maxlength="14" placeholder="080XXXXXXXX" class="w-full mb-3 p-2 border rounded">

            <label class="block text-sm mb-1">Amount (₦)</label>
            <input type="number" name="amount" min="50" required class="w-full mb-3 p-2 border rounded">

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="block text-sm mb-1">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="w-full mb-4 p-2 border rounded">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2" {{ $hasPin ? '' : 'disabled' }}>
                <i data-lucide="smartphone" class="w-4 h-4"></i> Buy Airtime
            </button>
        </form>
    </div>

    <div id="data-tab" class="stat-card p-6" style="display:none;">
        <form method="POST" action="{{ route('services.data') }}">
            @csrf
            <label class="block text-sm mb-1">Network</label>
            <select name="network" id="data-network" required class="w-full mb-3 p-2 border rounded">
                @foreach($networks as $n)
                    <option value="{{ $n }}">{{ strtoupper($n) }}</option>
                @endforeach
            </select>

            <label class="block text-sm mb-1">Phone Number</label>
            <input type="text" name="phone" list="saved-phones" required maxlength="14" placeholder="080XXXXXXXX" class="w-full mb-3 p-2 border rounded">

            <label class="block text-sm mb-1">Plan</label>
            <select name="plan_id" id="data-plan" required class="w-full mb-1 p-2 border rounded">
                <option value="">Loading plans…</option>
            </select>
            <input type="hidden" name="price" id="data-price">
            <p class="text-muted text-xs mb-3" id="data-price-label"></p>

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="block text-sm mb-1">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="w-full mb-4 p-2 border rounded">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2" {{ $hasPin ? '' : 'disabled' }}>
                <i data-lucide="wifi" class="w-4 h-4"></i> Buy Data
            </button>
        </form>
    </div>

    <div id="cable-tab" class="stat-card p-6" style="display:none;">
        <form method="POST" action="{{ route('services.cable') }}">
            @csrf
            <label class="block text-sm mb-1">Provider</label>
            <select name="provider" id="cable-provider" required class="w-full mb-3 p-2 border rounded">
                <option value="gotv">GOTV</option>
                <option value="dstv">DSTV</option>
                <option value="startime">StarTimes</option>
            </select>

            <label class="block text-sm mb-1">Smart Card / IUC Number</label>
            <input type="text" name="smart_card_number" list="saved-cards" required maxlength="20" placeholder="Smart card number" class="w-full mb-3 p-2 border rounded">

            <label class="block text-sm mb-1">Plan</label>
            <select name="plan_id" id="cable-plan" required class="w-full mb-3 p-2 border rounded">
                <option value="">Loading plans…</option>
            </select>

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="save_number" value="1"> Save this number for next time
            </label>

            <label class="block text-sm mb-1">Transaction PIN</label>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="w-full mb-4 p-2 border rounded">
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2" {{ $hasPin ? '' : 'disabled' }}>
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
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    evt.target.classList.add('active');
}

function loadPlans() {
    const network = document.getElementById('data-network').value;
    const select = document.getElementById('data-plan');
    select.innerHTML = '<option value="">Loading plans…</option>';

    fetch(`{{ route('services.data-plans') }}?network=${network}`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '';
            if (!data.groups || data.groups.length === 0) {
                select.innerHTML = '<option value="">No plans available right now</option>';
                return;
            }
            data.groups.forEach(group => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = group.label;
                group.plans.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.label} — ₦${p.price}`;
                    opt.dataset.price = p.price;
                    optgroup.appendChild(opt);
                });
                select.appendChild(optgroup);
            });
            updatePrice();
        })
        .catch(() => {
            select.innerHTML = '<option value="">Could not load plans — try again</option>';
        });
}

function updatePrice() {
    const select = document.getElementById('data-plan');
    const opt = select.options[select.selectedIndex];
    const price = opt ? opt.dataset.price : '';
    document.getElementById('data-price').value = price || '';
    document.getElementById('data-price-label').textContent = price ? `Price: ₦${price}` : '';
}

function loadCablePlans() {
    const provider = document.getElementById('cable-provider').value;
    const select = document.getElementById('cable-plan');
    select.innerHTML = '<option value="">Loading plans…</option>';

    fetch(`{{ route('services.cable-plans') }}?provider=${provider}`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '';
            if (!data.plans || data.plans.length === 0) {
                select.innerHTML = '<option value="">No plans available right now</option>';
                return;
            }
            data.plans.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.label} — ₦${p.price}`;
                select.appendChild(opt);
            });
        })
        .catch(() => {
            select.innerHTML = '<option value="">Could not load plans — try again</option>';
        });
}

document.getElementById('data-network').addEventListener('change', loadPlans);
document.getElementById('data-plan').addEventListener('change', updatePrice);
document.getElementById('cable-provider').addEventListener('change', loadCablePlans);
document.addEventListener('DOMContentLoaded', loadPlans);
document.addEventListener('DOMContentLoaded', loadCablePlans);
</script>
@endsection

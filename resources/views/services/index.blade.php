@extends('layouts.app')
@section('content')

<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1">Services</h1>
    <p class="text-muted text-sm mb-6">Buy airtime and data — straight from your wallet. Balance: ₦{{ number_format($balance, 2) }}</p>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="tabs mb-4 flex gap-2">
        <button type="button" class="tab-btn active" onclick="showTab('airtime', event)">Airtime</button>
        <button type="button" class="tab-btn" onclick="showTab('data', event)">Data</button>
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
            <input type="text" name="phone" required maxlength="14" placeholder="080XXXXXXXX" class="w-full mb-3 p-2 border rounded">

            <label class="block text-sm mb-1">Amount (₦)</label>
            <input type="number" name="amount" min="50" required class="w-full mb-4 p-2 border rounded">

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
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
            <input type="text" name="phone" required maxlength="14" placeholder="080XXXXXXXX" class="w-full mb-3 p-2 border rounded">

            <label class="block text-sm mb-1">Plan</label>
            <select name="plan_id" id="data-plan" required class="w-full mb-1 p-2 border rounded">
                <option value="">Loading plans…</option>
            </select>
            <input type="hidden" name="price" id="data-price">
            <p class="text-muted text-xs mb-4" id="data-price-label"></p>

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                <i data-lucide="wifi" class="w-4 h-4"></i> Buy Data
            </button>
        </form>
    </div>
</main>

<script>
function showTab(tab, evt) {
    document.getElementById('airtime-tab').style.display = tab === 'airtime' ? 'block' : 'none';
    document.getElementById('data-tab').style.display = tab === 'data' ? 'block' : 'none';
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
            if (!data.plans || data.plans.length === 0) {
                select.innerHTML = '<option value="">No plans available right now</option>';
                return;
            }
            data.plans.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.label} — ₦${p.price}`;
                opt.dataset.price = p.price;
                select.appendChild(opt);
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

document.getElementById('data-network').addEventListener('change', loadPlans);
document.getElementById('data-plan').addEventListener('change', updatePrice);
document.addEventListener('DOMContentLoaded', loadPlans);
</script>
@endsection

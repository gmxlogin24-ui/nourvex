@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Send Money</h1>

    @if(session('status'))
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-accent">{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-red-500">{{ $errors->first() }}</div>
    @endif

    <div class="glass-card p-6" id="detailsStep">
        <label class="text-sm font-medium mb-1 block">Recipient Email</label>
        <input type="text" id="recipientInput" placeholder="Recipient email" required class="input-field !mt-0 mb-3">

        <div id="resolveStatus" class="text-sm mb-3"></div>

        <label class="text-sm font-medium mb-1 block">Amount (₦)</label>
        <input type="number" id="amountInput" min="1" required class="input-field !mt-0 mb-3">

        <label class="text-sm font-medium mb-1 block">Note (optional)</label>
        <input type="text" id="noteInput" placeholder="Note (optional)" class="input-field !mt-0 mb-3">

        @unless($hasPin ?? false)
        <p class="text-sm text-muted mb-3">You need a transaction PIN before sending money. <a href="{{ route('wallet.pin.edit') }}" class="underline font-semibold">Set one now</a>.</p>
        @endunless

        <button type="button" id="continueBtn" class="btn-primary w-full justify-center" disabled {{ ($hasPin ?? false) ? '' : 'disabled' }}>Continue</button>
        <a href="{{ route('wallet.index') }}" class="text-muted text-sm mt-3 w-full text-center block">Cancel</a>
    </div>

    <div class="glass-card p-6" id="pinStep" style="display:none;">
        <button type="button" id="backBtn" class="text-muted text-sm mb-4">&larr; Back</button>
        <p class="text-sm text-muted mb-1">Sending</p>
        <p class="text-2xl font-semibold mb-1" id="pinAmountLabel"></p>
        <p class="text-sm text-muted mb-6" id="pinRecipientLabel"></p>

        <p class="text-center text-sm font-medium mb-4">Enter your transaction PIN</p>
        <div class="pin-dots" id="pinDots">
            <span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span>
            <span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span>
        </div>
        <p id="pinError" class="text-sm text-red-500 text-center mb-3" style="min-height:1.25rem;"></p>

        <div class="pin-keypad">
            <button type="button" class="pin-key" data-key="1">1</button>
            <button type="button" class="pin-key" data-key="2">2</button>
            <button type="button" class="pin-key" data-key="3">3</button>
            <button type="button" class="pin-key" data-key="4">4</button>
            <button type="button" class="pin-key" data-key="5">5</button>
            <button type="button" class="pin-key" data-key="6">6</button>
            <button type="button" class="pin-key" data-key="7">7</button>
            <button type="button" class="pin-key" data-key="8">8</button>
            <button type="button" class="pin-key" data-key="9">9</button>
            <div></div>
            <button type="button" class="pin-key" data-key="0">0</button>
            <button type="button" class="pin-key" id="pinBackspace">&#9003;</button>
        </div>
    </div>

    <form id="sendMoneyForm" method="POST" action="{{ route('wallet.send') }}" style="display:none;">
        @csrf
        <input type="hidden" name="recipient_identifier" id="hiddenRecipient">
        <input type="hidden" name="amount" id="hiddenAmount">
        <input type="hidden" name="note" id="hiddenNote">
        <input type="hidden" name="pin" id="hiddenPin">
    </form>
</main>

<style>
.pin-dots { display: flex; justify-content: center; gap: 14px; margin-bottom: 24px; }
.pin-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid var(--color-accent, #7c5cf0); background: transparent; transition: background 0.15s; }
.pin-dot.filled { background: var(--color-accent, #7c5cf0); }
.pin-keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 260px; margin: 0 auto; }
.pin-key { font-size: 1.25rem; font-weight: 600; padding: 18px 0; border-radius: 50%; background: var(--color-card, #f3f2fb); border: none; }
.pin-key:active { background: var(--color-accent, #7c5cf0); color: #fff; }
</style>

<script>
(function () {
    const recipientInput = document.getElementById('recipientInput');
    const amountInput = document.getElementById('amountInput');
    const noteInput = document.getElementById('noteInput');
    const resolveStatus = document.getElementById('resolveStatus');
    const continueBtn = document.getElementById('continueBtn');
    const hasPin = {{ ($hasPin ?? false) ? 'true' : 'false' }};
    let resolveTimer = null;
    let resolvedName = null;

    function tryResolve() {
        continueBtn.disabled = true;
        resolvedName = null;
        resolveStatus.textContent = '';

        const identifier = recipientInput.value.trim();
        if (!identifier) return;

        resolveStatus.textContent = 'Looking up recipient...';
        resolveStatus.className = 'text-sm mb-3 text-muted';

        fetch('{{ route("wallet.send.resolve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ recipient_identifier: identifier }),
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                resolveStatus.textContent = data.error || 'Could not find recipient.';
                resolveStatus.className = 'text-sm mb-3 text-red-500';
                return;
            }
            resolvedName = data.name;
            resolveStatus.textContent = 'Sending to: ' + data.name;
            resolveStatus.className = 'text-sm mb-3 text-accent';
            maybeEnableContinue();
        })
        .catch(() => {
            resolveStatus.textContent = 'Could not verify recipient. Check your connection and try again.';
            resolveStatus.className = 'text-sm mb-3 text-red-500';
        });
    }

    function maybeEnableContinue() {
        continueBtn.disabled = !(hasPin && resolvedName && amountInput.value && Number(amountInput.value) > 0);
    }

    function debounceResolve() {
        clearTimeout(resolveTimer);
        resolveTimer = setTimeout(tryResolve, 500);
    }

    recipientInput.addEventListener('input', debounceResolve);
    amountInput.addEventListener('input', maybeEnableContinue);

    // ---- Step transition ----
    const detailsStep = document.getElementById('detailsStep');
    const pinStep = document.getElementById('pinStep');
    const pinAmountLabel = document.getElementById('pinAmountLabel');
    const pinRecipientLabel = document.getElementById('pinRecipientLabel');
    const pinError = document.getElementById('pinError');
    const dots = document.querySelectorAll('.pin-dot');
    let pin = '';

    continueBtn.addEventListener('click', function () {
        pinAmountLabel.textContent = '₦' + Number(amountInput.value).toLocaleString();
        pinRecipientLabel.textContent = 'to ' + resolvedName;
        detailsStep.style.display = 'none';
        pinStep.style.display = 'block';
    });

    document.getElementById('backBtn').addEventListener('click', function () {
        pin = '';
        renderDots();
        pinError.textContent = '';
        pinStep.style.display = 'none';
        detailsStep.style.display = 'block';
    });

    function renderDots() {
        dots.forEach((d, i) => d.classList.toggle('filled', i < pin.length));
    }

    document.querySelectorAll('.pin-key').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (pin.length >= 6) return;
            pin += btn.dataset.key;
            renderDots();
            if (pin.length === 6) submitTransfer();
        });
    });

    document.getElementById('pinBackspace').addEventListener('click', function () {
        pin = pin.slice(0, -1);
        renderDots();
    });

    function submitTransfer() {
        if (!confirm('Send this money? Wallet transfers cannot be reversed.')) {
            pin = '';
            renderDots();
            return;
        }
        document.getElementById('hiddenRecipient').value = recipientInput.value.trim();
        document.getElementById('hiddenAmount').value = amountInput.value;
        document.getElementById('hiddenNote').value = noteInput.value;
        document.getElementById('hiddenPin').value = pin;
        document.getElementById('sendMoneyForm').submit();
    }
})();
</script>
@endsection

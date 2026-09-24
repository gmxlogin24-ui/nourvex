@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Payout Bank Account</h1>

    @if(session('status'))
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-accent">{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-card card-rounded p-4 mb-4 text-sm text-red-500">{{ $errors->first() }}</div>
    @endif

    @if($user->payout_account_number)
    <div class="glass-card p-4 mb-6">
        <p class="text-sm text-muted mb-1">Currently saved</p>
        <p class="font-semibold">{{ $user->payout_account_name }}</p>
        <p class="text-sm text-muted">{{ $user->payout_bank_name }} — {{ $user->payout_account_number }}</p>
    </div>
    <p class="text-muted text-xs mb-4">Saving a new account below will replace this one.</p>
    @endif

    <div class="glass-card p-6">
        <form id="bankAccountForm" method="POST" action="{{ route('wallet.bank-account.store') }}">
            @csrf
            <label class="text-sm font-medium mb-1 block">Bank</label>
            <select id="bankSelect" name="bank_code" required class="input-field !mt-0 mb-3">
                <option value="" disabled selected>Select your bank</option>
                @foreach($banks as $bank)
                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                @endforeach
            </select>
            <input type="hidden" name="bank_name" id="bankNameInput">

            <label class="text-sm font-medium mb-1 block">Account Number</label>
            <input type="text" id="accountNumberInput" name="account_number" maxlength="10" inputmode="numeric" placeholder="10-digit account number" required class="input-field !mt-0 mb-3">

            <div id="resolveStatus" class="text-sm mb-3"></div>

            <button type="submit" id="saveBtn" class="btn-primary w-full justify-center" disabled>Save Bank Account</button>
        </form>
    </div>
</main>

<script>
(function () {
    const bankSelect = document.getElementById('bankSelect');
    const bankNameInput = document.getElementById('bankNameInput');
    const accountNumberInput = document.getElementById('accountNumberInput');
    const resolveStatus = document.getElementById('resolveStatus');
    const saveBtn = document.getElementById('saveBtn');
    let resolveTimer = null;

    function tryResolve() {
        saveBtn.disabled = true;
        resolveStatus.textContent = '';

        const bankCode = bankSelect.value;
        const accountNumber = accountNumberInput.value.trim();

        if (!bankCode || accountNumber.length !== 10) {
            return;
        }

        resolveStatus.textContent = 'Verifying account...';
        resolveStatus.className = 'text-sm mb-3 text-muted';

        fetch('{{ route("wallet.bank-account.resolve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ bank_code: bankCode, account_number: accountNumber }),
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                resolveStatus.textContent = data.error || 'Could not verify account.';
                resolveStatus.className = 'text-sm mb-3 text-red-500';
                return;
            }
            resolveStatus.textContent = 'Account name: ' + data.account_name;
            resolveStatus.className = 'text-sm mb-3 text-accent';
            bankNameInput.value = bankSelect.options[bankSelect.selectedIndex].dataset.name || '';
            saveBtn.disabled = false;
        })
        .catch(() => {
            resolveStatus.textContent = 'Could not verify account. Check your connection and try again.';
            resolveStatus.className = 'text-sm mb-3 text-red-500';
        });
    }

    function debounceResolve() {
        clearTimeout(resolveTimer);
        resolveTimer = setTimeout(tryResolve, 500);
    }

    bankSelect.addEventListener('change', debounceResolve);
    accountNumberInput.addEventListener('input', debounceResolve);
})();
</script>
@endsection

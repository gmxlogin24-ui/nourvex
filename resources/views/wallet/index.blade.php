@extends('layouts.app-authenticated')
@section('title', 'Wallet')
@section('content')
@php
  $wlDark = (auth()->user()->theme_style ?? '') === 'qelvo-dark';
  $wlTxCount = count($transactions);
  $wlTier = auth()->user()->tier ?? 'free';
@endphp
<link rel="stylesheet" href="{{ asset('css/wallet-redesign.css') }}?v={{ time() }}">
<div class="wl {{ $wlDark ? 'wl-dark' : '' }}">
  <header class="wl-head">
    <h1>Wallet</h1>
    <p>Money in, money out, all in one place.</p>
  </header>

  @if(session('status'))<div class="wl-flash ok">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="wl-flash bad">{{ $errors->first() }}</div>@endif
  @if(!($isVerified ?? true))
    <div class="wl-notice">
      <b>Unverified wallet limit</b>
      <span>You can hold up to {{ \App\Support\Money::q($walletCapRemaining ?? 0) }} more before you need to verify your BVN. Earnings from gigs, sales and referrals aren't affected.</span>
    </div>
  @endif

  <div class="wl-grid">
    <div class="wl-col-a">
      <section class="wl-balance">
        <p class="wl-lbl">Total balance</p>
        <p class="wl-amt">{!! \App\Support\Money::coin($balance) !!}</p>
        <div class="wl-actions">
          <button type="button" class="wl-act primary" onclick="document.getElementById('withdrawModal').classList.add('open')"><i data-lucide="arrow-down-to-line"></i><span>Withdraw</span></button>
          <a href="{{ route('wallet.deposit') }}" class="wl-act"><i data-lucide="plus"></i><span>Top up</span></a>
          <a href="{{ route('wallet.send.form') }}" class="wl-act"><i data-lucide="send"></i><span>Send</span></a>
        </div>
      </section>

      <a href="{{ route('wallet.virtual-account') }}" class="wl-link">
        <span class="ib"><i data-lucide="landmark"></i></span>
        <span><b>Activate NAURVEX Pay</b><small>Fund your wallet by bank transfer, anytime</small></span>
        <i data-lucide="chevron-right" class="go"></i>
      </a>

      <a href="{{ route('wallet.bank-account.edit') }}" class="wl-link">
        <span class="ib"><i data-lucide="building-2"></i></span>
        <span><b>{{ $hasBankAccount ? 'Payout bank account' : 'Add payout bank account' }}</b><small>{{ $hasBankAccount ? 'Where your withdrawals are sent' : 'Required before you can withdraw' }}</small></span>
        <i data-lucide="chevron-right" class="go"></i>
      </a>

      @if(in_array($wlTier, ['tier2', 'tier3'], true))
      <a href="{{ route('money-tracker.index') }}" class="wl-link">
        <span class="ib"><i data-lucide="trending-up"></i></span>
        <span><b>Money Tracker</b><small>See your cash flow by month</small></span>
        <i data-lucide="chevron-right" class="go"></i>
      </a>
      @endif
    </div>

    <div class="wl-col-b">
      <section class="wl-card">
        <div class="wl-card-head"><h2>Transaction history</h2><span>{{ $wlTxCount }} {{ $wlTxCount === 1 ? 'entry' : 'entries' }}</span></div>
        @if($wlTxCount === 0)
          <p class="wl-empty">No transactions yet. Earnings from gigs, sales and referrals will show up here.</p>
        @else
          <div class="wl-list">
            @foreach($transactions as $tx)
            <a href="{{ route('wallet.transaction.show', $tx->id) }}" class="wl-row">
              <span style="min-width:0">
                <span class="wl-rt" style="display:block">{{ $tx->description }}</span>
                <span class="wl-rs">{{ $tx->created_at->diffForHumans() }}{{ $tx->reference ? ' · ' . $tx->reference : '' }}</span>
              </span>
              <span class="wl-amt-row {{ $tx->amount >= 0 ? 'in' : '' }}">{{ $tx->amount >= 0 ? '+' : '' }}{!! \App\Support\Money::forTx($tx->type, $tx->amount) !!}</span>
            </a>
            @endforeach
          </div>
        @endif
      </section>
    </div>
  </div>
</div>

<div id="topupModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Top Up Wallet</h3>
        <form method="POST" action="{{ route('paystack.initiate') }}">
            @csrf
            <input type="number" name="amount" min="100" max="1000000" step="0.01" inputmode="decimal" autocomplete="off" placeholder="Amount (₦)" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment</button>
        </form>
        <button onclick="document.getElementById('topupModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>

<div id="withdrawModal" class="modal-overlay">
    <div class="glass-card p-6 max-w-sm mx-auto mt-24">
        <h3 class="font-semibold mb-3">Withdraw Funds</h3>
        @unless($hasPin ?? false)
        <p class="text-sm text-muted mb-3">You need a transaction PIN before withdrawing. <a href="{{ route('wallet.pin.edit') }}" class="underline font-semibold">Set one now</a>.</p>
        @endunless
        @unless($hasBankAccount ?? false)
        <p class="text-sm text-muted mb-3">Add a payout bank account before withdrawing. <a href="{{ route('wallet.bank-account.edit') }}" class="underline font-semibold">Add one now</a>.</p>
        @endunless
        <form method="POST" action="{{ route('wallet.withdraw') }}" onsubmit="return confirm('Withdraw this amount to your bank account? This cannot be reversed once submitted.');">
            @csrf
            <input type="number" id="wdQ" min="{{ \App\Support\Money::toQ(100) }}" step="1" inputmode="decimal" autocomplete="off" placeholder="Amount (Q)" required class="input-field !mt-0 mb-1" oninput="wdSync(this)">
        <input type="hidden" name="amount" id="wdNaira" value="">
        <p id="wdPrev" class="text-sm text-muted mb-3">≈ ₦0.00 will be sent to your bank</p>
        <script>
        function wdSync(e){var r={{ \App\Support\Money::rate() }};var n=Math.round((parseFloat(e.value)||0)/r*100)/100;
          document.getElementById('wdNaira').value=n;
          document.getElementById('wdPrev').textContent='≈ ₦'+n.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})+' will be sent to your bank';}
        </script>
            <input type="password" name="pin" inputmode="numeric" maxlength="6" placeholder="Transaction PIN" required class="input-field !mt-0 mb-3">
            <button type="submit" class="btn-primary w-full justify-center" {{ (($hasPin ?? false) && ($hasBankAccount ?? false)) ? '' : 'disabled' }}>Confirm Withdraw</button>
        </form>
        <button onclick="document.getElementById('withdrawModal').classList.remove('open')" class="text-muted text-sm mt-3 w-full text-center">Cancel</button>
    </div>
</div>
@endsection

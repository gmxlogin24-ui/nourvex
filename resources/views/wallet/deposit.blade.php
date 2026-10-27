@extends('layouts.app-authenticated')
@section('title', 'Add money')
@section('content')
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('wallet.index') }}" class="sp-back" aria-label="Back to wallet"><i data-lucide="arrow-left"></i></a>
      <h1>Add money</h1>
    </div>
    <p class="sp-meta">Wallet balance</p>
    <p class="dp-bal">₦{{ number_format($balance, 2) }}</p>
  </section>

  @if(session('status'))<div class="sp-flash ok">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="sp-flash bad">{{ $errors->first() }}</div>@endif

  @if(!$hasVirtual && $walletCapRemaining !== null)
    <section class="sp-s">
      <p class="sp-note bad" style="margin:0">Unverified wallet limit: you can add up to ₦{{ number_format($walletCapRemaining) }} more until you activate NAURVEX Pay.</p>
    </section>
  @endif

  <section class="sp-s">
    <h2 class="sp-h">Bank transfer <span class="dp-tag">Recommended</span></h2>
    @if($hasVirtual)
      <div class="dp-acct">
        <div><small>{{ $user->virtual_account_bank }}</small><b>{{ $user->virtual_account_number }}</b></div>
        <button type="button" class="btn-secondary sp-btn" id="dpCopy" data-account="{{ $user->virtual_account_number }}">Copy</button>
      </div>
      <p class="sp-note">Send any amount from any bank app to this account. Your wallet is credited automatically, usually within a few minutes.</p>
    @else
      <p class="sp-note" style="margin:0 0 12px">Get your own NAURVEX Pay account number and fund your wallet by transfer any time, from any bank app.</p>
      <a href="{{ route('wallet.virtual-account') }}" class="btn-primary sp-btn"><i data-lucide="landmark"></i> Activate NAURVEX Pay</a>
    @endif
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Pay with card, USSD or bank</h2>
    <form method="POST" action="{{ route('paystack.initiate') }}" id="depForm">
      @csrf
      <label class="dp-lbl" for="dpAmt">Amount</label>
      <div class="dp-amt"><span>₦</span><input id="dpAmt" type="number" name="amount" min="100" max="1000000" step="0.01" inputmode="decimal" autocomplete="off" placeholder="0.00" required value="{{ old('amount') }}"></div>
      <div class="dp-chips">
        @foreach([1000, 5000, 10000, 20000, 50000] as $dpC)<button type="button" data-v="{{ $dpC }}">₦{{ number_format($dpC) }}</button>@endforeach
      </div>
      <p class="sp-note">Minimum ₦100, maximum ₦1,000,000. You pay securely on Paystack, then come straight back here to see the result.</p>
    </form>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">How it works</h2>
    <ol class="dp-steps">
      <li><b>Choose how to pay</b><span>Transfer to your NAURVEX Pay number, or enter an amount and pay on Paystack.</span></li>
      <li><b>We confirm it</b><span>Every payment is verified before your wallet is credited.</span></li>
      <li><b>See the result</b><span>You get a receipt screen with the amount, reference and your new balance.</span></li>
    </ol>
  </section>

  @if($recent->count())
    <section class="sp-s">
      <h2 class="sp-h">Recent deposits</h2>
      @foreach($recent as $dpR)
        <a href="{{ route('wallet.transaction.show', $dpR->id) }}" class="sp-row" style="text-decoration:none">
          <span>{{ $dpR->created_at->format('M j, Y') }} · {{ $dpR->created_at->diffForHumans() }}</span>
          <b>+₦{{ number_format($dpR->amount, 2) }}</b>
        </a>
      @endforeach
    </section>
  @endif

  <div class="pd-bar">
    <button type="submit" form="depForm" class="btn-primary pd-buy"><i data-lucide="lock"></i> Continue to payment</button>
  </div>
</main>
<script>
(function () {
  var inp = document.getElementById('dpAmt');
  document.querySelectorAll('.dp-chips button').forEach(function (b) {
    b.addEventListener('click', function () { inp.value = b.dataset.v; inp.focus(); });
  });
  var c = document.getElementById('dpCopy');
  if (c && navigator.clipboard) {
    c.addEventListener('click', function () {
      navigator.clipboard.writeText(c.dataset.account).then(function () {
        var t = c.textContent;
        c.textContent = 'Copied';
        setTimeout(function () { c.textContent = t; }, 1500);
      }).catch(function () {});
    });
  }
})();
</script>
@include('marketplace.partials.shop-boot')
@endsection

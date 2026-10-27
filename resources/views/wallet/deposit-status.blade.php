@extends('layouts.app-authenticated')
@section('title', 'Payment')
@section('content')
@php
  $dsOk = $topup->status === 'successful';
  $dsFail = $topup->status === 'failed';
@endphp
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0 dp-res {{ $dsOk ? 'ok' : ($dsFail ? 'bad' : 'wait') }}">
    <span class="dp-ic"><i data-lucide="{{ $dsOk ? 'check' : ($dsFail ? 'x' : 'clock') }}"></i></span>
    <h1>{{ $dsOk ? 'Deposit successful' : ($dsFail ? 'Payment failed' : 'Confirming your payment') }}</h1>
    <p class="dp-big">₦{{ number_format($topup->amount, 2) }}</p>
    <p class="sp-note">
      @if($dsOk)Your wallet has been credited.
      @elseif($dsFail)We didn't receive this payment, so you were not charged. If money left your account, it will be returned by your bank.
      @else We haven't had confirmation from Paystack yet. If you were charged, your wallet is credited automatically within a few minutes.
      @endif
    </p>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Details</h2>
    <div class="sp-row"><span>Status</span><b>{{ $dsOk ? 'Successful' : ($dsFail ? 'Failed' : 'Pending') }}</b></div>
    <div class="sp-row"><span>Reference</span><b>{{ $topup->tx_ref }}</b></div>
    <div class="sp-row"><span>Date</span><b>{{ $topup->created_at->format('M j, Y · g:ia') }}</b></div>
    <div class="sp-row t"><span>Wallet balance</span><span>₦{{ number_format($balance, 2) }}</span></div>
  </section>

  <section class="sp-s dp-act">
    @if($dsOk || $dsFail)
      <a href="{{ route('dashboard') }}" class="btn-primary sp-btn">{{ $dsOk ? 'Done' : 'Back home' }}</a>
    @else
      <a href="{{ route('paystack.callback', ['reference' => $topup->tx_ref]) }}" class="btn-primary sp-btn">Check again</a>
    @endif
    @if($tx)<a href="{{ route('wallet.transaction.show', $tx->id) }}" class="btn-secondary sp-btn">View receipt</a>@endif
    <a href="{{ route('wallet.deposit') }}" class="btn-secondary sp-btn">{{ $dsFail ? 'Try again' : 'Add more' }}</a>
  </section>
</main>
@include('marketplace.partials.shop-boot')
@endsection

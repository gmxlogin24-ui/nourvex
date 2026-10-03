@extends('layouts.app-authenticated')
@section('title', 'Dashboard')
@section('content')
@php
    $u = auth()->user();
    $bal = (float) ($u->wallet_balance ?? 0);
    $acct = $u->virtual_account_number ?? null;
    $bank = $u->virtual_account_bank ?? null;
    $walletUrl = Route::has('wallet.index') ? route('wallet.index') : '#';
@endphp
<style>
    .hbp-page{max-width:560px;margin:0 auto;padding:84px 14px 130px}
    .hbp-page .hb-stage{height:312px}
</style>
<div class="hbp-page">
  <section class="hb-wrap" id="hbWrap">
    <div class="hb-stage" id="hbStage">
      <div class="hb-tilt" id="hbTilt"><div class="hb-flip" id="hbFlip">

        <div class="hb-face hb-front">
          <div class="hb-skin"><div class="hb-aurora a1"></div><div class="hb-aurora a2"></div><div class="hb-grid"></div><div class="hb-foil"></div></div>
          <div class="hb-body">
            <div class="hb-top"><div class="hb-chip"></div><i class="bi bi-wifi hb-nfc"></i></div>
            <div class="hb-mid">
              <div class="hb-label">Total Balance</div>
              <div class="dqp-hero-amount">₦<span class="hb-count" data-value="{{ $bal }}">{{ number_format($bal, 2) }}</span></div>
              @if($acct)<div class="dqp-hero-masked">•••• •••• {{ substr($acct, -4) }}</div>@endif
            </div>
            <div class="hb-bottom">
              @if($acct)
              <div class="dqp-hero-holder-row">
                <span class="dqp-hero-holder">Card holder<b>{{ $u->name }}</b></span>
                <span class="dqp-hero-holder">Bank<b>{{ $bank }}</b></span>
              </div>
              @endif
              <div class="dqp-hero-actions">
                <a href="{{ $walletUrl }}" class="dqp-btn dqp-btn-primary"><i class="bi bi-arrow-up-right"></i> Send</a>
                <a href="{{ $walletUrl }}" class="dqp-btn dqp-btn-ghost">Withdraw</a>
                <a href="{{ $walletUrl }}" class="dqp-btn dqp-btn-icon"><i class="bi bi-three-dots"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="hb-face hb-back">
          <div class="hb-skin"><div class="hb-aurora a1"></div><div class="hb-aurora a2"></div><div class="hb-grid"></div><div class="hb-foil"></div></div>
          <div class="hb-body">
            <div class="hb-top"><div class="hb-label">Account details</div><i class="bi bi-bank hb-ico"></i></div>
            <div class="hb-mid">
              @if($acct)
                <div class="hb-label">Card holder</div><div class="hb-line">{{ $u->name }}</div>
                <div class="hb-label">Bank</div><div class="hb-line">{{ $bank }}</div>
                <div class="hb-label">Account</div><div class="hb-line hb-mono">•••• •••• {{ substr($acct, -4) }}</div>
              @else
                <div class="hb-line">No bank account linked yet</div>
              @endif
            </div>
            <div class="hb-hint">Tap to flip back</div>
          </div>
        </div>

      </div></div>
    </div>
    <div class="hb-dots"><span class="hb-dot on" id="hbD0"></span><span class="hb-dot" id="hbD1"></span></div>
  </section>
</div>
<script src="{{ asset('js/holo-page.js') }}?v={{ time() }}"></script>
@endsection

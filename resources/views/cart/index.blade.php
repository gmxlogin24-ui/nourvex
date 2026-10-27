@extends('layouts.world-shell')
@section('world', 'market')
@section('title', 'Cart')
@section('content')
@php
  $crCount = $listings->count();
  $crWallet = (float) (auth()->user()->wallet_balance ?? 0);
  $crShort = $crCount && $crWallet < $total;
@endphp
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('marketplace.index') }}" class="sp-back" aria-label="Back to market"><i data-lucide="arrow-left"></i></a>
      <h1>Cart</h1>
      <span>{{ $crCount }} {{ $crCount === 1 ? 'item' : 'items' }}</span>
    </div>
    @include('marketplace.partials.shop-tabs', ['active' => 'cart'])
  </section>

  @if(session('status'))<div class="sp-flash ok">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="sp-flash bad">{{ $errors->first() }}</div>@endif

  @if($crCount)
    @foreach($listings as $listing)
      @php $crImg = $listing->image_url ?: optional($listing->images->first())->image_url; @endphp
      <section class="sp-s sp-item">
        <a href="{{ route('marketplace.show', $listing->id) }}" class="sp-th">@if($crImg)<img src="{{ $crImg }}" alt="{{ $listing->title }}" loading="lazy">@else<i data-lucide="image"></i>@endif</a>
        <div class="sp-in">
          <a href="{{ route('marketplace.show', $listing->id) }}" class="sp-name">{{ $listing->title }}</a>
          @if($listing->store_name)<p class="sp-store">{{ $listing->store_name }}</p>@endif
          <p class="sp-meta">{{ $listing->location }} · {{ $listing->delivery_method === 'ship' ? 'Seller ships' : 'Meetup' }}</p>
          <p class="sp-price">₦{{ number_format($listing->amount) }}</p>
        </div>
        <form method="POST" action="{{ route('cart.remove', $listing->id) }}">
          @csrf
          <button type="submit" class="sp-rm" aria-label="Remove {{ $listing->title }}"><i data-lucide="x"></i></button>
        </form>
      </section>
    @endforeach

    <section class="sp-s">
      <h2 class="sp-h">Order summary</h2>
      <div class="sp-row"><span>Items ({{ $crCount }})</span><b>₦{{ number_format($total) }}</b></div>
      <div class="sp-row"><span>Wallet balance</span><b class="{{ $crShort ? 'bad' : '' }}">₦{{ number_format($crWallet) }}</b></div>
      <div class="sp-row t"><span>Total</span><span>₦{{ number_format($total) }}</span></div>
      @if($crShort)<p class="sp-note bad">Top up ₦{{ number_format($total - $crWallet) }} or remove items to check out.</p>@endif
      <p class="sp-note">Paid from your wallet and held in escrow until you confirm delivery of each item.</p>
    </section>

    <div class="pd-bar">
      @if($crShort)
        <a href="{{ Route::has('wallet.index') ? route('wallet.index') : '#' }}" class="btn-primary pd-buy"><i data-lucide="wallet"></i> Top up wallet to check out</a>
      @else
        <form method="POST" action="{{ route('cart.checkout') }}">
          @csrf
          <button type="submit" class="btn-primary pd-buy"><i data-lucide="lock"></i> Checkout · ₦{{ number_format($total) }}</button>
        </form>
      @endif
    </div>
  @else
    <section class="sp-s sp-empty">
      <i data-lucide="shopping-bag"></i>
      <h2>Your cart is empty</h2>
      <p>Tap the heart on any item you like and it will wait here.</p>
      <a href="{{ route('marketplace.index') }}" class="btn-primary sp-btn">Browse Campus Market</a>
    </section>
  @endif

  @if(isset($saved) && $saved->count())
    <section class="sp-s"><h2 class="sp-h">Saved for later</h2></section>
    @foreach($saved as $sv)
      @php $svImg = $sv->image_url ?: optional($sv->images->first())->image_url; @endphp
      <section class="sp-s sp-item">
        <a href="{{ route('marketplace.show', $sv->id) }}" class="sp-th">@if($svImg)<img src="{{ $svImg }}" alt="{{ $sv->title }}" loading="lazy">@else<i data-lucide="image"></i>@endif</a>
        <div class="sp-in">
          <a href="{{ route('marketplace.show', $sv->id) }}" class="sp-name">{{ $sv->title }}</a>
          <p class="sp-meta">{{ $sv->location }}</p>
          <p class="sp-price">₦{{ number_format($sv->amount) }}</p>
        </div>
        <div style="display:flex;gap:6px;align-items:center">
          <form method="POST" action="{{ route('cart.add', $sv->id) }}">@csrf<button type="submit" class="sp-rm" aria-label="Move to cart"><i data-lucide="shopping-cart"></i></button></form>
          <form method="POST" action="{{ route('cart.remove', $sv->id) }}">@csrf<button type="submit" class="sp-rm" aria-label="Remove"><i data-lucide="x"></i></button></form>
        </div>
      </section>
    @endforeach
  @endif
</main>
@include('marketplace.partials.shop-boot')
@endsection

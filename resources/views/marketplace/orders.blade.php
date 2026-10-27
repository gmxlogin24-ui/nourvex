@extends('layouts.world-shell')
@section('world', 'market')
@section('title', 'Orders')
@section('content')
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('marketplace.index') }}" class="sp-back" aria-label="Back to market"><i data-lucide="arrow-left"></i></a>
      <h1>Orders</h1>
      <span>{{ count($orders) }} {{ count($orders) === 1 ? 'order' : 'orders' }}</span>
    </div>
    @include('marketplace.partials.shop-tabs', ['active' => 'orders'])
  </section>

  @if(session('status'))<div class="sp-flash ok">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="sp-flash bad">{{ $errors->first() }}</div>@endif

  @forelse($orders as $order)
    @php
      $orL = $order->listing;
      $orImg = $orL ? ($orL->image_url ?: optional($orL->images->first())->image_url) : null;
    @endphp
    <section class="sp-s sp-item">
      <a href="{{ $orL ? route('marketplace.show', $orL->id) : '#' }}" class="sp-th">@if($orImg)<img src="{{ $orImg }}" alt="" loading="lazy">@else<i data-lucide="package"></i>@endif</a>
      <div class="sp-in">
        <a href="{{ $orL ? route('marketplace.show', $orL->id) : '#' }}" class="sp-name">{{ $orL->title ?? 'Listing removed' }}</a>
        <p class="sp-price">₦{{ number_format($order->amount) }}</p>
        <p class="sp-meta">{{ $order->created_at->format('M j, Y') }} · {{ $order->created_at->diffForHumans() }}</p>
        @if($order->status === 'paid')
          <span class="sp-chip">Paid · held in escrow</span>
          <div class="sp-act">
            <form method="POST" action="{{ route('marketplace.order.confirm', $order->id) }}" onsubmit="return confirm('Confirm you received this item? This releases payment to the seller.');">
              @csrf
              <button type="submit" class="btn-primary sp-btn">Confirm receipt</button>
            </form>
          </div>
        @else
          <span class="sp-chip ok">✓ Completed</span>
        @endif
      </div>
    </section>
  @empty
    <section class="sp-s sp-empty">
      <i data-lucide="package"></i>
      <h2>No orders yet</h2>
      <p>Items you buy will show up here until you confirm you received them.</p>
      <a href="{{ route('marketplace.index') }}" class="btn-primary sp-btn">Browse Campus Market</a>
    </section>
  @endforelse
</main>
@include('marketplace.partials.shop-boot')
@endsection

@extends('layouts.world-shell')
@section('world', 'market')
@section('title', 'Messages')
@section('content')
@php
  $show = request('show');
  $list = $threads->filter(fn ($t) => $show === 'selling' ? $t['selling'] : ($show === 'buying' ? !$t['selling'] : true));
@endphp
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<main class="sp">
  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('marketplace.index') }}" class="sp-back" aria-label="Back to market"><i data-lucide="arrow-left"></i></a>
      <h1>Messages</h1>
      <span>{{ $list->count() }} {{ $list->count() === 1 ? 'chat' : 'chats' }}</span>
    </div>
    @include('marketplace.partials.shop-tabs', ['active' => 'inbox'])
    <div class="wp-chips" style="margin-top:10px">
      <a href="{{ route('marketplace.inbox') }}" class="wp-chip {{ !$show ? 'on' : '' }}">All</a>
      <a href="{{ route('marketplace.inbox', ['show' => 'selling']) }}" class="wp-chip {{ $show === 'selling' ? 'on' : '' }}">Selling</a>
      <a href="{{ route('marketplace.inbox', ['show' => 'buying']) }}" class="wp-chip {{ $show === 'buying' ? 'on' : '' }}">Buying</a>
    </div>
  </section>

  @forelse($list as $t)
    @php $l = $t['listing']; $img = $l->image_url ?: optional($l->images->first())->image_url; @endphp
    <section class="sp-s sp-item">
      <a href="{{ route('marketplace.show', $l->id) }}" class="sp-th">@if($img)<img src="{{ $img }}" alt="{{ $l->title }}" loading="lazy">@else<i data-lucide="image"></i>@endif</a>
      <div class="sp-in">
        <a href="{{ route('marketplace.show', $l->id) }}" class="sp-name">{{ $t['other'] }}</a>
        <p class="sp-store">{{ $t['selling'] ? 'Asking about' : 'You asked about' }}: {{ \Illuminate\Support\Str::limit($l->title, 34) }}</p>
        <p class="sp-meta">{{ $t['mine'] ? 'You: ' : '' }}{{ \Illuminate\Support\Str::limit($t['text'], 60) }}</p>
        <p class="sp-meta">{{ $t['at']->diffForHumans() }}</p>
      </div>
    </section>
  @empty
    <section class="sp-s sp-empty">
      <i data-lucide="message-circle"></i>
      <h2>No messages yet</h2>
      <p>When buyers ask about your listings, or you chat with a seller, it shows up here.</p>
      <a href="{{ route('marketplace.index') }}" class="btn-primary sp-btn">Browse Campus Market</a>
    </section>
  @endforelse
</main>
@endsection

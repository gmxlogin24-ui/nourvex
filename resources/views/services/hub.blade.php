@extends('layouts.app-authenticated')
@section('title', 'Services')
@section('content')
<link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
<link rel="stylesheet" href="{{ asset('css/services-redesign.css') }}?v={{ filemtime(public_path('css/services-redesign.css')) }}">
@php
  $hub = [
    ['Airtime', 'Top up any network', 'smartphone', 'linear-gradient(145deg,#38BDF8,#2563EB)', route('services.show', 'airtime')],
    ['Data', 'Bundles for every network', 'wifi', 'linear-gradient(145deg,#34D399,#059669)', route('services.show', 'data')],
    ['Cable TV', 'GOTV, DSTV, StarTimes', 'tv', 'linear-gradient(145deg,#FB923C,#EA580C)', route('services.show', 'cable')],
    ['Electricity', 'Coming soon', 'plug-zap', 'linear-gradient(145deg,#FACC15,#CA8A04)', null],
    ['Betting', 'Coming soon', 'trophy', 'linear-gradient(145deg,#F472B6,#DB2777)', null],
    ['Education', 'Coming soon', 'graduation-cap', 'linear-gradient(145deg,#A78BFA,#7C3AED)', null],
  ];
@endphp
<main class="svc-wrap">
  <div class="svcx-head">
    <a href="{{ route('dashboard') }}" class="svcx-back"><i data-lucide="arrow-left"></i> Home</a>
    <div class="svcx-title"><div><h1>Services</h1><p>Pay bills straight from your wallet</p></div></div>
  </div>
  <div class="svc-balance-strip">
    <div>
      <div class="svc-balance-label">Wallet Balance</div>
      <div class="svc-balance-amount">₦{{ number_format($balance, 2) }}</div>
    </div>
    <i data-lucide="wallet" class="w-7 h-7" style="opacity:0.6;"></i>
  </div>
  <div class="svcx-hub">
    @foreach($hub as $h)
      @if($h[4])
      <a href="{{ $h[4] }}" class="svcx-tile">
        <span class="svcx-ico" style="--svcx:{{ $h[3] }}"><i data-lucide="{{ $h[2] }}"></i></span>
        <b>{{ $h[0] }}</b><small>{{ $h[1] }}</small>
      </a>
      @else
      <div class="svcx-tile soon">
        <span class="svcx-ico" style="--svcx:{{ $h[3] }}"><i data-lucide="{{ $h[2] }}"></i></span>
        <b>{{ $h[0] }}</b><small>{{ $h[1] }}</small>
      </div>
      @endif
    @endforeach
  </div>
</main>
<script>document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });</script>
@endsection

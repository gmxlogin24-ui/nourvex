@php
  $hiRows = collect();
  try {
    foreach (['App\\Models\\Investment', 'App\\Models\\UserInvestment'] as $hiM) {
      if (class_exists($hiM)) { $hiRows = $hiM::query()->latest()->take(4)->get(); break; }
    }
  } catch (\Throwable $e) { $hiRows = collect(); }
  $hiUrl = \Illuminate\Support\Facades\Route::has('investment.index') ? route('investment.index') : '#';
@endphp
<link rel="stylesheet" href="{{ asset('css/hot-invest.css') }}?v={{ time() }}">
<section class="hi-wrap">
  <div class="hi-head"><b>Hot investments</b><a href="{{ $hiUrl }}">See all</a></div>
  <div class="hi-grid">
    @for($hiN = 0; $hiN < 4; $hiN++)
      @php $hi = $hiRows[$hiN] ?? null; @endphp
      <a href="{{ $hiUrl }}" class="hi-card dg-glass">
        <span class="hi-fire"><i data-lucide="flame"></i></span>
        <b>{{ $hi ? ($hi->name ?? $hi->title ?? $hi->plan_name ?? 'Investment') : 'Hot pick' }}</b>
        <small>@if($hi && isset($hi->amount))₦{{ number_format((float) $hi->amount) }}@else Tap to invest @endif</small>
      </a>
    @endfor
  </div>
</section>

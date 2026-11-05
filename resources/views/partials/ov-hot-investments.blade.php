@php
  $hiRows = collect();
  try {
    $hiRows = \App\Models\InvestmentProject::where('status', 'open')
      ->where('closes_at', '>', now())
      ->orderByDesc('raised_q')
      ->take(4)
      ->get();
  } catch (\Throwable $e) { $hiRows = collect(); }
  $hiUrl = \Illuminate\Support\Facades\Route::has('investment.index') ? route('investment.index') : '#';
@endphp
<link rel="stylesheet" href="{{ asset('css/hot-invest.css') }}?v={{ time() }}">
<section class="hi-wrap">
  <div class="hi-head"><b>Hot investments</b><a href="{{ $hiUrl }}">See all</a></div>
  <div class="hi-grid">
    @for($hiN = 0; $hiN < 4; $hiN++)
      @php
        $hi = $hiRows[$hiN] ?? null;
        $hiLink = ($hi && \Illuminate\Support\Facades\Route::has('investment.show')) ? route('investment.show', $hi) : $hiUrl;
      @endphp
      <a href="{{ $hiLink }}" class="hi-card dg-glass">
        <span class="hi-fire"><i data-lucide="flame"></i></span>
        <b>{{ $hi ? \Illuminate\Support\Str::limit($hi->title, 28) : 'Hot pick' }}</b>
        <small>@if($hi){{ $hi->progressPercent() }}% funded · ₦{{ number_format($hi->raised_q) }}@else Tap to invest @endif</small>
      </a>
    @endfor
  </div>
</section>

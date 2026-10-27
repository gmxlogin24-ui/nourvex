@extends('layouts.app-authenticated')
@section('title', $project->title)
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">

<div class="inv">
  <a href="{{ route('investment.index') }}" class="inv-muted">← Investments</a>

  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif

  <header style="margin-top:10px">
    <span class="inv-tag">{{ config('investment.types')[$project->type] ?? $project->type }}</span>
    <h1 style="margin-top:8px">{{ $project->title }}</h1>
    <p class="inv-sub">
      Status: {{ str_replace('_', ' ', ucfirst($project->status)) }}
      @if($project->closes_at) · Closes {{ $project->closes_at->format('M j, Y') }} @endif
    </p>
  </header>

  <section class="inv-hero">
    <p class="inv-lbl">Raised</p>
    <p class="inv-amt">{{ number_format($project->raised_q) }} Q</p>
    <p style="color:rgba(255,255,255,.7);font-size:12px;margin:0">of {{ number_format($project->goal_q) }} Q goal · {{ \App\Services\InvestmentService::naira($project->raised_q) }} raised</p>
    <div class="inv-bar" style="margin-top:14px;background:rgba(255,255,255,.12)"><span style="width: {{ $project->progressPercent() }}%"></span></div>
    <div class="inv-stats">
      <div><small>Released to owner</small><b>{{ number_format($project->released_q) }} Q</b></div>
      <div><small>Returns paid</small><b>{{ number_format($project->returns_distributed_q) }} Q</b></div>
    </div>
  </section>

  <section class="inv-glass inv-section">
    <h2>About this project</h2>
    <p class="inv-muted" style="white-space:pre-line;color:var(--ink);font-size:13px">{{ $project->description }}</p>
    <p class="inv-muted">Per-investor limit: {{ number_format($project->max_invest_q) }} Q · Minimum: {{ number_format($project->min_invest_q) }} Q</p>
  </section>

  @if($canInvest)
    <section class="inv-glass inv-section">
      <h2>Invest</h2>
      <p class="inv-muted">Your balance: {{ number_format($qBalance) }} Q · You can still invest up to {{ number_format($maxAllowed) }} Q here.</p>
      <form method="POST" action="{{ route('investment.invest', $project) }}">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idemKey }}">
        <input class="inv-input" type="number" name="amount_q" min="{{ $project->min_invest_q }}" max="{{ $maxAllowed }}" step="1" required value="{{ old('amount_q', $project->min_invest_q) }}">
        @error('amount_q')<div class="inv-err">{{ $message }}</div>@enderror
        <button class="inv-btn" type="submit">Invest {{ number_format(old('amount_q', $project->min_invest_q)) }} Q</button>
      </form>
    </section>
  @elseif($project->status === 'open' && ! $isOwner && $maxAllowed <= 0)
    <p class="inv-muted inv-section">You have reached your limit for this project.</p>
  @endif

  @if($mine->count())
    <section class="inv-glass inv-section">
      <h2>Your positions here</h2>
      @foreach($mine as $inv)
        <div class="inv-row">
          <span>{{ $inv->created_at->format('M j, Y') }}</span>
          <span>{{ number_format($inv->amount_q) }} Q <span class="inv-status {{ $inv->status === 'active' ? '' : 'off' }}">{{ ucfirst($inv->status) }}</span></span>
        </div>
      @endforeach
    </section>
  @endif
</div>
@endsection

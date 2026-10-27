@extends('layouts.app-authenticated')
@section('title', 'Investment')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">

<div class="inv inv-index">
  <header>
    <p class="inv-kicker">Invest</p>
    <h1>Investments</h1>
    <p class="inv-sub">Put your Q Coins to work in verified projects.</p>
  </header>

  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif

  <section class="inv-hero">
    <p class="inv-lbl">Your Q Coins</p>
    <p class="inv-amt">{{ number_format($qBalance) }} Q</p>
    <p class="inv-sub2" style="color:rgba(255,255,255,.7)">≈ {{ \App\Services\InvestmentService::naira($qBalance) }} · {{ config('investment.qcoin_per_naira') }} Q = ₦1</p>
    <div class="inv-stats">
      <div><small>Invested now</small><b>{{ number_format($totalInvested) }} Q</b></div>
      <div><small>Active positions</small><b>{{ $myInvestments->where('status', 'active')->count() }}</b></div>
    </div>
  </section>

  @if(! $isVerified)
    <section class="inv-glass inv-section">
      <h2>Get verified to start a project</h2>
      @if($verification && $verification->status === 'pending')
        <p class="inv-muted">Your request is with the admin team.</p>
      @else
        @if($verification && $verification->status === 'rejected')
          <p class="inv-muted">Your last request was not approved. You can send more details.</p>
        @endif
        <form method="POST" action="{{ route('investment.verification') }}">
          @csrf
          <textarea class="inv-input" name="details" rows="4" minlength="20" maxlength="2000" required placeholder="Who you are, what you plan to build, and how you will use the funds.">{{ old('details') }}</textarea>
          @error('details')<div class="inv-err">{{ $message }}</div>@enderror
          <button class="inv-btn" type="submit">Send for review</button>
        </form>
      @endif
    </section>
  @else
    <section class="inv-section">
      <a href="{{ route('investment.create') }}" class="inv-glass">
        <h2>Start a project</h2>
        <p class="inv-muted">List a business or land pool for investors. An admin reviews it before it opens.</p>
      </a>
    </section>
  @endif

  <section class="inv-section">
    <h2>Open projects</h2>
    @forelse($openProjects as $p)
      <a href="{{ route('investment.show', $p) }}" class="inv-glass">
        <span class="inv-tag">{{ config('investment.types')[$p->type] ?? $p->type }}</span>
        <b>{{ $p->title }}</b>
        <div class="inv-bar"><span style="width: {{ $p->progressPercent() }}%"></span></div>
        <small>{{ number_format($p->raised_q) }} of {{ number_format($p->goal_q) }} Q · {{ $p->progressPercent() }}%</small>
      </a>
    @empty
      <p class="inv-muted">No open projects right now.</p>
    @endforelse
  </section>

  <section class="inv-section">
    <h2>My investments</h2>
    <div class="inv-glass">
      @forelse($myInvestments as $inv)
        <div class="inv-row">
          <a href="{{ route('investment.show', $inv->project) }}"><b>{{ $inv->project->title }}</b></a>
          <span>{{ number_format($inv->amount_q) }} Q <span class="inv-status {{ $inv->status === 'active' ? '' : 'off' }}">{{ ucfirst($inv->status) }}</span></span>
        </div>
      @empty
        <p class="inv-muted">You have not invested yet.</p>
      @endforelse
    </div>
  </section>

  @if($myProjects->count())
    <section class="inv-section">
      <h2>My projects</h2>
      <div class="inv-glass">
        @foreach($myProjects as $p)
          <div class="inv-row">
            <a href="{{ route('investment.show', $p) }}"><b>{{ $p->title }}</b></a>
            <span class="inv-status {{ $p->status === 'open' || $p->status === 'funded' ? '' : 'off' }}">{{ str_replace('_', ' ', ucfirst($p->status)) }}</span>
          </div>
        @endforeach
      </div>
    </section>
  @endif
</div>
@endsection

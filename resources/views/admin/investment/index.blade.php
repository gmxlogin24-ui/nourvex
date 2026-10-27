@extends('layouts.app-authenticated')
@section('title', 'Investment admin')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">

<div class="inv">
  <header>
    <p class="inv-kicker">Admin</p>
    <h1>Investments</h1>
  </header>

  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif

  <section class="inv-section">
    <h2>Verification requests</h2>
    @forelse($pendingVerifications as $v)
      <div class="inv-glass">
        <b>{{ $v->user->name }}</b> <small>{{ $v->user->email }}</small>
        <p class="inv-muted" style="white-space:pre-line;color:var(--ink)">{{ $v->details }}</p>
        <form method="POST" action="{{ route('admin.investment.verifications.review', $v) }}">
          @csrf
          <button class="inv-btn small" name="decision" value="approve">Approve</button>
          <button class="inv-btn small ghost" name="decision" value="reject">Reject</button>
        </form>
      </div>
    @empty
      <p class="inv-muted">No pending requests.</p>
    @endforelse
  </section>

  <section class="inv-section">
    <h2>Projects waiting for review</h2>
    @forelse($pendingProjects as $p)
      <div class="inv-glass">
        <b>{{ $p->title }}</b> <small>{{ $p->owner->name }} · {{ number_format($p->goal_q) }} Q goal</small>
        <form method="POST" action="{{ route('admin.investment.projects.approve', $p) }}">
          @csrf
          <button class="inv-btn small">Approve and open</button>
        </form>
      </div>
    @empty
      <p class="inv-muted">Nothing waiting.</p>
    @endforelse
  </section>

  <section class="inv-section">
    <h2>Open and funded projects</h2>
    @forelse($activeProjects as $p)
      <div class="inv-glass">
        <b>{{ $p->title }}</b>
        <small>{{ $p->owner->name }} · {{ $p->status }} · raised {{ number_format($p->raised_q) }} / {{ number_format($p->goal_q) }} Q · escrow {{ number_format($p->availableToRelease()) }} Q · returns pending {{ number_format($p->availableReturns()) }} Q</small>

        @if($p->status === 'funded')
          <form method="POST" action="{{ route('admin.investment.projects.release', $p) }}" style="margin-top:10px">
            @csrf
            <input class="inv-input" type="number" name="amount_q" min="1" max="{{ $p->availableToRelease() }}" placeholder="Amount to release (Q)" required>
            <button class="inv-btn small">Release to owner</button>
          </form>

          <form method="POST" action="{{ route('admin.investment.projects.returns', $p) }}" style="margin-top:10px">
            @csrf
            <input class="inv-input" type="number" name="amount_q" min="1" placeholder="Income received (Q)" required>
            <button class="inv-btn small">Record returns</button>
          </form>

          <form method="POST" action="{{ route('admin.investment.projects.distribute', $p) }}" style="margin-top:10px">
            @csrf
            <input class="inv-input" type="number" name="amount_q" min="1" max="{{ $p->availableReturns() }}" placeholder="Pay to investors (Q)" required>
            <button class="inv-btn small">Pay investors</button>
          </form>
        @endif

        @if($p->released_q === 0)
          <form method="POST" action="{{ route('admin.investment.projects.cancel', $p) }}" style="margin-top:10px" onsubmit="return confirm('Cancel this project and refund every investor?')">
            @csrf
            <button class="inv-btn small ghost">Cancel and refund</button>
          </form>
        @endif
      </div>
    @empty
      <p class="inv-muted">No open or funded projects.</p>
    @endforelse
  </section>
</div>
@endsection

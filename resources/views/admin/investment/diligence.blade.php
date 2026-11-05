@extends('layouts.app-authenticated')
@section('title', 'Due diligence')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">
@php
  $checks = $d ? (array) $d->checks : [];
  $msSum = (int) $milestones->sum('amount_q');
@endphp
<div class="inv">
  <a href="{{ route('admin.investment.index') }}" class="inv-muted">← Investment admin</a>
  <header style="margin-top:10px">
    <p class="inv-kicker">Admin</p>
    <h1>{{ $project->title }}</h1>
    <p class="inv-sub">Status: {{ str_replace('_', ' ', ucfirst($project->status)) }} · Goal ₦{{ number_format($project->goal_q) }}</p>
  </header>
  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="inv-flash bad">{{ $errors->first() }}</div>@endif

  <form method="POST" action="{{ route('admin.investment.diligence.save', $project) }}" class="inv-glass inv-section">
    @csrf
    <h2>Checks (tick only what you have really verified)</h2>
    @foreach(\App\Services\InvestorGate::CHECKS as $k => $label)
      <label style="display:flex;gap:8px;align-items:center;margin:6px 0">
        <input type="checkbox" name="checks[{{ $k }}]" value="1" @checked(! empty($checks[$k]))> <span>{{ $label }}</span>
      </label>
    @endforeach
    <h2 style="margin-top:14px">Risk rating</h2>
    <select class="inv-input" name="risk_rating">
      <option value="">Not rated</option>
      @foreach(['low', 'medium', 'high'] as $r)
        <option value="{{ $r }}" @selected(($d->risk_rating ?? '') === $r)>{{ ucfirst($r) }}</option>
      @endforeach
    </select>
    <h2 style="margin-top:14px">State</h2>
    <input class="inv-input" name="location_state" maxlength="100" value="{{ old('location_state', $d->location_state ?? '') }}">
    <h2 style="margin-top:14px">Site address</h2>
    <input class="inv-input" name="location_address" maxlength="255" value="{{ old('location_address', $d->location_address ?? '') }}">
    <h2 style="margin-top:14px">CAC number</h2>
    <input class="inv-input" name="cac_number" maxlength="40" value="{{ old('cac_number', $d->cac_number ?? '') }}">
    <h2 style="margin-top:14px">Use of funds (public)</h2>
    <textarea class="inv-input" name="use_of_funds" rows="4" maxlength="2000">{{ old('use_of_funds', $d->use_of_funds ?? '') }}</textarea>
    <h2 style="margin-top:14px">Risks of this project (public)</h2>
    <textarea class="inv-input" name="risk_disclosure" rows="4" maxlength="2000">{{ old('risk_disclosure', $d->risk_disclosure ?? '') }}</textarea>
    <h2 style="margin-top:14px">Private notes</h2>
    <textarea class="inv-input" name="notes" rows="3" maxlength="2000">{{ old('notes', $d->notes ?? '') }}</textarea>
    <button class="inv-btn" type="submit">Save</button>
  </form>

  <section class="inv-glass inv-section">
    <h2>Milestones: ₦{{ number_format($msSum) }} of ₦{{ number_format($project->goal_q) }}</h2>
    <p class="inv-muted">They must add up to the goal before the project can open. Funds are released in order, after proof.</p>
    @foreach($milestones as $m)
      <div class="inv-row" style="display:block">
        <b>{{ $m->position }}. {{ $m->title }}</b> · ₦{{ number_format($m->amount_q) }}
        <span class="inv-status {{ $m->status === 'released' ? '' : 'off' }}">{{ ucfirst($m->status) }}</span>
        @if($m->description)<p class="inv-muted">{{ $m->description }}</p>@endif
        @if($m->status === 'released')
          <p class="inv-muted">Proof: {{ $m->proof_note }}</p>
        @elseif($project->status === 'funded')
          <form method="POST" action="{{ route('admin.investment.milestones.release', $m) }}">
            @csrf
            <textarea class="inv-input" name="proof_note" rows="2" minlength="10" maxlength="1000" required placeholder="What proof did you check? (photos, invoices, site visit)"></textarea>
            <button class="inv-btn small">Release ₦{{ number_format($m->amount_q) }} to owner</button>
          </form>
        @elseif($project->status === 'pending_review')
          <form method="POST" action="{{ route('admin.investment.milestones.delete', $m) }}">
            @csrf @method('DELETE')
            <button class="inv-btn small ghost">Remove</button>
          </form>
        @endif
      </div>
    @endforeach
    <form method="POST" action="{{ route('admin.investment.milestones.store', $project) }}" style="margin-top:12px">
      @csrf
      <input class="inv-input" name="title" maxlength="120" required placeholder="Milestone title">
      <textarea class="inv-input" name="description" rows="2" maxlength="1000" placeholder="What must be done"></textarea>
      <input class="inv-input" type="number" name="amount_q" min="1" required placeholder="Amount in ₦">
      <button class="inv-btn small">Add milestone</button>
    </form>
  </section>

  <section class="inv-glass inv-section">
    <h2>Updates for investors</h2>
    @foreach($updates as $u)
      <div class="inv-row" style="display:block"><b>{{ $u->title }}</b> <small class="inv-muted">{{ $u->created_at->format('M j, Y') }}</small>
        <p class="inv-muted" style="white-space:pre-line">{{ $u->body }}</p></div>
    @endforeach
    <form method="POST" action="{{ route('admin.investment.updates.store', $project) }}" style="margin-top:12px">
      @csrf
      <input class="inv-input" name="title" maxlength="120" required placeholder="Title">
      <textarea class="inv-input" name="body" rows="3" maxlength="3000" required placeholder="What happened"></textarea>
      <button class="inv-btn small">Post update</button>
    </form>
  </section>
@php
  $__invs = \App\Models\Investment::with('user')->where('investment_project_id', $project->id)->orderByDesc('id')->get();
@endphp
  <section class="inv-glass inv-section">
    <h2>Investors ({{ $__invs->where('status', 'active')->count() }} active)</h2>
    @forelse($__invs as $__inv)
      <div class="inv-row">
        <span>{{ $__inv->user->name ?? 'user' }} <small class="inv-muted">{{ $__inv->created_at->format('M j, Y') }}</small></span>
        <span>₦{{ number_format($__inv->amount_q) }} <span class="inv-status {{ $__inv->status === 'active' ? '' : 'off' }}">{{ ucfirst($__inv->status) }}</span></span>
      </div>
    @empty
      <p class="inv-muted">No investors yet.</p>
    @endforelse
  </section>
</div>
@endsection

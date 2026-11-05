@extends('layouts.app-authenticated')
@section('title', 'Investment activity log')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">
<div class="inv">
  <a href="{{ route('admin.investment.index') }}" class="inv-muted">← Investment admin</a>
  <header style="margin-top:10px">
    <p class="inv-kicker">Admin</p>
    <h1>Activity log</h1>
    <p class="inv-sub">The last 300 actions on ID checks, due diligence, milestones and refunds.</p>
  </header>
  <div class="inv-glass inv-section">
    @forelse($rows as $r)
      <div class="inv-row" style="display:block">
        <b>{{ str_replace('_', ' ', $r->action) }}</b>
        <small class="inv-muted">{{ $r->admin_name ?? 'system' }} · {{ \Carbon\Carbon::parse($r->created_at)->format('M j, Y g:ia') }}@if($r->ip) · {{ $r->ip }}@endif</small>
        @if($r->subject_type)<div class="inv-muted">{{ $r->subject_type }} #{{ $r->subject_id }} {{ $r->details }}</div>@endif
      </div>
    @empty
      <p class="inv-muted">Nothing logged yet.</p>
    @endforelse
  </div>
</div>
@endsection

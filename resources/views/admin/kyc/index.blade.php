@extends('layouts.app-authenticated')
@section('title', 'ID checks')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">
<div class="inv">
  <a href="{{ route('admin.investment.index') }}" class="inv-muted">← Investment admin</a>
  <header style="margin-top:10px">
    <p class="inv-kicker">Admin</p>
    <h1>ID checks waiting</h1>
  </header>
  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif
  @forelse($rows as $r)
    <div class="inv-glass inv-section">
      <b>{{ $r->name }}</b> <small>{{ $r->email }}</small>
      <p class="inv-muted">Phone {{ $r->phone }} · {{ str_replace('_', ' ', (string) $r->id_type) }}<br>{{ $r->address_line }}, {{ $r->city }}, {{ $r->state }}, {{ $r->country }}</p>
      <p>
        @if($r->id_front_path)<a class="inv-status" target="_blank" rel="noopener" href="{{ route('admin.kyc.file', [$r->id, 'id_front']) }}">ID front</a>@endif
        @if($r->id_back_path)<a class="inv-status" target="_blank" rel="noopener" href="{{ route('admin.kyc.file', [$r->id, 'id_back']) }}">ID back</a>@endif
        @if($r->selfie_path)<a class="inv-status" target="_blank" rel="noopener" href="{{ route('admin.kyc.file', [$r->id, 'selfie']) }}">Selfie</a>@endif
      </p>
      <form method="POST" action="{{ route('admin.kyc.decision', $r->id) }}">
        @csrf
        <input class="inv-input" name="note" maxlength="500" placeholder="Note (required if you reject)">
        <button class="inv-btn small" name="decision" value="approve">Approve</button>
        <button class="inv-btn small ghost" name="decision" value="reject">Reject</button>
      </form>
    </div>
  @empty
    <p class="inv-muted">Nothing waiting.</p>
  @endforelse
</div>
@endsection

@extends('layouts.app-authenticated')
@section('title', 'Investor profile')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">
<div class="inv">
  <a href="{{ route('investment.index') }}" class="inv-muted">← Investments</a>
  <header style="margin-top:10px">
    <p class="inv-kicker">Investor</p>
    <h1>Investor profile</h1>
  </header>
  @if(session('status'))<div class="inv-flash ok">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="inv-flash bad">{{ session('error') }}</div>@endif
  @include('investment._status')
  <section class="inv-glass inv-section">
    <h2>Yearly income</h2>
    @if($income > 0)
      <p class="inv-muted">Declared: ₦{{ number_format($income) }}.
        @if($limit !== null)
          Your yearly investing limit is {{ rtrim(rtrim(number_format($pct, 2), '0'), '.') }}% of that: ₦{{ number_format($limit) }}.
          Used this year: ₦{{ number_format($used) }}. Left: ₦{{ number_format(max(0, $limit - $used)) }}.
        @endif
        To change your declared income, contact support.
      </p>
    @else
      <p class="inv-muted">Tell us your yearly income in naira. It sets how much you can invest in a year. Be honest, we may ask for proof.</p>
      <form method="POST" action="{{ route('investment.profile.save') }}">
        @csrf
        <input class="inv-input" type="number" name="annual_income_naira" min="1" step="1" required value="{{ old('annual_income_naira') }}">
        @error('annual_income_naira')<div class="inv-err">{{ $message }}</div>@enderror
        <button class="inv-btn" type="submit">Save</button>
      </form>
    @endif
  </section>
</div>
@endsection

@extends('layouts.app-authenticated')
@section('title', 'New project')
@section('content')
<link rel="stylesheet" href="{{ asset('css/investment.css') }}?v={{ @filemtime(public_path('css/investment.css')) }}">

<div class="inv">
  <a href="{{ route('investment.index') }}" class="inv-muted">← Investments</a>
  <header style="margin-top:10px">
    <p class="inv-kicker">New</p>
    <h1>List a project</h1>
    <p class="inv-sub">Goal range {{ number_format(config('investment.min_goal_q')) }} to {{ number_format(config('investment.max_goal_q')) }} Q. Each investor is capped at {{ config('investment.max_share_of_goal_percent') }}% of the goal.</p>
  </header>

  @if($errors->any())
    <div class="inv-flash bad">{{ $errors->first('project') ?: $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('investment.store') }}" class="inv-glass inv-section">
    @csrf
    <h2>Type</h2>
    <select class="inv-input" name="type" required>
      @foreach($types as $key => $label)
        <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
      @endforeach
    </select>

    <h2 style="margin-top:14px">Title</h2>
    <input class="inv-input" type="text" name="title" maxlength="120" required value="{{ old('title') }}">

    <h2 style="margin-top:14px">What is this project?</h2>
    <textarea class="inv-input" name="description" rows="6" maxlength="3000" required>{{ old('description') }}</textarea>

    <h2 style="margin-top:14px">Goal (Q)</h2>
    <input class="inv-input" type="number" name="goal_q" min="{{ config('investment.min_goal_q') }}" max="{{ config('investment.max_goal_q') }}" required value="{{ old('goal_q') }}">

    <h2 style="margin-top:14px">Max per investor (Q)</h2>
    <input class="inv-input" type="number" name="max_invest_q" min="1" required value="{{ old('max_invest_q') }}">

    <h2 style="margin-top:14px">Days open</h2>
    <input class="inv-input" type="number" name="duration_days" min="{{ config('investment.min_duration_days') }}" max="{{ config('investment.max_duration_days') }}" required value="{{ old('duration_days', 30) }}">

    <button class="inv-btn" type="submit">Submit for review</button>
  </form>
</div>
@endsection

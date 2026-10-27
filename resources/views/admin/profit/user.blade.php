@extends('layouts.app-admin')
@section('title', $target->name . ' · flow')
@section('content')
@php $max = max(1, max($flow['earnings'] ?: [1])); @endphp
<div style="max-width:900px;margin:0 auto;padding:24px;display:flex;flex-direction:column;gap:16px;">
  <a href="{{ route('admin.profit.index', ['month' => $month]) }}" style="font-size:13px;">← All users</a>
  <h1 style="font-size:24px;font-weight:900;margin:0;">{{ $target->name }} · {{ $month }}</h1>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Money in</small><br><b>₦{{ number_format($flow['in']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Money out</small><br><b>₦{{ number_format($flow['out']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Net</small><br><b>₦{{ number_format($flow['net']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Q earned</small><br><b>{{ number_format($flow['q_earned']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Q spent</small><br><b>{{ number_format($flow['q_spent']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Profit share Q</small><br><b>{{ number_format($flow['q_profit_share']) }}</b></div>
    <div style="padding:14px;border-radius:16px;border:1px solid rgba(128,128,128,.25);"><small>Ranking points</small><br><b>{{ number_format($flow['points']) }}</b></div>
  </div>

  <section style="border:1px solid rgba(128,128,128,.25);border-radius:18px;padding:18px;">
    <h2 style="font-size:16px;font-weight:800;margin:0 0 10px;">Where the money came from</h2>
    @foreach($flow['earnings'] as $label => $amt)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>{{ $label }}</span><b>₦{{ number_format($amt) }}</b></div>
      <div style="height:6px;border-radius:6px;background:rgba(128,128,128,.2);margin-bottom:8px;"><div style="height:6px;border-radius:6px;background:#7c5cf0;width:{{ $amt / $max * 100 }}%;"></div></div>
    @endforeach
    <p style="font-size:12px;opacity:.7;margin:10px 0 0;">Investment returns: {{ $flow['investments'] ? '₦' . number_format($flow['investments']) : 'no investments yet' }}.</p>
  </section>
</div>
@endsection

@extends('layouts.app-admin')
@section('title', 'Profit & Q Coin')
@section('content')
<div style="max-width:1100px;margin:0 auto;padding:24px;display:flex;flex-direction:column;gap:18px;">
  <header>
    <h1 style="font-size:24px;font-weight:900;margin:0;">Profit & Q Coin</h1>
    <p style="font-size:13px;opacity:.7;margin:4px 0 0;">Mode: <b>{{ config('qcoin.mode') === 'live' ? 'LIVE, credits real Q' : 'TEST, preview only' }}</b> · Pool: {{ (int) (config('qcoin.profit_share.pool_share') * 100) }}% of profit · {{ config('qcoin.profit_share.q_per_naira') }} Q per ₦1</p>
  </header>

  <form method="GET" action="{{ route('admin.profit.index') }}" style="display:flex;gap:8px;align-items:center;">
    <input type="month" name="month" value="{{ $month }}">
    <button type="submit">Show month</button>
  </form>

  <section style="border:1px solid rgba(128,128,128,.25);border-radius:18px;padding:18px;">
    <h2 style="font-size:16px;font-weight:800;margin:0 0 10px;">{{ $month }} run</h2>
    @if($run)
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;font-size:13px;">
        <div><small>Revenue</small><br><b>₦{{ number_format($run->revenue, 2) }}</b></div>
        <div><small>Costs</small><br><b>₦{{ number_format($run->costs, 2) }}</b></div>
        <div><small>Profit</small><br><b>₦{{ number_format($run->profit, 2) }}</b></div>
        <div><small>Pool</small><br><b>₦{{ number_format($run->pool, 2) }}</b></div>
        <div><small>Q minted</small><br><b>{{ number_format($run->pool_q) }} Q</b></div>
        <div><small>Status</small><br><b>{{ strtoupper($run->status) }} · {{ strtoupper($run->mode) }}</b></div>
      </div>
    @else
      <p style="font-size:13px;opacity:.7;margin:0;">No run for this month yet. Run <code>php artisan qcoin:profit-share --month={{ $month }} --costs=0</code> to create a preview.</p>
    @endif
  </section>

  <section style="border:1px solid rgba(128,128,128,.25);border-radius:18px;padding:18px;">
    <h2 style="font-size:16px;font-weight:800;margin:0 0 10px;">Allocations</h2>
    @forelse($allocs as $a)
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid rgba(128,128,128,.2);font-size:13px;">
        <span>{{ $a->name }}</span>
        <span>{{ number_format($a->q_earned) }} earned · <b>{{ number_format($a->q_share) }} Q</b> · {{ $a->credited_at ? 'credited' : 'preview' }}</span>
      </div>
    @empty
      <p style="font-size:13px;opacity:.7;">No allocations.</p>
    @endforelse
  </section>

  <section style="border:1px solid rgba(128,128,128,.25);border-radius:18px;padding:18px;">
    <h2 style="font-size:16px;font-weight:800;margin:0 0 10px;">Every user this month</h2>
    @forelse($users as $row)
      <a href="{{ route('admin.profit.user', ['user' => $row['user']->id, 'month' => $month]) }}" style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid rgba(128,128,128,.2);font-size:13px;color:inherit;text-decoration:none;">
        <span>{{ $row['user']->name }}</span>
        <span>In ₦{{ number_format($row['flow']['in']) }} · Out ₦{{ number_format($row['flow']['out']) }} · Q {{ number_format($row['flow']['q_earned']) }}</span>
      </a>
    @empty
      <p style="font-size:13px;opacity:.7;">No user activity this month.</p>
    @endforelse
  </section>

  <section style="border:1px solid rgba(128,128,128,.25);border-radius:18px;padding:18px;">
    <h2 style="font-size:16px;font-weight:800;margin:0 0 10px;">Recent runs</h2>
    @foreach($runs as $r)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;">
        <span>{{ $r->month }}</span><span>₦{{ number_format($r->profit, 2) }} profit · {{ number_format($r->pool_q) }} Q · {{ $r->status }}</span>
      </div>
    @endforeach
  </section>
</div>
@endsection

@php
  $uf = app(\App\Services\UserFlowService::class)->forMonth(auth()->user());
  $ufMax = max(1, max($uf['earnings'] ?: [1]));
@endphp
<link rel="stylesheet" href="{{ asset('css/flow.css') }}?v={{ time() }}">
<section class="fl">
  <header class="fl-head"><h2>Your money this month</h2><span>{{ now()->format('F Y') }}</span></header>
  <div class="fl-kpis">
    <div><small>Money in</small><b>{{ number_format(($uf['in']) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($uf['in']) }}</small></b></div>
    <div><small>Money out</small><b>{{ number_format(($uf['out']) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($uf['out']) }}</small></b></div>
    <div class="{{ $uf['net'] >= 0 ? 'pos' : 'neg' }}"><small>Net</small><b>{{ $uf['net'] >= 0 ? '+' : '' }}{{ number_format(($uf['net']) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($uf['net']) }}</small></b></div>
  </div>
  <h3>Where your earnings come from</h3>
  @foreach($uf['earnings'] as $label => $amt)
    <div class="fl-row"><span>{{ $label }}</span><b>{{ number_format(($amt) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format($amt) }}</small></b></div>
    <div class="fl-bar"><i style="width: {{ $amt / $ufMax * 100 }}%"></i></div>
  @endforeach
  <div class="fl-q">
    <div><small>Q earned</small><b>{{ number_format($uf['q_earned']) }}</b></div>
    <div><small>Q spent</small><b>{{ number_format($uf['q_spent']) }}</b></div>
    <div><small>Profit share</small><b>{{ number_format($uf['q_profit_share']) }}</b></div>
  </div>
  <div class="fl-inv"><b>Investments</b><span>{{ $uf['investments'] ? number_format($uf['investments'] * config('qcoin.q_per_naira_display', 10)) . ' Q returned this month' : 'Not launched yet. Your returns will show here once they start.' }}</span></div>
</section>

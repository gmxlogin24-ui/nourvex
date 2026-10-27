<section class="dgd" aria-label="Overview details">
  <div class="dgd-stats">
    <div class="dgd-s"><small>Total balance</small><b>{{ number_format(((float) $balance) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format((float) $balance) }}</small></b><em class="{{ $net < 0 ? 'dn' : 'up' }}">{{ $net < 0 ? '−' : '+' }}{{ number_format((abs($net)) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format(abs($net)) }}</small> this week</em></div>
    <div class="dgd-s"><small>Money in (7 days)</small><b>{{ number_format(((float) $weekIn) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format((float) $weekIn) }}</small></b><em class="up">Received</em></div>
    <div class="dgd-s"><small>Money out (7 days)</small><b>{{ number_format(((float) $weekOut) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format((float) $weekOut) }}</small></b><em class="dn">Spent</em></div>
    <div class="dgd-s"><small>Open hustles</small><b>{{ $gigCount }}</b><em class="up">Ready for you</em></div>
  </div>
  <div class="dgd-row">
    <div class="dgd-p">
      <h5>Weekly cash flow</h5>
      <div class="dgd-chart">
        @foreach($flow as $fv)<i class="{{ $fv < 0 ? 'neg' : '' }}" title="{{ number_format((abs($fv)) * config('qcoin.q_per_naira_display', 10)) }} Q<small style="display:block;font-size:.5em;font-weight:500;opacity:.4">₦{{ number_format(abs($fv)) }}</small>" style="height: {{ max(8, round(abs($fv) / $fmax * 100)) }}%"></i>@endforeach
      </div>
    </div>
    <div class="dgd-p">
      <h5>Jump to</h5>
      <div class="dgd-links">
        <a href="{{ route('gigs.index') }}"><i data-lucide="briefcase"></i>Hustles</a>
        <a href="{{ route('marketplace.index') }}"><i data-lucide="shopping-bag"></i>Market</a>
        <a href="{{ $gvUrl }}"><i data-lucide="flag"></i>NaGolf</a>
        <a href="{{ route('wallet.index') }}"><i data-lucide="wallet"></i>Wallet</a>
        <a href="{{ route('profile.index') }}"><i data-lucide="user"></i>Profile</a>
      </div>
    </div>
  </div>
</section>

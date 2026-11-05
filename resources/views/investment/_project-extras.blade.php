@php
  $__d = \App\Models\InvestmentDiligence::where('investment_project_id', $project->id)->first();
  $__ms = \App\Models\InvestmentMilestone::where('investment_project_id', $project->id)->orderBy('position')->get();
  $__up = \App\Models\InvestmentUpdate::where('investment_project_id', $project->id)->latest()->take(10)->get();
  $__checks = $__d ? (array) $__d->checks : [];
@endphp
@if($__d)
  <section class="inv-glass inv-section">
    <h2>Checks and risk</h2>
    <p class="inv-muted">Risk level: <b>{{ ucfirst($__d->risk_rating ?: 'not rated') }}</b>
      @if($__d->location_state) · {{ $__d->location_state }}@if($__d->location_address), {{ $__d->location_address }}@endif @endif
      @if($__d->cac_number) · CAC {{ $__d->cac_number }}@endif
    </p>
    @foreach(\App\Services\InvestorGate::CHECKS as $__k => $__l)
      <div class="inv-row"><span>{{ $__l }}</span><span class="inv-status {{ ! empty($__checks[$__k]) ? '' : 'off' }}">{{ ! empty($__checks[$__k]) ? 'Passed' : 'Pending' }}</span></div>
    @endforeach
    @if($__d->use_of_funds)
      <h2 style="margin-top:12px">Use of funds</h2>
      <p class="inv-muted" style="white-space:pre-line;color:var(--ink)">{{ $__d->use_of_funds }}</p>
    @endif
    @if($__d->risk_disclosure)
      <h2 style="margin-top:12px">Risks of this project</h2>
      <p class="inv-muted" style="white-space:pre-line;color:var(--ink)">{{ $__d->risk_disclosure }}</p>
    @endif
  </section>
@endif
@if($__ms->count())
  <section class="inv-glass inv-section">
    <h2>Milestones</h2>
    <p class="inv-muted">Money is released to the owner one milestone at a time, after an admin checks proof.</p>
    @foreach($__ms as $__m)
      <div class="inv-row">
        <span>{{ $__m->position }}. {{ $__m->title }}</span>
        <span>₦{{ number_format($__m->amount_q) }} <span class="inv-status {{ $__m->status === 'released' ? '' : 'off' }}">{{ ucfirst($__m->status) }}</span></span>
      </div>
    @endforeach
  </section>
@endif
@if($__up->count())
  <section class="inv-glass inv-section">
    <h2>Updates</h2>
    @foreach($__up as $__u)
      <div class="inv-row" style="display:block">
        <b>{{ $__u->title }}</b> <small class="inv-muted">{{ $__u->created_at->format('M j, Y') }}</small>
        <p class="inv-muted" style="white-space:pre-line;color:var(--ink)">{{ $__u->body }}</p>
      </div>
    @endforeach
  </section>
@endif
@if(isset($mine))
  @foreach($mine as $__i)
    @if($__i->status === 'active' && $__i->cooling_off_until && now()->lt(\Carbon\Carbon::parse($__i->cooling_off_until)) && $project->status === 'open')
      <form method="POST" action="{{ route('investment.cancel', $__i) }}" class="inv-section">
        @csrf
        <small class="inv-muted">You can cancel ₦{{ number_format($__i->amount_q) }} until {{ \Carbon\Carbon::parse($__i->cooling_off_until)->format('M j, g:ia') }}.</small>
        <button class="inv-btn small ghost" type="submit">Cancel and get my money back</button>
      </form>
    @endif
  @endforeach
@endif

@php
  $__goal = max(1, (int) $project->goal_q);
  $__feePct = (int) config('investment.platform_fee_percent');
  $__myPayouts = \App\Models\InvestmentPayout::where('investment_project_id', $project->id)->where('user_id', auth()->id())->latest()->get();
@endphp
@if($project->status === 'open' || $project->status === 'funded')
<section class="inv-glass inv-section" id="share-calc">
  <h2>What would my share be?</h2>
  <p class="inv-muted">This is simple arithmetic, not a forecast. The project may earn less than any example, later than expected, or nothing at all.</p>
  <input class="inv-input" type="number" id="scAmount" min="1" step="1" value="{{ (int) $project->min_invest_q }}" placeholder="Amount you would invest (₦)">
  <select class="inv-input" id="scIncome">
    <option value="100000">If the project pays out ₦100,000 in total</option>
    <option value="500000">If the project pays out ₦500,000 in total</option>
    <option value="1000000" selected>If the project pays out ₦1,000,000 in total</option>
    <option value="5000000">If the project pays out ₦5,000,000 in total</option>
  </select>
  <p class="inv-muted" id="scOut"></p>
</section>
<script>
(function () {
  var goal = {{ $__goal }}, fee = {{ $__feePct }};
  var a = document.getElementById('scAmount'), i = document.getElementById('scIncome'), o = document.getElementById('scOut');
  function fmt(n) { return '₦' + Math.floor(n).toLocaleString('en-NG'); }
  function calc() {
    var amt = Math.max(0, parseFloat(a.value) || 0), gross = parseFloat(i.value) || 0;
    var share = Math.min(1, amt / goal), net = gross * (1 - fee / 100);
    o.textContent = 'Your share of the project would be ' + (share * 100).toFixed(2) + '%. After our ' + fee + '% fee on returns, you would receive about ' + fmt(net * share) + ' of that payout.';
  }
  a.addEventListener('input', calc);
  i.addEventListener('change', calc);
  calc();
})();
</script>
@endif
@if($__myPayouts->count())
<section class="inv-glass inv-section">
  <h2>Money paid back to you</h2>
  @foreach($__myPayouts as $__pay)
    <div class="inv-row">
      <span>{{ $__pay->created_at->format('M j, Y') }} · {{ $__pay->kind === 'return' ? 'Return' : 'Refund' }}</span>
      <span>₦{{ number_format($__pay->amount_q) }}</span>
    </div>
  @endforeach
</section>
@endif

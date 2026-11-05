@php($__rows = app(\App\Services\InvestorGate::class)->checklist(auth()->user()))
@if(collect($__rows)->contains(fn ($row) => ! $row['ok']))
  <div class="inv-flash bad">You cannot invest yet. Still needed:
    @foreach($__rows as $__r)@if(! $__r['ok'])<a href="{{ $__r['url'] }}">{{ $__r['label'] }}</a>@if(! $loop->last) · @endif @endif @endforeach
  </div>
@endif
<div class="inv-glass" style="margin:10px 0">
  <h2>Risk warning</h2>
  @include('investment._risk-text')
  <label style="display:flex;gap:8px;align-items:flex-start;margin-top:8px">
    <input type="checkbox" name="risk_ack" value="1" required>
    <span class="inv-muted">I have read this, I am within my yearly limit, and I can afford to lose all of this money.</span>
  </label>
  @error('risk_ack')<div class="inv-err">Please accept the risk warning.</div>@enderror
</div>
<input class="inv-input" type="password" name="pin" inputmode="numeric" autocomplete="off" maxlength="12" required placeholder="Transaction PIN">
@error('pin')<div class="inv-err">{{ $message }}</div>@enderror

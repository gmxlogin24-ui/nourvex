@php($__rows = app(\App\Services\InvestorGate::class)->checklist(auth()->user()))
<section class="inv-glass inv-section">
  <h2>Your investor status</h2>
  <p class="inv-muted">To invest you must pass every step below. The rest of Nourvex works as normal without this.</p>
  @foreach($__rows as $__r)
    <div class="inv-row">
      <span>{{ $__r['label'] }}</span>
      @if($__r['ok'])
        <span class="inv-status">Done</span>
      @else
        <a class="inv-status off" href="{{ $__r['url'] }}">Do this</a>
      @endif
    </div>
  @endforeach
</section>

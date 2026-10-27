@php
  $gcPk = is_array($gig->packages) ? $gig->packages : [];
  $gcFrom = count($gcPk) ? min(array_map(fn ($x) => (float) ($x['price'] ?? 0), $gcPk)) : (float) $gig->amount;
  $gcMeta = $gfCat[$gig->category] ?? ['briefcase', '#64748B'];
  $gcPos = $gig->poster ?? null;
  $gcLvl = data_get($gig->poster_level ?? null, 'label');
@endphp
<a href="{{ route('gigs.show', $gig->id) }}" class="gf-c">
  <span class="gf-ci" style="background:linear-gradient(145deg,{{ $gcMeta[1] }},#111827)">
    <i data-lucide="{{ $gcMeta[0] }}"></i>
    <em>{{ $gig->category }}</em>
    @if(count($gcPk))<span class="gf-pkt">{{ count($gcPk) }} packages</span>@endif
  </span>
  <span class="gf-cb">
    <span class="gf-by">
      <span class="gf-av">@if($gcPos && $gcPos->avatar_url)<img src="{{ $gcPos->avatar_url }}" alt="">@else{{ strtoupper(substr($gig->poster_name ?? 'U', 0, 1)) }}@endif</span>
      <b>{{ \Illuminate\Support\Str::limit($gig->poster_name ?? 'User', 14) }}</b>
      @if(!empty($gig->poster_verified))<i data-lucide="badge-check" class="gf-vf"></i>@endif
    </span>
    <span class="gf-t">{{ $gig->title }}</span>
    <span class="gf-rt">
      @if(!empty($gig->avg_rating))<i data-lucide="star"></i><b>{{ $gig->avg_rating }}</b><small>({{ $gig->review_count }})</small>@else<small>New</small>@endif
      @if($gcLvl)<small class="gf-lv">{{ $gcLvl }}</small>@endif
    </span>
    <span class="gf-f"><small>{{ count($gcPk) ? 'Starting at' : 'Budget' }}</small><strong>₦{{ number_format($gcFrom) }}</strong></span>
  </span>
</a>

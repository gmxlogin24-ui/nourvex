@php
  try { $oaRows = \App\Models\Announcement::live()->latest()->take(8)->get(); } catch (\Throwable $e) { $oaRows = collect(); }
  $oaGrad = [
    'orange' => 'linear-gradient(135deg,#F97316,#C2410C)',
    'teal'   => 'linear-gradient(135deg,#0EA5A4,#115E59)',
    'green'  => 'linear-gradient(135deg,#16A34A,#14532D)',
    'purple' => 'linear-gradient(135deg,#7C5CF0,#3B2A8A)',
    'blue'   => 'linear-gradient(135deg,#2563EB,#1E3A8A)',
    'rose'   => 'linear-gradient(135deg,#E11D48,#881337)',
  ];
@endphp
@if($oaRows->count())
<link rel="stylesheet" href="{{ asset('css/ov-announce.css') }}?v={{ time() }}">
<section class="ova" aria-label="Announcements">
  <div class="ova-hd"><b>Announcements</b></div>
  <div class="ova-row">
    @foreach($oaRows as $oa)
    @php
      $oaBg = $oa->image_url
        ? "linear-gradient(180deg,rgba(0,0,0,.08) 0%,rgba(0,0,0,.72) 100%),url('" . $oa->image_url . "')"
        : ($oaGrad[$oa->color] ?? $oaGrad['orange']);
      $oaTag = $oa->link_url ? 'a' : 'div';
    @endphp
    <{{ $oaTag }} @if($oa->link_url) href="{{ $oa->link_url }}" @endif class="ova-a" style="background-image:{{ $oaBg }};background-size:cover;background-position:center">
      <span class="ova-ic"><i data-lucide="megaphone"></i></span>
      <small>Announcement</small>
      <strong>{{ $oa->title }}</strong>
      @if($oa->body)<span class="ova-sub">{{ $oa->body }}</span>@endif
    </{{ $oaTag }}>
    @endforeach
  </div>
</section>
@endif

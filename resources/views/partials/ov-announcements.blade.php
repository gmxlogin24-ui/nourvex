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
<link rel="stylesheet" href="{{ asset('css/ov-announce2.css') }}?v={{ time() }}">
<section class="an-sec" aria-label="Announcements">
  <div class="an-hd">
    <b><i data-lucide="megaphone"></i>Announcements</b>
    <a href="{{ route('announcements.index') }}">See all<i data-lucide="chevron-right"></i></a>
  </div>
  <div class="an-row">
    @foreach($oaRows as $oa)
      @php
        $oaBg = $oa->image_url
          ? "linear-gradient(180deg,rgba(0,0,0,.05) 0%,rgba(0,0,0,.78) 100%),url('" . $oa->image_url . "')"
          : ($oaGrad[$oa->color] ?? $oaGrad['blue']);
        $oaNew = $oa->created_at && $oa->created_at->gt(now()->subDays(3));
      @endphp
      <a href="{{ route('announcements.index', ['a' => $oa->id]) }}" class="an-c" style="background-image:{{ $oaBg }}">
        <div class="an-top">
          <span class="an-ic"><i data-lucide="megaphone"></i></span>
          @if($oaNew)<span class="an-new">New</span>@endif
        </div>
        <strong>{{ $oa->title }}</strong>
        @if($oa->body)<span class="an-sub">{{ $oa->body }}</span>@endif
        <span class="an-more">Read more<i data-lucide="arrow-right"></i></span>
      </a>
    @endforeach
  </div>
</section>
@endif

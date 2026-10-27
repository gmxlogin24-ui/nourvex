@php
  $spSrc = collect($recommended ?? [])->count() >= 1 ? collect($recommended) : collect($listings ?? []);
  $sp = $spSrc->first();
  $spImg = null;
  if ($sp) {
    $spImg = data_get($sp, 'image_url');
    if (!$spImg) {
      $spF = data_get($sp, 'images.0');
      $spImg = $spF ? (data_get($spF, 'url') ?? data_get($spF, 'path') ?? data_get($spF, 'image_path') ?? data_get($spF, 'image_url')) : null;
    }
    if ($spImg && !preg_match('~^(https?:)?//~', $spImg)) { $spImg = asset(ltrim($spImg, '/')); }
  }
@endphp
@if($sp)
<a href="{{ route('marketplace.show', data_get($sp, 'id')) }}" class="mk-sp">
  <span class="mk-sp-i">@if($spImg)<img src="{{ $spImg }}" alt="" loading="lazy">@else<i data-lucide="shopping-bag"></i>@endif</span>
  <span class="mk-sp-b"><small>Sponsored</small><b>{{ \Illuminate\Support\Str::limit((string) (data_get($sp, 'title') ?? data_get($sp, 'name') ?? data_get($sp, 'category') ?? 'Featured listing'), 26) }}</b><em>₦{{ number_format((float) data_get($sp, 'amount', 0)) }}</em></span>
  <i data-lucide="arrow-right" class="mk-sp-a"></i>
</a>
@endif

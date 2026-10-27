@php
  $img = $imgOf($item);
  $rt = $mkRates[$item->id] ?? null;
  $left = (int) ($item->stock_quantity ?? 0);
@endphp
<div class="mk2-p">
  <a href="{{ route('marketplace.show', $item->id) }}" class="mk2-link">
    <span class="mk2-i">
      @if($img)<img src="{{ $img }}" alt="" loading="lazy">@else<i data-lucide="{{ $cats[$item->category][0] ?? 'shopping-bag' }}"></i>@endif
      @if($item->user_id && in_array($item->user_id, $mkVerified))<span class="mk2-ver" aria-label="Verified seller"><i data-lucide="badge-check"></i></span>@endif
    </span>
    <span class="mk2-b">
      <span class="mk2-t">{{ $item->title }}</span>
      <span class="mk2-pr">₦{{ number_format($item->amount) }}</span>
      @if($rt)<span class="mk2-r"><i data-lucide="star"></i>{{ number_format($rt->a, 1) }} <small>({{ $rt->c }})</small></span>@endif
      @if($left > 0 && $left <= 5)<span class="mk2-low">Only {{ $left }} left</span>@endif
      @if(!empty($item->location))<span class="mk2-l"><i data-lucide="map-pin"></i>{{ $item->location }}</span>@endif
    </span>
  </a>
  <button type="button" class="mk2-heart {{ in_array($item->id, $mkLoved) ? 'on' : '' }}" data-url="{{ route('marketplace.love', $item->id) }}" aria-label="Save and add to cart"><i data-lucide="heart"></i></button>
</div>

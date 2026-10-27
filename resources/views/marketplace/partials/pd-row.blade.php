@if($items->count())
<section class="pd-s pd-sr">
  <h2 class="pd-sec">{{ $title }}</h2>
  <div class="pd-sim">
    @foreach($items as $pdS)
      @php $pdSi = $pdS->image_url ?: optional($pdS->images->first())->image_url; @endphp
      <a href="{{ route('marketplace.show', $pdS->id) }}" class="pd-sc">
        <span class="im">@if($pdSi)<img src="{{ $pdSi }}" alt="" loading="lazy">@else<i data-lucide="shopping-bag"></i>@endif</span>
        <b>{{ $pdS->title }}</b>
        <em>₦{{ number_format($pdS->amount) }}</em>
        @if($pdS->location)<small><i data-lucide="map-pin"></i>{{ $pdS->location }}</small>@endif
      </a>
    @endforeach
  </div>
</section>
@endif

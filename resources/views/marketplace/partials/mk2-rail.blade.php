@if(count($rItems))
<section class="mk2-sec">
  <div class="mk2-sh"><b>{{ $rTitle }}</b>@if(!empty($rLink))<a href="{{ $rLink }}" class="mk2-all" aria-label="See all {{ $rTitle }}"><i data-lucide="arrow-right"></i></a>@endif</div>
  <div class="mk2-rail">
    @foreach($rItems as $item)
      @include('marketplace.partials.mk2-card')
    @endforeach
  </div>
</section>
@endif

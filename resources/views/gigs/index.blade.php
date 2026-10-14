@extends('layouts.app-authenticated')
@section('title', 'Hustles')

@section('content')
@php
  $list = isset($gigsPage) ? $gigsPage : $gigs;
  $openCount = collect($gigs)->where('status', 'open')->count();
  $avgPay = (int) round(collect($gigs)->where('status', 'open')->avg('amount') ?? 0);
  $cats = collect(isset($categories) ? $categories : [])->filter()->values();
  if ($cats->isEmpty()) { $cats = collect(['Typing & Printing', 'Hair & Beauty', 'Repairs', 'Errands & Delivery', 'Tutoring', 'Photography']); }
  $icons = ['Typing & Printing' => 'printer', 'Hair & Beauty' => 'scissors', 'Repairs' => 'wrench', 'Errands & Delivery' => 'truck', 'Tutoring' => 'graduation-cap', 'Photography' => 'camera'];
  $cat = request('category'); $sort = request('sort');
  $u = function ($extra = []) {
      $p = array_merge(request()->only(['search', 'category', 'sort']), $extra);
      return route('gigs.index', array_filter($p, fn ($v) => $v !== null && $v !== ''));
  };
@endphp
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">

<div class="wp wp-gigs">
  <div class="wp-top">
    <div>
      <h1>Hustles</h1>
      <p class="wp-sub">{{ $openCount }} open &middot; avg ₦{{ number_format($avgPay) }}</p>
    </div>
    <div class="wp-acts">
      <a href="{{ route('posts.index') }}" class="wp-ico" title="My posts"><i data-lucide="layers" style="width:17px;height:17px"></i></a>
      @if(auth()->user()->planAllows('gigs_post'))
        <a href="{{ route('gigs.create') }}" class="wp-btn"><i data-lucide="plus" style="width:15px;height:15px"></i>Post</a>
      @endif
    </div>
  </div>

  <form method="GET" action="{{ route('gigs.index') }}" class="wp-search">
    <i data-lucide="search"></i>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search hustles...">
    @if($cat)<input type="hidden" name="category" value="{{ $cat }}">@endif
    @if($sort)<input type="hidden" name="sort" value="{{ $sort }}">@endif
  </form>

  <div class="wp-chips">
    <a href="{{ $u(['category' => null]) }}" class="wp-chip {{ !$cat ? 'on' : '' }}">All</a>
    @foreach($cats as $c)
      <a href="{{ $u(['category' => $c]) }}" class="wp-chip {{ $cat === $c ? 'on' : '' }}">{{ $c }}</a>
    @endforeach
  </div>
  <div class="wp-sort">
    <span>Sort</span>
    <a href="{{ $u(['sort' => null]) }}" class="{{ !$sort ? 'on' : '' }}">Best match</a>
    <a href="{{ $u(['sort' => 'newest']) }}" class="{{ $sort === 'newest' ? 'on' : '' }}">Newest</a>
    <a href="{{ $u(['sort' => 'highest']) }}" class="{{ $sort === 'highest' ? 'on' : '' }}">Highest pay</a>
  </div>

  <div class="wp-list">
    @forelse($list as $gig)
      <a href="{{ route('gigs.show', $gig->id) }}" class="gc">
        <span class="gc-ic"><i data-lucide="{{ $icons[$gig->category] ?? 'briefcase' }}" style="width:18px;height:18px"></i></span>
        <span class="gc-b">
          <span class="gc-t">{{ $gig->title }}</span>
          <span class="gc-m">
            <span>{{ $gig->poster_name ?? 'Unknown' }}@if(!empty($gig->poster_verified)) &#10003;@endif</span>
            @if(!empty($gig->poster_level['label']))<span>&middot; {{ $gig->poster_level['label'] }}</span>@endif
            @if(!empty($gig->avg_rating))<span>&middot; &#9733; {{ $gig->avg_rating }}</span>@endif
          </span>
          <span class="gc-f">
            <span class="gc-p">₦{{ number_format($gig->amount) }}</span>
            <span class="gc-s {{ $gig->status === 'open' ? '' : 'off' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
          </span>
        </span>
      </a>
    @empty
      <div class="wp-empty"><b>No hustles here yet</b>Try another category, or post the first one.</div>
    @endforelse
  </div>

  @if(method_exists($list, 'hasPages') && $list->hasPages())
    <div class="wp-pages">{{ $list->links() }}</div>
  @endif
</div>
@endsection

@section('scripts')
<script>
if (window.gsap) {
  gsap.from(gsap.utils.toArray('.gc').slice(0, 30), { y: 14, opacity: 0, duration: .45, stagger: .03, ease: 'power2.out', clearProps: 'all' });
}
</script>
@endsection

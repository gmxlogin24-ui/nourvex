@extends('layouts.app-authenticated')

@section('title', 'Hustles')

@section('content')
@php
  $list = isset($gigsPage) ? $gigsPage : $gigs;
  $all = collect($gigs);
  $gfCat = [
    'Tutoring' => ['graduation-cap', '#7C5CF0'], 'Typing & Printing' => ['printer', '#2563EB'],
    'Hair & Beauty' => ['scissors', '#DB2777'], 'Repairs' => ['wrench', '#EA580C'],
    'Laundry' => ['shirt', '#0EA5A4'], 'Errands & Delivery' => ['truck', '#16A34A'],
    'Photography' => ['camera', '#CA8A04'],
  ];
  $cats = collect(array_keys((array) config('gig_categories', [])))->merge(collect($categories ?? []))->filter()->unique()->values();
  $q = trim((string) request('search')); $cat = request('category'); $sort = request('sort');
  $min = request('min_price'); $max = request('max_price');
  $filtered = $q !== '' || $cat || $sort || $min || $max;
  $u = function ($extra = []) {
    $p = array_merge(request()->only(['search', 'category', 'sort', 'min_price', 'max_price']), $extra);
    return route('gigs.index', array_filter($p, fn ($v) => $v !== null && $v !== ''));
  };
  $from = function ($g) {
    $pk = is_array($g->packages) ? $g->packages : [];
    return count($pk) ? min(array_map(fn ($x) => (float) ($x['price'] ?? 0), $pk)) : (float) $g->amount;
  };
  $ago = function ($v) { try { return $v ? \Illuminate\Support\Carbon::parse($v)->diffForHumans() : ''; } catch (\Throwable $e) { return ''; } };
  $open = $all->where('status', 'open');
  $fresh = $open->sortByDesc('created_at')->take(10)->values();
  $top = $open->filter(fn ($g) => !empty($g->avg_rating))->sortByDesc('avg_rating')->take(10)->values();
  $cheap = $open->filter(fn ($g) => $from($g) <= 5000)->sortBy(fn ($g) => $from($g))->take(10)->values();
  $rails = [['Just posted', $fresh], ['Top rated', $top], ['Budget friendly', $cheap]];
@endphp
<link rel="stylesheet" href="{{ asset('css/gig-feed.css') }}?v={{ time() }}">
<div class="gf-feed" id="gfFeed">

  <div class="gf-top">
    <form method="GET" action="{{ route('gigs.index') }}" class="gf-search">
      <i data-lucide="search"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search typing, tutoring, repairs, errands...">
      @if($cat)<input type="hidden" name="category" value="{{ $cat }}">@endif
      @if($sort)<input type="hidden" name="sort" value="{{ $sort }}">@endif
    </form>
  </div>

  <section class="gf-sec">
    <div class="gf-cats">
      @foreach($cats as $c)
        @php $cm = $gfCat[$c] ?? ['briefcase', '#64748B']; @endphp
        <a href="{{ $u(['category' => $c]) }}" class="gf-cat {{ $cat === $c ? 'on' : '' }}"><span style="background:{{ $cm[1] }}"><i data-lucide="{{ $cm[0] }}"></i></span><small>{{ $c }}</small></a>
      @endforeach
    </div>
  </section>

  <div class="gf-chips">
    <a href="{{ $u(['min_price' => null, 'max_price' => null]) }}" class="gf-chip {{ !$min && !$max ? 'on' : '' }}">Any price</a>
    <a href="{{ $u(['min_price' => null, 'max_price' => 5000]) }}" class="gf-chip {{ $max == 5000 && !$min ? 'on' : '' }}">Under ₦5k</a>
    <a href="{{ $u(['min_price' => 5000, 'max_price' => 20000]) }}" class="gf-chip {{ $min == 5000 && $max == 20000 ? 'on' : '' }}">₦5k to 20k</a>
    <a href="{{ $u(['min_price' => 20000, 'max_price' => null]) }}" class="gf-chip {{ $min == 20000 && !$max ? 'on' : '' }}">₦20k+</a>
    <span class="gf-sep"></span>
    <a href="{{ $u(['sort' => null]) }}" class="gf-chip {{ !$sort ? 'on' : '' }}">Best match</a>
    <a href="{{ $u(['sort' => 'newest']) }}" class="gf-chip {{ $sort === 'newest' ? 'on' : '' }}">Newest</a>
    <a href="{{ $u(['sort' => 'highest']) }}" class="gf-chip {{ $sort === 'highest' ? 'on' : '' }}">Highest pay</a>
  </div>

  @unless($filtered)
    @foreach($rails as $r)
      @if($r[1]->count())
        <section class="gf-sec">
          <div class="gf-sh"><b>{{ $r[0] }}</b></div>
          <div class="gf-rail">
            @foreach($r[1] as $gig)@include('gigs.partials.gf-card', ['gig' => $gig, 'gfCat' => $gfCat])@endforeach
          </div>
        </section>
      @endif
    @endforeach
  @endunless

  <section class="gf-sec">
    <div class="gf-sh">
      <b>{{ $q !== '' ? 'Results for “' . $q . '”' : ($cat ?: 'All hustles') }}</b>
      @if($filtered)<a href="{{ route('gigs.index') }}" class="gf-clear">Clear</a>@endif
    </div>
    <p class="gf-n">{{ $all->count() }} {{ $all->count() === 1 ? 'hustle' : 'hustles' }}</p>

    @forelse($list as $gig)
      @php
        $jPk = is_array($gig->packages) ? $gig->packages : [];
        $jM = $gfCat[$gig->category] ?? ['briefcase', '#64748B'];
        $jD = trim((string) ($gig->description ?? ''));
      @endphp
      <a href="{{ route('gigs.show', $gig->id) }}" class="gf-j">
        <span class="gf-jh">
          <span class="gf-jc"><i data-lucide="{{ $jM[0] }}" style="color:{{ $jM[1] }}"></i>{{ $gig->category ?? 'Gig' }}</span>
          <span>{{ $ago($gig->created_at) }}</span>
        </span>
        <b class="gf-jt">{{ $gig->title }}</b>
        @if($jD !== '')<p class="gf-jd">{{ \Illuminate\Support\Str::limit($jD, 130) }}</p>@endif
        <span class="gf-tags">
          <em class="gf-bud">{{ count($jPk) ? 'From ' : '' }}₦{{ number_format($from($gig)) }}</em>
          @if(count($jPk))<em>{{ count($jPk) }} packages</em>@endif
          @if($gig->deadline)<em><i data-lucide="calendar"></i>Due {{ $gig->deadline->format('M j') }}</em>@endif
          @if($gig->location_type)<em>{{ ucfirst($gig->location_type) }}</em>@endif
        </span>
        <span class="gf-jf">
          <span class="gf-jp">{{ $gig->poster_name ?? 'User' }}@if(!empty($gig->poster_verified))<i data-lucide="badge-check"></i>@endif</span>
          @if(!empty($gig->avg_rating))<span class="gf-jr"><i data-lucide="star"></i>{{ $gig->avg_rating }}</span>@endif
          <span class="gf-st {{ $gig->status === 'open' ? 'ok' : '' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
        </span>
      </a>
    @empty
      <div class="gf-empty"><b>No hustles here yet</b>Try another category or price. New hustles appear as people post them.</div>
    @endforelse

    @if(method_exists($list, 'hasPages') && $list->hasPages())
      <div class="gf-pages">{{ $list->links() }}</div>
    @endif
  </section>
</div>
@endsection

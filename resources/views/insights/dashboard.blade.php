@extends('layouts.app-authenticated')
@section('title', 'Your dashboard')
@section('content')
<link rel="stylesheet" href="{{ asset('css/insights.css') }}?v={{ time() }}">
<main class="in">
  <header class="in-hd">
    <a href="{{ $all ? route('insights.dashboard') : route('profile.index') }}" class="in-ic" aria-label="Back"><i data-lucide="arrow-left"></i></a>
    <h1>{{ $all ? 'Your tools' : 'Your dashboard' }}</h1>
    <a href="{{ route('profile.account') }}" class="in-ic" aria-label="Settings"><i data-lucide="settings"></i></a>
  </header>

  @if(!$all)
  <section class="in-sec">
    <div class="in-sh"><h2>Insights</h2><span>{{ $label }}</span></div>
    <a class="in-row" href="{{ route('profile.public', ['user' => auth()->id()]) }}#viewers">
      <span>Views</span>
      <span class="in-val">@if($vt === 'up')<i data-lucide="arrow-up-right" class="up"></i>@elseif($vt === 'down')<i data-lucide="arrow-down-right" class="down"></i>@endif{{ number_format($views) }}<i data-lucide="chevron-right" class="chev"></i></span>
    </a>
    <a class="in-row" href="{{ route('insights.page', 'followers') }}">
      <span>New followers</span>
      <span class="in-val">@if($ft === 'up')<i data-lucide="arrow-up-right" class="up"></i>@elseif($ft === 'down')<i data-lucide="arrow-down-right" class="down"></i>@endif{{ number_format($followers) }}<i data-lucide="chevron-right" class="chev"></i></span>
    </a>
    <a class="in-row" href="{{ route('insights.page', 'content') }}">
      <span>Content you shared</span>
      <span class="in-val">{{ number_format($content) }}<i data-lucide="chevron-right" class="chev"></i></span>
    </a>
  </section>
  @endif

  <section class="in-sec in-sec-b">
    <div class="in-sh"><h2>Your tools</h2>@if(!$all)<a href="{{ route('insights.tools') }}">See all</a>@endif</div>
    @foreach($tools as $t)
    <a class="in-tool" href="{{ route('insights.page', $t['slug']) }}">
      <i data-lucide="{{ $t['icon'] }}" class="ti"></i>
      <span class="in-tx"><b>{{ $t['title'] }}</b>@if($t['sub'])<small>{{ $t['sub'] }}</small>@endif</span>
      @if($t['new'])<em class="in-new">New</em>@endif
      <i data-lucide="chevron-right" class="chev"></i>
    </a>
    @endforeach
  </section>
</main>
<script>if (window.lucide) lucide.createIcons();</script>
@endsection

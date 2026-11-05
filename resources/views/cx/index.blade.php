@extends('layouts.app-authenticated')
@section('title', 'Messages')
@section('content')
<link rel="stylesheet" href="{{ asset('css/cx.css') }}?v={{ time() }}">
<main class="cx" id="cxI" data-csrf="{{ csrf_token() }}" data-base="{{ url('/cx') }}">
  <header class="cx-hd"><h1>Messages</h1><button type="button" id="cxNew" class="cx-ic" aria-label="New chat"><i data-lucide="square-pen"></i></button></header>
  <div class="cx-sr"><i data-lucide="search"></i><input id="cxQ" placeholder="Search chats" autocomplete="off"></div>
  <div id="cxList">
    @forelse($rows as $r)
    <a class="cx-row" href="{{ route('cx.thread', $r['id']) }}" data-name="{{ strtolower($r['peer']['name']) }}">
      <span class="cx-av">@if($r['peer']['avatar'])<img src="{{ $r['peer']['avatar'] }}" alt="">@else{{ strtoupper(mb_substr($r['peer']['name'], 0, 1)) }}@endif @if($r['peer']['online'])<i class="on"></i>@endif</span>
      <span class="cx-mid"><b>{{ $r['peer']['name'] }}</b><small>{{ $r['preview'] }}</small></span>
      <span class="cx-rt"><em class="{{ $r['unread'] ? 'u' : '' }}">{{ $r['time'] }}</em>@if($r['unread'])<span class="cx-bd">{{ $r['unread'] > 99 ? '99+' : $r['unread'] }}</span>@endif</span>
    </a>
    @empty
    <p class="cx-empty">No chats yet. Tap the pencil to start one.</p>
    @endforelse
  </div>
  <div class="cx-sheet" id="cxNS" hidden><div class="cx-bk"></div><div class="cx-pn">
    <h3>New chat</h3><div class="cx-sr"><i data-lucide="search"></i><input id="cxNQ" placeholder="Search people" autocomplete="off"></div><div id="cxNR"></div>
  </div></div>
</main>
<script src="{{ asset('js/cx.js') }}?v={{ time() }}" defer></script>
<script>if (window.lucide) lucide.createIcons();</script>
@endsection

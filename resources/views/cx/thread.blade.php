@extends('layouts.app-authenticated')
@section('title', $peer['name'])
@section('content')
<link rel="stylesheet" href="{{ asset('css/cx.css') }}?v={{ time() }}">
<main class="cxt" id="cxT" data-id="{{ $cid }}" data-base="{{ url('/cx') }}" data-csrf="{{ csrf_token() }}" data-group="{{ $peer['group'] ? 1 : 0 }}">
  <header class="cxt-hd">
    <a href="{{ route('cx.index') }}" class="cx-ic" aria-label="Back"><i data-lucide="arrow-left"></i></a>
    <span class="cx-av sm">@if($peer['avatar'])<img src="{{ $peer['avatar'] }}" alt="">@else{{ strtoupper(mb_substr($peer['name'], 0, 1)) }}@endif</span>
    <div class="cxt-who"><b>{{ $peer['name'] }}</b><small id="cxSt">{{ $peer['status'] }}</small></div>
    <button type="button" id="cxSB" class="cx-ic" aria-label="Search"><i data-lucide="search"></i></button>
  </header>
  <div class="cxt-sr" id="cxSR" hidden><input id="cxSI" placeholder="Search in this chat" autocomplete="off"></div>
  <div class="cxt-list" id="cxL"></div>
  <div class="cxt-bar" id="cxBar" hidden><span id="cxBarT"></span><button type="button" id="cxBarX" aria-label="Cancel">&times;</button></div>
  <div class="cxt-in" id="cxIn">
    <label class="cx-ic" aria-label="Attach"><i data-lucide="paperclip"></i><input type="file" id="cxF" hidden></label>
    <textarea id="cxTx" rows="1" placeholder="Message" maxlength="4000"></textarea>
    <button type="button" id="cxGo" class="cx-go" aria-label="Send"></button>
  </div>
  <div class="cxt-rec" id="cxRec" hidden><button type="button" id="cxRX" class="cx-ic" aria-label="Cancel">&#128465;</button><span><i class="dot"></i><b id="cxRT">0:00</b> Recording…</span><button type="button" id="cxRS" class="cx-go" aria-label="Send voice note">&#10148;</button></div>
  <div class="cx-sheet" id="cxSh" hidden></div>
</main>
<script src="{{ asset('js/cx.js') }}?v={{ time() }}" defer></script>
<script>if (window.lucide) lucide.createIcons();</script>
@endsection

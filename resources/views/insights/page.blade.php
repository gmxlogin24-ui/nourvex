@extends('layouts.app-authenticated')
@section('title', $title)
@section('content')
<link rel="stylesheet" href="{{ asset('css/insights.css') }}?v={{ time() }}">
<main class="in">
  <header class="in-hd">
    <a href="{{ route('insights.dashboard') }}" class="in-ic" aria-label="Back"><i data-lucide="arrow-left"></i></a>
    <h1>{{ $title }}</h1>
    <span class="in-ic ghost"></span>
  </header>

  @if($slug === 'monthly-recap')
    <p class="in-note">{{ $month }}</p>
    <div class="in-stats">
      <div><b>{{ number_format($recap['views']) }}</b><span>Profile views</span></div>
      <div><b>{{ number_format($recap['followers']) }}</b><span>New followers</span></div>
      <div><b>{{ number_format($recap['content']) }}</b><span>Things you shared</span></div>
    </div>

  @elseif($slug === 'best-practices')
    @foreach($tips as $t)<div class="in-card"><b>{{ $t[0] }}</b><p>{{ $t[1] }}</p></div>@endforeach

  @elseif($slug === 'inspiration')
    @foreach($ideas as $t)<div class="in-card"><b>{{ $t[0] }}</b><p>{{ $t[1] }}</p></div>@endforeach

  @elseif($slug === 'ad-tools')
    <div class="in-stats two">
      <div><b>{{ number_format($tv) }}</b><span>Post views</span></div>
      <div><b>{{ number_format($tc) }}</b><span>Link clicks</span></div>
    </div>
    @forelse($posts as $p)
      <div class="in-card"><b>{{ $p->title }}</b><p>{{ number_format($p->views ?? 0) }} views · {{ number_format($p->clicks ?? 0) }} clicks · {{ ($p->type ?? 'news') === 'ad' ? 'Offer' : 'News' }}</p></div>
    @empty
      <p class="in-empty">You have not posted to Pulse yet. Use "+ Post" on the dashboard and your results will show here.</p>
    @endforelse

  @elseif($slug === 'trial-reels')
    <div class="in-empty big"><i data-lucide="play-square"></i><p>Reels are not part of the app yet. This page is ready for when they arrive.</p></div>

  @elseif($slug === 'saved-replies')
    <form method="POST" action="{{ route('insights.replies.store') }}" class="in-form">
      @csrf
      <textarea name="body" maxlength="500" rows="3" placeholder="Write a reply you use often..." required></textarea>
      <button type="submit">Save reply</button>
    </form>
    @forelse($replies as $r)
      <div class="in-card in-rep">
        <p>{{ $r->body }}</p>
        <form method="POST" action="{{ route('insights.replies.destroy', $r->id) }}">@csrf @method('DELETE')<button type="submit" aria-label="Delete"><i data-lucide="trash-2"></i></button></form>
      </div>
    @empty
      <p class="in-empty">No saved replies yet.</p>
    @endforelse

  @elseif($slug === 'followers')
    @forelse($people as $p)
      <a class="in-tool" href="{{ route('profile.public', ['user' => $p->id]) }}">
        <span class="in-av">@if($p->avatar_url)<img src="{{ $p->avatar_url }}" alt="">@else{{ strtoupper(substr($p->username ?: $p->name, 0, 1)) }}@endif</span>
        <span class="in-tx"><b>{{ $p->username ? '@' . $p->username : $p->name }}</b></span>
        <i data-lucide="chevron-right" class="chev"></i>
      </a>
    @empty
      <p class="in-empty">No followers yet. Profiles have no Follow button yet, so this fills up once one is added.</p>
    @endforelse

  @elseif($slug === 'content')
    @forelse($items as $i)
      <a class="in-tool" href="{{ $i['url'] }}">
        <i data-lucide="{{ $i['icon'] }}" class="ti"></i>
        <span class="in-tx"><b>{{ $i['title'] }}</b><small>{{ $i['kind'] }}@if($i['at']) · {{ $i['at'] }}@endif</small></span>
        <i data-lucide="chevron-right" class="chev"></i>
      </a>
    @empty
      <p class="in-empty">You have not shared anything yet.</p>
    @endforelse
  @endif
</main>
<script>if (window.lucide) lucide.createIcons();</script>
@endsection

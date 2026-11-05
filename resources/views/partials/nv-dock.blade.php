@php
  if (empty($nvItems)) {
    $nvR = fn ($name, $fb = '#') => \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fb;
    $nvUnread = (int) ($navUnreadChats ?? 0);
    $nvWU = auth()->check() ? \App\Http\Controllers\InboxController::unreadByWorld() : [];
    $nvCartN = count(session('cart', []));
    if (request()->routeIs('profile.*', 'leaderboard.*', 'inbox.profile')) {
      $nvItems = [
        ['home', 'Home', $nvR('dashboard'), false, 0],
        ['message-circle', 'Message', $nvR('inbox.profile'), request()->routeIs('inbox.profile'), $nvWU['profile'] ?? 0],
        ['plus-circle', 'Post', $nvR('posts.index'), false, 0, 'pfnCreate'],
        ['briefcase', 'Jobs', $nvR('gigs.index'), false, 0],
        ['trophy', 'Ranking', $nvR('leaderboard.index'), request()->routeIs('leaderboard.*'), 0],
      ];
    } elseif (request()->routeIs('gigs.*', 'posts.*', 'inbox.hustles')) {
      $nvItems = [
        ['home', 'Home', $nvR('dashboard'), false, 0],
        ['briefcase', 'Jobs', $nvR('gigs.index'), request()->routeIs('gigs.index', 'gigs.show', 'gigs.edit'), 0],
        ['plus-circle', 'Post', $nvR('gigs.create'), request()->routeIs('gigs.create'), 0],
        ['layers', 'My posts', $nvR('posts.index'), request()->routeIs('posts.*'), 0],
        ['message-circle', 'Messages', $nvR('inbox.hustles'), request()->routeIs('inbox.hustles'), $nvWU['hustles'] ?? 0],
      ];
    } elseif (request()->routeIs('marketplace.*', 'cart.*', 'inbox.market')) {
      $nvItems = [
        ['home', 'Home', $nvR('dashboard'), false, 0],
        ['shopping-bag', 'Shop', $nvR('marketplace.index'), request()->routeIs('marketplace.index', 'marketplace.show', 'marketplace.edit'), 0],
        ['plus-circle', 'Post', $nvR('marketplace.create'), request()->routeIs('marketplace.create'), 0],
        ['message-circle', 'Messages', $nvR('inbox.market'), request()->routeIs('inbox.market'), $nvWU['market'] ?? 0],
        ['shopping-cart', 'Cart', $nvR('cart.index'), request()->routeIs('cart.*'), $nvCartN],
      ];
    }
  }
@endphp
@php
  $nvMap = ["dashboard" => "overview", "marketplace.*" => "market", "gigs.*" => "hustles", "games.*" => "games", "golfvex*" => "games", "wallet.*" => "assets", "assets.*" => "assets"];
  $nvActive = '';
  foreach ($nvMap as $nvRoute => $nvKey) { if (request()->routeIs($nvRoute)) { $nvActive = $nvKey; break; } }
  $nvLensMap = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='r' x1='0' x2='1' y1='0' y2='0'><stop offset='0' stop-color='rgb(255,0,0)'/><stop offset='.14' stop-color='rgb(128,0,0)'/><stop offset='.86' stop-color='rgb(128,0,0)'/><stop offset='1' stop-color='rgb(0,0,0)'/></linearGradient><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,255,0)'/><stop offset='.3' stop-color='rgb(0,128,0)'/><stop offset='.7' stop-color='rgb(0,128,0)'/><stop offset='1' stop-color='rgb(0,0,0)'/></linearGradient></defs><rect width='100' height='100' fill='url(#r)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <filter id="nvLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
    <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($nvLensMap) }}" preserveAspectRatio="none" result="m"/>
    <feDisplacementMap in="SourceGraphic" in2="m" scale="38" xChannelSelector="R" yChannelSelector="G"/>
  </filter>
</svg>
<div class="nv-dock-fixed" id="nvDock">
  @if(!empty($nvItems))
  <link rel="stylesheet" href="{{ asset('css/ov-dock.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset("css/dock-solid.css") }}?v={{ time() }}">
  <nav class="ovd" aria-label="World">
    @foreach($nvItems as $n)
    <a href="{{ $n[2] }}" class="ovd-btn {{ $n[3] ? 'on' : '' }}" @if(!empty($n[5])) data-dock-sheet="{{ $n[5] }}" @endif aria-label="{{ $n[1] }}" title="{{ $n[1] }}">
      <span class="ovd-tile"><span class="ovd-in"><i data-lucide="{{ $n[0] }}"></i></span>@if(!empty($n[4]))<em class="nv-badge">{{ $n[4] }}</em>@endif</span>
      <span class="ovd-lbl">{{ $n[1] }}</span>
    </a>
    @endforeach
  </nav>
  @else
  @include('partials.ov-dock', ['ovDockActive' => $nvActive])
  @endif
</div>
<script>
(function () {
  var d = document.getElementById('nvDock');
  if (d && d.parentNode !== document.body) { document.body.appendChild(d); }
  var ua = navigator.userAgent;
  if (/Chrome\//.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua)) { document.documentElement.classList.add('nv-lens'); }
  document.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('[data-dock-sheet]');
        if (!a) return;
        var b = document.querySelector('button[data-sheet="' + a.getAttribute('data-dock-sheet') + '"]');
        if (b) { e.preventDefault(); b.click(); }
    });
    if (window.lucide) { lucide.createIcons(); }
})();
</script>

<link rel="stylesheet" href="{{ asset('css/merged-bar.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/site-scale.css') }}?v={{ time() }}">
<script src="{{ asset('js/merged-bar.js') }}?v={{ time() }}" defer></script>
<script src="{{ asset('js/mb-curve.js') }}?v={{ time() }}" defer></script>
{{-- MB-STYLE-FALLBACK: pages that do not set the dock style themselves still get a merged style --}}
<script>(function(){var s='{{ auth()->check() ? (auth()->user()->mobile_nav_style ?? "painted_pill") : "" }}';if(s.indexOf('merged_')===0&&!document.body.getAttribute('data-mobile-nav-style')){document.body.setAttribute('data-mobile-nav-style',s);}})();</script>

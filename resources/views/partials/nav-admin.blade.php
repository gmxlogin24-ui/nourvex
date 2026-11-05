@php
  $has = fn ($n) => \Illuminate\Support\Facades\Route::has($n);
  $u = auth()->user();
  $cc = $u?->role === 'customer_care' && !$u?->is_admin;
  if ($cc) {
    $G = ['Care' => [['customer-care.index','Overview','layout-dashboard'],['customer-care.orders','Orders','package'],['customer-care.gig-disputes','Gig disputes','scale'],['customer-care.listing-disputes','Market disputes','shield-alert'],['customer-care.user-lookup','Lookup','search']]];
  } else {
    $G = [
      'Core' => [['admin.index','Command','radar'],['admin.users','Users','users']],
      'Money' => [['admin.transactions','Money','arrow-left-right'],['admin.coin','Coin Lab','coins'],['admin.revenue','Revenue','trending-up'],['admin.profit.index','Profit','piggy-bank'],['admin.investment.index','Invest','landmark'],['admin.subscriptions','Subs','credit-card']],
      'Market' => [['admin.gigs','Gigs','briefcase'],['admin.listings','Listings','shopping-bag'],['admin.disputes.index','Gig disputes','scale'],['admin.marketplace-disputes.index','Market disputes','shield-alert']],
      'Community' => [['admin.conversations.index','Chats','messages-square'],['admin.announcements.index','Alerts','megaphone'],['admin.news','News','newspaper'],['admin.blog','Blog','file-text'],['admin.invites','Invites','mail-plus']],
      'System' => [['admin.levels.index','Levels','layers'],['admin.badges.index','Badges','award'],['admin.feature-gates.index','Features','toggle-left'],['admin.grant-access','Access','key'],['admin.settings.appearance','Appearance','paintbrush'],['admin.audit','Audit log','shield-check']],
    ];
  }
  $all = [];
  foreach ($G as $gn => $items) {
    $keep = array_values(array_filter($items, fn ($i) => $has($i[0])));
    if ($keep) { $G[$gn] = $keep; foreach ($keep as $k) { $all[] = $k; } } else { unset($G[$gn]); }
  }
  $nDock = $cc ? 5 : 4;
  $isOn = fn ($n) => $activeRoute === $n || (!in_array($n, ['admin.index','customer-care.index']) && str_starts_with((string) $activeRoute, preg_replace('/\.index$/', '', $n)));
  $axLens = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' preserveAspectRatio='none'><defs><linearGradient id='r' x1='0' x2='1' y1='0' y2='0'><stop offset='0' stop-color='rgb(255,0,0)'/><stop offset='.14' stop-color='rgb(128,0,0)'/><stop offset='.86' stop-color='rgb(128,0,0)'/><stop offset='1' stop-color='rgb(0,0,0)'/></linearGradient><linearGradient id='g' x1='0' x2='0' y1='0' y2='1'><stop offset='0' stop-color='rgb(0,255,0)'/><stop offset='.3' stop-color='rgb(0,128,0)'/><stop offset='.7' stop-color='rgb(0,128,0)'/><stop offset='1' stop-color='rgb(0,0,0)'/></linearGradient></defs><rect width='100' height='100' fill='url(#r)'/><rect width='100' height='100' fill='url(#g)' style='mix-blend-mode:screen'/></svg>";
@endphp
<link rel="stylesheet" href="{{ asset('css/admin-x.css') }}?v={{ time() }}">
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <filter id="axLens" x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB">
    <feImage href="data:image/svg+xml;utf8,{{ rawurlencode($axLens) }}" preserveAspectRatio="none" result="m"/>
    <feDisplacementMap in="SourceGraphic" in2="m" scale="38" xChannelSelector="R" yChannelSelector="G"/>
  </filter>
</svg>
<header class="ax-top">
  <a class="ax-logo" href="{{ route($all[0][0]) }}"><span class="ax-mark"></span><b>NAURVEX</b><em>{{ $cc ? 'CARE' : 'ADMIN' }}</em></a>
  <span class="ax-title">{{ trim($__env->yieldContent('title')) }}</span>
  <span class="ax-live"><i></i>Secure</span>
  <a class="ax-exit" href="{{ route('dashboard') }}" aria-label="Exit admin"><i data-lucide="log-out"></i></a>
</header>
<aside class="ax-rail" id="axRail" aria-label="Admin">
  <button type="button" class="ax-col" id="axCol" aria-label="Collapse"><i data-lucide="panel-left-close"></i><span>Collapse</span></button>
  @foreach($G as $gn => $items)
    <div class="ax-gh">{{ $gn }}</div>
    @foreach($items as $i)
      <a href="{{ route($i[0]) }}" class="{{ $isOn($i[0]) ? 'on' : '' }}" title="{{ $i[1] }}"><i data-lucide="{{ $i[2] }}"></i><span>{{ $i[1] }}</span></a>
    @endforeach
  @endforeach
</aside>
<nav class="axd" aria-label="Admin">
  @foreach(array_slice($all, 0, $nDock) as $i)
    <a href="{{ route($i[0]) }}" class="{{ $isOn($i[0]) ? 'on' : '' }}" aria-label="{{ $i[1] }}"><span class="axd-t"><span class="axd-c"><i data-lucide="{{ $i[2] }}"></i></span></span><span class="axd-l">{{ $i[1] }}</span></a>
  @endforeach
  @if(count($all) > $nDock)<a href="#" data-ax-more aria-label="More"><span class="axd-t"><span class="axd-c"><i data-lucide="layout-grid"></i></span></span><span class="axd-l">More</span></a>@endif
</nav>
<div class="ax-sheet" id="axSheet" hidden>
  <div class="ax-bk"></div>
  <div class="ax-pn">
    @foreach($all as $i)
      <a href="{{ route($i[0]) }}" class="ax-tile {{ $isOn($i[0]) ? 'on' : '' }}"><i data-lucide="{{ $i[2] }}"></i><span>{{ $i[1] }}</span></a>
    @endforeach
  </div>
</div>
<script>
(function () {
  document.documentElement.setAttribute('data-theme', 'light');
  function sn() { document.body.setAttribute('data-admin-nav', innerWidth > 900 ? 'topbar' : 'ax'); } sn(); window.addEventListener('resize', sn);
  var ua = navigator.userAgent;
  if (/Chrome\//.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua)) { document.documentElement.classList.add('ax-lens'); }
  try { if (localStorage.getItem('axCol') === '1') document.body.classList.add('ax-collapsed'); } catch (e) {}
  var s = document.getElementById('axSheet');
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-ax-more]')) { e.preventDefault(); s.hidden = !s.hidden; return; }
    if (e.target.classList.contains('ax-bk')) { s.hidden = true; return; }
    if (e.target.closest('#axCol')) {
      var c = document.body.classList.toggle('ax-collapsed');
      try { localStorage.setItem('axCol', c ? '1' : '0'); } catch (x) {}
      window.dispatchEvent(new Event('resize'));
    }
  });
  var t;
  function arm() { clearTimeout(t); t = setTimeout(function () { location.reload(); }, 905000); }
  ['click', 'keydown', 'touchstart', 'scroll'].forEach(function (n) { document.addEventListener(n, arm, { passive: true }); });
  arm();
})();
</script>
@include('partials.nav-admin-classic')
<div class="admin-edge-logo"><div class="logo-badge"></div></div>
<div class="admin-edge-selected" id="admin-topbar-selected-user"><span class="admin-topbar-selected-avatar">—</span></div>

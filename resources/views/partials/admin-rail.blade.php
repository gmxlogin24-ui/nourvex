@php
  $arItems = [
    ['Overview','admin.index','layout-dashboard'],['Users','admin.users','users'],['Transactions','admin.transactions','arrow-left-right'],
    ['Subscriptions','admin.subscriptions','credit-card'],['Gigs','admin.gigs','briefcase'],['Listings','admin.listings','store'],
    ['Disputes','admin.disputes.index','shield-alert'],['Revenue','admin.revenue','bar-chart-3'],['Coin Lab','admin.coin','coins'],
    ['Audit','admin.audit','shield-check'],['Blog','admin.blog','file-text'],['News','admin.news','newspaper'],
    ['Invites','admin.invites','mail'],['Announcements','admin.announcements.index','megaphone'],['Map','admin.map','map'],
  ];
  $arMe = auth()->user();
@endphp
<button type="button" id="arM" class="ar-m" aria-label="Menu"><i data-lucide="menu"></i></button>
<div id="arBk"></div>
<aside id="arR" class="ar">
  <a class="ar-logo" href="{{ Route::has('admin.index') ? route('admin.index') : url('/admin') }}"><i data-lucide="layout-dashboard"></i></a>
  <nav>
    @foreach($arItems as $it)
      @if(Route::has($it[1]))
      <a href="{{ route($it[1]) }}" class="{{ url()->current() === route($it[1]) ? 'on' : '' }}"><i data-lucide="{{ $it[2] }}"></i><span>{{ $it[0] }}</span></a>
      @endif
    @endforeach
    <a href="#" id="arS"><i data-lucide="search"></i><span>Search</span></a>
    <a href="{{ url('/dashboard') }}"><i data-lucide="log-out"></i><span>Exit</span></a>
  </nav>
  <div class="ar-me"><i>{{ strtoupper(substr($arMe->name ?? 'A', 0, 1)) }}</i><b>{{ \Illuminate\Support\Str::limit($arMe->name ?? 'Admin', 12) }}</b></div>
</aside>
<script>
(function () {
  var b = document.body;
  function t(o) { b.classList.toggle('ar-open', o); }
  var m = document.getElementById('arM'), k = document.getElementById('arBk'), s = document.getElementById('arS');
  if (m) m.addEventListener('click', function () { t(!b.classList.contains('ar-open')); });
  if (k) k.addEventListener('click', function () { t(false); });
  if (s) s.addEventListener('click', function (e) { e.preventDefault(); t(false); var x = document.getElementById('axK'); if (x) x.click(); });
  if (window.lucide) lucide.createIcons();
})();
</script>

@php
    $cartCount = count(session('cart', []));
    $avatarUrl = auth()->user()?->avatar_url;
    $userName = auth()->user()?->name ?? 'Guest';
    $initial = strtoupper(substr($userName, 0, 1));
    $isAdmin = auth()->user()?->is_admin ?? false;
@endphp
<aside class="neu-sidebar" id="appSidebar">
    <div class="sidebar-icon-rail">
        <div class="rail-label-spacer"></div>
        <div class="sidebar-avatar sidebar-avatar-mini">
            @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
        </div>
        <a href="{{ route('dashboard') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}" title="Home"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></a>
        <a href="{{ route('gigs.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'gigs' ? 'active' : '' }}" title="Gigs"><i data-lucide="briefcase" class="w-4 h-4"></i></a>
        <a href="{{ route('marketplace.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}" title="Market">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            @if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif
        </a>
        <a href="{{ route('profile.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'profile' ? 'active' : '' }}" title="Profile"><i data-lucide="user-circle" class="w-4 h-4"></i></a>
        @if($isAdmin)
        <a href="{{ route('admin.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'admin' ? 'active' : '' }}" title="Admin"><i data-lucide="shield" class="w-4 h-4"></i></a>
        @endif
    </div>

    <div class="sidebar-content-panel">
        <div class="sidebar-top">
            <div class="sidebar-logo-row">
                <div class="logo-badge sidebar-logo-icon" data-face-cursor></div>
                <span class="sidebar-brand-label">Campulsy</span>
            </div>
            <button class="sidebar-collapse-btn" id="sidebarToggle" aria-label="Collapse sidebar" type="button">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="neu-user-card">
            <div class="sidebar-avatar">
                @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
            </div>
            <div class="sidebar-user-meta">
                <p class="sidebar-user-greeting">Good day</p>
                <p class="sidebar-user-name">{{ $userName }}</p>
            </div>
        </div>

        <p class="sidebar-group-label">Menu</p>
        <nav class="neu-sidebar-nav">
            <a href="{{ route('dashboard') }}" class="{{ ($active ?? '') == 'dashboard' ? 'active' : '' }}"><span class="sidebar-link-label">Home</span></a>
            <a href="{{ route('gigs.index') }}" class="{{ ($active ?? '') == 'gigs' ? 'active' : '' }}"><span class="sidebar-link-label">Gigs</span></a>
            <a href="{{ route('marketplace.index') }}" class="{{ ($active ?? '') == 'marketplace' ? 'active' : '' }}"><span class="sidebar-link-label">Market</span></a>
            <a href="{{ route('profile.index') }}" class="{{ ($active ?? '') == 'profile' ? 'active' : '' }}"><span class="sidebar-link-label">Profile</span></a>
            @if($isAdmin)
            <a href="{{ route('admin.index') }}" class="{{ ($active ?? '') == 'admin' ? 'active' : '' }}"><span class="sidebar-link-label">Admin</span></a>
            @endif
            <button type="button" class="sidebar-theme-btn-inline"><span class="sidebar-link-label">Theme</span></button>
            @auth
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="sidebar-logout-item"><span class="sidebar-link-label">Log Out</span></button>
            </form>
            @else
            <a href="{{ route('login') }}" class="sidebar-logout-item"><span class="sidebar-link-label">Log In</span></a>
            @endauth
        </nav>

        <a href="{{ route('gigs.create') }}" class="sidebar-fab" title="Post a Gig">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span class="sidebar-fab-label">Post a Gig</span>
        </a>
    </div>
</aside>

<nav class="mobile-auth-topbar-pill" id="mobileTopbarPill">
    <button class="pill-burger" id="gnBurger" aria-label="Menu" type="button">
        <span></span><span></span><span></span>
    </button>
    <a href="{{ route('dashboard') }}" class="pill-brand">
        <div class="logo-badge" style="width:1.6rem;height:1.6rem;"></div>
        <span>Campulsy</span>
    </a>
    <div class="pill-avatar">
        @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
    </div>
</nav>

<div class="gn-backdrop" id="gnBackdrop"></div>
<div id="mobileNavDrawer" class="gn-drawer">
    <div class="gn-profile-card">
        <div class="gn-avatar-ring">
            @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
        </div>
        <div class="gn-profile-meta">
            <p class="gn-profile-name">{{ $userName }}</p>
            <a href="{{ route('profile.index') }}" class="gn-profile-link">View profile</a>
        </div>
    </div>
    <nav class="gn-item-list">
        <a href="{{ route('dashboard') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></span><span>Home</span></a>
        <a href="{{ route('gigs.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span>Gigs</span></a>
        <a href="{{ route('marketplace.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span>Marketplace</span>@if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif</a>
        <a href="{{ route('profile.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="user-circle" class="w-4 h-4"></i></span><span>Profile</span></a>
        @if($isAdmin)
        <a href="{{ route('admin.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shield" class="w-4 h-4"></i></span><span>Admin</span></a>
        @endif
    </nav>
    <div class="gn-divider"></div>
    <button type="button" class="gn-item gn-theme-btn-inline"><span class="gn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
    @auth
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="gn-item gn-item-danger" style="width:100%;border:none;background:none;"><span class="gn-item-icon"><i data-lucide="log-out" class="w-4 h-4"></i></span><span>Log Out</span></button>
    </form>
    @else
    <a href="{{ route('login') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="log-in" class="w-4 h-4"></i></span><span>Log In</span></a>
    @endauth
</div>

<script>
(function () {
  function initNav() {
    const burger = document.getElementById('gnBurger');
    const drawer = document.getElementById('mobileNavDrawer');
    const topbarPill = document.getElementById('mobileTopbarPill');
    if (!burger) return;

    let isOpen = false;

    function setOpen(opening) {
      isOpen = opening;
      const shell = document.querySelector('.app-shell-with-sidebar');
      burger.classList.toggle('gn-open', opening);
      document.body.classList.toggle('gn-lock', opening);
      document.documentElement.classList.toggle('gn-lock-html', opening);
      if (topbarPill) topbarPill.classList.toggle('pill-hidden', opening);

      if (!shell || window.innerWidth > 900) return;

      if (opening) {
        shell.style.transition = 'none';
        shell.style.position = 'fixed';
        shell.style.top = '30px';
        shell.style.bottom = '30px';
        shell.style.right = '30px';
        shell.style.left = 'auto';
        shell.style.width = '22vw';
        shell.style.minWidth = '190px';
        shell.style.borderRadius = '26px';
        shell.style.overflowY = 'auto';
        shell.style.overflowX = 'hidden';
        shell.style.webkitOverflowScrolling = 'touch';
        shell.style.boxShadow = '-6px 0 24px rgba(0,0,0,0.35)';
        shell.style.zIndex = '2';
        shell.style.transform = 'translateX(140%)';
        shell.style.opacity = '0';
        void shell.offsetWidth;
        shell.style.transition = 'transform 0.9s cubic-bezier(0.65,0,0.35,1), opacity 0.9s ease';
        shell.style.transform = 'translateX(0)';
        shell.style.opacity = '1';
      } else {
        shell.style.transition = 'transform 0.9s cubic-bezier(0.65,0,0.35,1), opacity 0.9s ease';
        shell.style.transform = 'translateX(140%)';
        shell.style.opacity = '0';
        setTimeout(() => {
          shell.style.position = '';
          shell.style.top = '';
          shell.style.bottom = '';
          shell.style.right = '';
          shell.style.left = '';
          shell.style.width = '';
          shell.style.minWidth = '';
          shell.style.transform = '';
          shell.style.opacity = '';
          shell.style.transition = '';
          shell.style.borderRadius = '';
          shell.style.overflowY = '';
          shell.style.overflowX = '';
          shell.style.boxShadow = '';
          shell.style.zIndex = '';
        }, 900);
      }
    }

    burger.addEventListener('click', () => setOpen(!isOpen));

    document.querySelectorAll('.gn-item, .gn-cta').forEach(el => {
      el.addEventListener('click', () => setOpen(false));
    });

    document.querySelectorAll('.gn-theme-btn-inline, .sidebar-theme-btn-inline').forEach(btn => {
      btn.addEventListener('click', function () {
        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNav);
  } else {
    initNav();
  }
})();
</script>

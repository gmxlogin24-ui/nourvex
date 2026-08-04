@php
$showSidebar = auth()->check() && !($forceTopbar ?? false);
$cartCount = auth()->check() ? count(session('cart', [])) : 0;
$avatarUrl = auth()->check() ? auth()->user()->avatar_url : null;
$initial = auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : '';
@endphp
<link rel="stylesheet" href="{{ asset('css/guest-nav.css') }}?v={{ time() }}">

@if(!$showSidebar)
<nav class="guest-topbar">
    <div class="max-w-6xl mx-auto flex justify-between items-center px-4 py-3">
        <a href="{{ auth()->check() ? route('dashboard') : route('welcome') }}" class="flex items-center gap-2" style="text-decoration:none;color:inherit;">
            <div class="logo-badge" style="width:2rem;height:2rem;"></div>
            <span class="font-semibold">Campulsy</span>
        </a>
        <div class="gn-desktop-only" style="gap:28px;align-items:center;">
            @auth
                <a href="{{ route('dashboard') }}" style="font-size:14px;">Dashboard</a>
                <a href="{{ route('gigs.index') }}" style="font-size:14px;">Gigs</a>
                <a href="{{ route('marketplace.index') }}" style="font-size:14px;">Marketplace</a>
                <a href="{{ route('profile.index') }}" style="font-size:14px;">Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-secondary text-sm">Log Out</button>
                </form>
            @else
                <a href="{{ route('welcome') }}" style="font-size:14px;">Home</a>
                <a href="{{ route('gigs.index') }}" style="font-size:14px;">Gigs</a>
                <a href="{{ route('marketplace.index') }}" style="font-size:14px;">Marketplace</a>
                <a href="{{ route('pulse.index') }}" style="font-size:14px;">Pulse</a>
                <a href="{{ route('features') }}" style="font-size:14px;">Features</a>
                <a href="{{ route('login') }}" style="font-size:14px;">Log In</a>
                <a href="{{ route('register') }}" class="btn-primary text-sm">Get Started</a>
            @endauth
        </div>
        <button class="gn-burger gn-mobile-only" id="gnBurger" aria-label="Menu" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="gn-backdrop" id="gnBackdrop"></div>
<div id="mobileNavDrawer" class="gn-drawer">
    @auth
        <div class="gn-profile-card">
            <div class="gn-avatar-ring">
                @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
            </div>
            <div class="gn-profile-meta">
                <p class="gn-profile-name">{{ auth()->user()->name }}</p>
                <a href="{{ route('profile.index') }}" class="gn-profile-link">View profile</a>
            </div>
        </div>
        <nav class="gn-item-list">
            <a href="{{ route('dashboard') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></span><span>Dashboard</span></a>
            <a href="{{ route('gigs.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span>Gigs</span></a>
            <a href="{{ route('marketplace.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span>Marketplace</span>@if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif</a>
            <a href="{{ route('profile.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="user-circle" class="w-4 h-4"></i></span><span>Profile</span></a>
            @if(auth()->user()->is_admin ?? false)
            <a href="{{ route('admin.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shield" class="w-4 h-4"></i></span><span>Admin</span></a>
            @endif
        </nav>
        <div class="gn-divider"></div>
        <button type="button" class="gn-item gn-theme-btn-inline"><span class="gn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="gn-item gn-item-danger" style="width:100%;border:none;background:none;"><span class="gn-item-icon"><i data-lucide="log-out" class="w-4 h-4"></i></span><span>Log Out</span></button>
        </form>
    @else
        <div class="gn-drawer-heading">Menu</div>
        <nav class="gn-item-list">
            <a href="{{ route('welcome') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="home" class="w-4 h-4"></i></span><span>Home</span></a>
            <a href="{{ route('gigs.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span>Gigs</span></a>
            <a href="{{ route('marketplace.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span>Marketplace</span></a>
            <a href="{{ route('pulse.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="activity" class="w-4 h-4"></i></span><span>Pulse</span></a>
            <a href="{{ route('features') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="sparkles" class="w-4 h-4"></i></span><span>Features</span></a>
        </nav>
        <div class="gn-divider"></div>
        <a href="{{ route('login') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="log-in" class="w-4 h-4"></i></span><span>Log In</span></a>
        <a href="{{ route('register') }}" class="gn-cta"><i data-lucide="user-plus" class="w-4 h-4"></i> Get Started</a>
        <button type="button" class="gn-item gn-theme-btn-inline" style="margin-top:auto;"><span class="gn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
    @endauth
</div>

@else
<aside class="app-sidebar-single" id="appSidebar">
    <div class="sidebar-top">
        <div class="sidebar-logo-row">
            <div class="logo-badge sidebar-logo-icon"></div>
            <span class="sidebar-brand-label">Campulsy</span>
        </div>
        <button class="sidebar-collapse-btn" id="sidebarToggle" aria-label="Collapse sidebar" type="button">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>
    </div>
    <div class="sidebar-user-card">
        <div class="sidebar-avatar">
            @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
        </div>
        <div class="sidebar-user-meta">
            <p class="sidebar-user-greeting">Good day</p>
            <p class="sidebar-user-name">{{ auth()->user()->name }}</p>
        </div>
    </div>
    <nav class="sidebar-nav-rail">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></span>
            <span class="sidebar-link-label">Home</span>
        </a>
        <a href="{{ route('gigs.index') }}" class="sidebar-link {{ ($active ?? '') == 'gigs' ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span>
            <span class="sidebar-link-label">Gigs</span>
        </a>
        <a href="{{ route('marketplace.index') }}" class="sidebar-link {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}">
            <span class="sidebar-link-icon">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                @if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif
            </span>
            <span class="sidebar-link-label">Market</span>
        </a>
        <a href="{{ route('profile.index') }}" class="sidebar-link {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i data-lucide="user-circle" class="w-4 h-4"></i></span>
            <span class="sidebar-link-label">Profile</span>
        </a>
        @if(auth()->user() && auth()->user()->is_admin)
        <a href="{{ route('admin.index') }}" class="sidebar-link {{ ($active ?? '') == 'admin' ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i data-lucide="shield" class="w-4 h-4"></i></span>
            <span class="sidebar-link-label">Admin</span>
        </a>
        @endif
    </nav>
    <div class="sidebar-bottom">
        <button type="button" class="sidebar-link sidebar-link-action sidebar-theme-btn-inline">
            <span class="sidebar-link-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span>
            <span class="sidebar-link-label">Theme</span>
        </button>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="sidebar-link sidebar-link-action">
                <span class="sidebar-link-icon"><i data-lucide="log-out" class="w-4 h-4"></i></span>
                <span class="sidebar-link-label">Log Out</span>
            </button>
        </form>
    </div>
</aside>

<nav class="guest-topbar mobile-auth-topbar">
    <div class="max-w-6xl mx-auto flex justify-between items-center px-4 py-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2" style="text-decoration:none;color:inherit;">
            <div class="logo-badge" style="width:2rem;height:2rem;"></div>
            <span class="font-semibold">Campulsy</span>
        </a>
        <button class="gn-burger gn-mobile-only" id="gnBurger" aria-label="Menu" type="button">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="gn-backdrop" id="gnBackdrop"></div>
<div id="mobileNavDrawer" class="gn-drawer">
    <div class="gn-profile-card">
        <div class="gn-avatar-ring">
            @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
        </div>
        <div class="gn-profile-meta">
            <p class="gn-profile-name">{{ auth()->user()->name ?? '' }}</p>
            <a href="{{ route('profile.index') }}" class="gn-profile-link">View profile</a>
        </div>
    </div>
    <nav class="gn-item-list">
        <a href="{{ route('dashboard') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></span><span>Home</span></a>
        <a href="{{ route('gigs.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span>Gigs</span></a>
        <a href="{{ route('marketplace.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span>Marketplace</span>@if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif</a>
        <a href="{{ route('profile.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="user-circle" class="w-4 h-4"></i></span><span>Profile</span></a>
        @if(auth()->user() && auth()->user()->is_admin)
        <a href="{{ route('admin.index') }}" class="gn-item"><span class="gn-item-icon"><i data-lucide="shield" class="w-4 h-4"></i></span><span>Admin</span></a>
        @endif
    </nav>
    <div class="gn-divider"></div>
    <button type="button" class="gn-item gn-theme-btn-inline"><span class="gn-item-icon"><i data-lucide="sun-moon" class="w-4 h-4"></i></span><span>Theme</span></button>
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="gn-item gn-item-danger" style="width:100%;border:none;background:none;"><span class="gn-item-icon"><i data-lucide="log-out" class="w-4 h-4"></i></span><span>Log Out</span></button>
    </form>
</div>
@endif

<script>
/*
 * Defensive, self-contained nav controller. Deliberately avoids relying on
 * load order relative to any other page script (e.g. homepage.js) — it
 * queries the DOM itself, attaches its own listeners via addEventListener
 * (not inline onclick, which can silently fail if this block loads after
 * the elements or if another script errors before reaching an inline
 * handler definition), and no other script on any page needs to know it
 * exists.
 */
(function () {
  function initNav() {
    const burger = document.getElementById('gnBurger');
    const drawer = document.getElementById('mobileNavDrawer');
    const backdrop = document.getElementById('gnBackdrop');
    const content = document.getElementById('appPushContent');
    if (!burger || !drawer || !backdrop) return; // nothing to wire on this page state

    let animating = false;

    function setOpen(opening) {
      if (animating) return;
      const hasGsap = !!window.gsap;

      if (!hasGsap) {
        drawer.classList.toggle('gn-open', opening);
        backdrop.classList.toggle('gn-open', opening);
        burger.classList.toggle('gn-open', opening);
        document.body.classList.toggle('gn-lock', opening);
        return;
      }

      animating = true;
      burger.classList.toggle('gn-open', opening);
      document.body.classList.toggle('gn-lock', opening);

      const staggerItems = drawer.querySelectorAll('.gn-profile-card, .gn-drawer-heading, .gn-item, .gn-cta, .gn-divider');
      const targets = content ? [drawer, backdrop, content] : [drawer, backdrop];
      gsap.set(targets, { willChange: 'transform, opacity' });

      const done = () => {
        animating = false;
        gsap.set(targets, { willChange: 'auto' });
        if (window.lucide) lucide.createIcons();
      };

      if (opening) {
        drawer.classList.add('gn-open');
        backdrop.classList.add('gn-open');
        gsap.fromTo(drawer, { xPercent: 100 }, { xPercent: 0, duration: 0.4, ease: 'power3.out', force3D: true, onComplete: done });
        gsap.fromTo(backdrop, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: 'power2.out' }, 0);
        if (content) {
          content.classList.add('gn-pushed');
          gsap.fromTo(content, { x: 0, scale: 1 }, { x: -48, scale: 0.96, duration: 0.4, ease: 'power3.out', force3D: true }, 0);
        }
        gsap.set(staggerItems, { opacity: 0, x: 14 });
        gsap.to(staggerItems, { opacity: 1, x: 0, duration: 0.2, stagger: 0.03, ease: 'power2.out', force3D: true, delay: 0.08 });
      } else {
        gsap.to(drawer, { xPercent: 100, duration: 0.32, ease: 'power3.in', force3D: true, onComplete: () => {
          drawer.classList.remove('gn-open');
          backdrop.classList.remove('gn-open');
          done();
        }});
        gsap.to(backdrop, { opacity: 0, duration: 0.24, ease: 'power2.in' });
        if (content) {
          gsap.to(content, { x: 0, scale: 1, duration: 0.32, ease: 'power3.in', force3D: true,
            onComplete: () => content.classList.remove('gn-pushed') });
        }
      }
    }

    burger.addEventListener('click', () => setOpen(!drawer.classList.contains('gn-open')));
    backdrop.addEventListener('click', () => setOpen(false));

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

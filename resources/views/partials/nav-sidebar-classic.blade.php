@php
    $cartCount = count(session('cart', []));
    $avatarUrl = auth()->user()?->avatar_url;
    $userName = auth()->user()?->name ?? 'Guest';
    $initial = strtoupper(substr($userName, 0, 1));
    $isAdmin = auth()->user()?->is_admin ?? false;
    $isCustomerCare = auth()->user()?->role === 'customer_care';
@endphp
<aside class="neu-sidebar" id="appSidebar">
    <div class="rail-col">
        <div class="rail-frame">
            <div class="sidebar-icon-rail">
                <div class="rail-label-spacer"></div>
                <div class="sidebar-avatar sidebar-avatar-mini">
                    @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
                </div>
                <a href="{{ route('dashboard') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}" title="Home"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></a>
                <a href="{{ route('gigs.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'gigs' ? 'active' : '' }}" title="Hustles"><i data-lucide="briefcase" class="w-4 h-4"></i></a>
                <a href="{{ route('marketplace.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}" title="Market">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    @if($cartCount)<span class="cart-badge">{{ $cartCount }}</span>@endif
                </a>
                <a href="{{ route('profile.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'profile' ? 'active' : '' }}" title="Profile"><i data-lucide="user-circle" class="w-4 h-4"></i></a>
                @if($isAdmin)
                <a href="{{ route('admin.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'admin' ? 'active' : '' }}" title="Admin"><i data-lucide="shield" class="w-4 h-4"></i></a>
                @endif
                @if($isCustomerCare)
                <a href="{{ route('customer-care.index') }}" class="rail-icon-btn rail-nav-icon {{ ($active ?? '') == 'customer-care' ? 'active' : '' }}" title="Customer Care"><i data-lucide="headset" class="w-4 h-4"></i></a>
                @endif
            </div>
        </div>
        <div class="rail-frame rail-frame-bottom">
            <button type="button" class="rail-icon-btn sidebar-theme-btn-inline" title="Theme"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
        </div>
    </div>

    <div class="sidebar-content-panel">
        <div class="sidebar-top">
            <div class="panel-dots"><span></span><span></span><span></span></div>
            <button class="sidebar-collapse-btn" id="sidebarToggle" aria-label="Collapse sidebar" type="button">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="neu-user-card">
            <div class="sidebar-avatar">
                @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="">@else<span>{{ $initial }}</span>@endif
            </div>
            <div class="sidebar-user-meta">
                <p class="sidebar-user-name">{{ $userName }} <i data-lucide="chevron-down" class="w-3 h-3"></i></p>
                <a href="{{ route('profile.index') }}" class="sidebar-user-greeting" style="text-decoration:none;">View profile</a>
            </div>
        </div>

        <div class="sidebar-search">
            <i data-lucide="search" class="w-4 h-4"></i>
            <input type="text" placeholder="Search">
        </div>

        <p class="sidebar-group-label">Menu</p>
        <nav class="neu-sidebar-nav">
            <a href="{{ route('dashboard') }}" class="{{ ($active ?? '') == 'dashboard' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></span><span class="sidebar-link-label">Home</span></a>
            <a href="{{ route('gigs.index') }}" class="{{ ($active ?? '') == 'gigs' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="briefcase" class="w-4 h-4"></i></span><span class="sidebar-link-label">Hustles</span></a>
            <a href="{{ route('marketplace.index') }}" class="{{ ($active ?? '') == 'marketplace' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span><span class="sidebar-link-label">Market</span>@if($cartCount)<span class="cart-badge" style="margin-left:auto;">{{ $cartCount }}</span>@endif</a>
            <a href="{{ route('profile.index') }}" class="{{ ($active ?? '') == 'profile' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="user-circle" class="w-4 h-4"></i></span><span class="sidebar-link-label">Profile</span></a>
            @if($isAdmin)
            <a href="{{ route('admin.index') }}" class="{{ ($active ?? '') == 'admin' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="shield" class="w-4 h-4"></i></span><span class="sidebar-link-label">Admin</span></a>
            @endif
            @if($isCustomerCare)
            <a href="{{ route('customer-care.index') }}" class="{{ ($active ?? '') == 'customer-care' ? 'active' : '' }}"><span class="sidebar-link-icon"><i data-lucide="headset" class="w-4 h-4"></i></span><span class="sidebar-link-label">Customer Care</span></a>
            @endif
        </nav>

        <p class="sidebar-group-label">General</p>
        <nav class="neu-sidebar-nav">
            @auth
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="sidebar-logout-item"><span class="sidebar-link-icon"><i data-lucide="log-out" class="w-4 h-4"></i></span><span class="sidebar-link-label">Log Out</span></button>
            </form>
            @else
            <a href="{{ route('login') }}" class="sidebar-logout-item"><span class="sidebar-link-icon"><i data-lucide="log-in" class="w-4 h-4"></i></span><span class="sidebar-link-label">Log In</span></a>
            @endauth
        </nav>

        <a href="{{ route('gigs.create') }}" class="sidebar-fab" title="Post a Hustle">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span class="sidebar-fab-label">Post a Hustle</span>
        </a>
    </div>
</aside>

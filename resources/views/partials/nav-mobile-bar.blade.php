@php
    $cartCount = count(session('cart', []));
@endphp
<!-- ===== MOBILE: bottom nav bar ONLY — no drawer, no topbar ===== -->
<nav class="mnav-bar" id="mnavBar">
    <a href="{{ route('dashboard') }}" class="mnav-item {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
        <span class="mnav-label">Home</span>
    </a>
    <a href="{{ route('gigs.index') }}" class="mnav-item {{ ($active ?? '') == 'gigs' ? 'active' : '' }}">
        <i data-lucide="briefcase" class="w-5 h-5"></i>
        <span class="mnav-label">Hustles</span>
    </a>
    <a href="{{ route('gigs.create') }}" class="mnav-fab" aria-label="Post a Hustle">
        <i data-lucide="sparkles" class="w-5 h-5"></i>
    </a>
    <a href="{{ route('marketplace.index') }}" class="mnav-item {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}">
        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
        @if($cartCount)<span class="mnav-badge">{{ $cartCount }}</span>@endif
        <span class="mnav-label">Market</span>
    </a>
    <a href="{{ route('profile.index') }}" class="mnav-item {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
        <i data-lucide="user-circle" class="w-5 h-5"></i>
        <span class="mnav-label">Profile</span>
    </a>
</nav>

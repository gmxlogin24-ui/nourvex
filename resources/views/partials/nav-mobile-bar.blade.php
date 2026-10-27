@php
    $cartCount = count(session('cart', []));
@endphp
<!-- ===== MOBILE: bottom nav bar ONLY — no drawer, no topbar ===== -->
<nav class="mnav-bar" id="mnavBar">
    <a href="{{ route('dashboard') }}" class="mnav-item {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
        <i class="bi bi-house-door-fill mnav-icon"></i>
        <span class="mnav-label">Home</span>
    </a>
    <a href="{{ route('gigs.index') }}" class="mnav-item {{ ($active ?? '') == 'gigs' ? 'active' : '' }}">
        <i class="bi bi-briefcase-fill mnav-icon"></i>
        <span class="mnav-label">Hustles</span>
    </a>
    <a href="{{ route('marketplace.index') }}" class="mnav-item {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}">
        <i class="bi bi-bag-fill mnav-icon"></i>
        @if($cartCount)<span class="mnav-badge">{{ $cartCount }}</span>@endif
        <span class="mnav-label">Market</span>
    </a>
    <a href="{{ route('pulse.index') }}" class="mnav-item"><i class="bi bi-lightning-charge-fill mnav-icon"></i><span class="mnav-label">Pulse</span></a>
    <a href="{{ route('profile.index') }}" class="mnav-item {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
        <i class="bi bi-person-circle mnav-icon"></i>
        <span class="mnav-label">Profile</span>
    </a>
</nav>

@php
    $cartCount = count(session('cart', []));
@endphp
<!-- ===== MOBILE: bottom nav bar ONLY — no drawer, no topbar ===== -->
<nav class="mnav-bar" id="mnavBar">
    <a href="{{ route('dashboard') }}" class="mnav-item {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
        <i class="ph-bold ph-house mnav-icon"></i>
        <span class="mnav-label">Home</span>
    </a>
    <a href="{{ route('gigs.index') }}" class="mnav-item {{ ($active ?? '') == 'gigs' ? 'active' : '' }}">
        <i class="ph-bold ph-briefcase mnav-icon"></i>
        <span class="mnav-label">Hustles</span>
    </a>
    <a href="{{ route('marketplace.index') }}" class="mnav-item {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}">
        <i class="ph-bold ph-shopping-bag mnav-icon"></i>
        @if($cartCount)<span class="mnav-badge">{{ $cartCount }}</span>@endif
        <span class="mnav-label">Market</span>
    </a>
    <a href="{{ route('pulse.index') }}" class="mnav-item mnav-item-pulse {{ ($active ?? '') == 'pulse' ? 'active' : '' }}">
        <i class="ph-bold ph-lightning mnav-icon"></i>
        <span class="mnav-label">Pulse</span>
    </a>
    <a href="{{ route('profile.index') }}" class="mnav-item {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
        <i class="ph-bold ph-user-circle mnav-icon"></i>
        <span class="mnav-label">Profile</span>
    </a>
</nav>

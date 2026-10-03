@php $haloCart = count(session('cart', [])); @endphp
<div class="halo-wrapper" id="haloDock">
    <div class="halo-container">

        <div class="halo-bar"><div class="halo-bar-side halo-bar-left"></div><svg class="halo-bar-mid" viewBox="0 0 140 90" width="140" height="90" xmlns="http://www.w3.org/2000/svg"><path d="M -1 0 H 0 Q 14.33 0 19.70 13.3 L 30.13 39.11 A 43 43 0 0 0 109.87 39.11 L 120.30 13.3 Q 125.67 0 140 0 H 141 V 90 H -1 Z" fill="#000000"/><path d="M -1 0.5 H 0 Q 14.33 0.5 19.70 13.8 L 30.13 39.61 A 43 43 0 0 0 109.87 39.61 L 120.30 13.8 Q 125.67 0.5 140 0.5 H 141" fill="none" stroke-width="1" class="halo-mid-stroke"/></svg><div class="halo-bar-side halo-bar-right"></div></div>

        <div class="halo-items">
            <a href="{{ route('dashboard') }}" class="halo-item {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span class="halo-label">Home</span>
            </a>

            <a href="{{ route('gigs.index') }}" class="halo-item {{ ($active ?? '') == 'gigs' ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i>
                <span class="halo-label">Hustles</span>
            </a>

            <div class="halo-gap"></div>

            <a href="{{ route('marketplace.index') }}" class="halo-item {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}">
                <i class="bi bi-bag-fill"></i>
                <span class="halo-label">Market</span>
                @if($haloCart)<span class="halo-badge">{{ $haloCart }}</span>@endif
            </a>

            <a href="{{ route('profile.index') }}" class="halo-item {{ ($active ?? '') == 'profile' ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span class="halo-label">Profile</span>
            </a>
        </div>

        <div class="halo-center-wrap">
            <a href="{{ route('pulse.index') }}" class="halo-center" aria-label="Pulse">
                <i class="bi bi-lightning-charge-fill"></i>
            </a>
        </div>

    </div>
</div>

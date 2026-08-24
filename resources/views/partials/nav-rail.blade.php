@php
    $navRailAvatar = auth()->user()?->avatar_url;
    $navRailName = auth()->user()?->name ?? 'Guest';
    $navRailInitial = strtoupper(substr($navRailName, 0, 1));
    $navRailIsAdmin = auth()->user()?->is_admin ?? false;
    $navRailIsCC = auth()->user()?->role === 'customer_care';
@endphp
<aside class="d2-global-rail">
    <div class="d2-rail-avatar">
        @if($navRailAvatar)<img src="{{ $navRailAvatar }}" alt="">@else<span>{{ $navRailInitial }}</span>@endif
    </div>
    <a href="{{ route('dashboard') }}" class="d2-rail-link {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}" title="Home"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></a>
    <a href="{{ route('gigs.index') }}" class="d2-rail-link {{ ($active ?? '') == 'gigs' ? 'active' : '' }}" title="Hustles"><i data-lucide="briefcase" class="w-4 h-4"></i></a>
    <a href="{{ route('marketplace.index') }}" class="d2-rail-link {{ ($active ?? '') == 'marketplace' ? 'active' : '' }}" title="Market"><i data-lucide="shopping-bag" class="w-4 h-4"></i></a>
    <a href="{{ route('profile.index') }}" class="d2-rail-link {{ ($active ?? '') == 'profile' ? 'active' : '' }}" title="Profile"><i data-lucide="user-circle" class="w-4 h-4"></i></a>
    @if($navRailIsAdmin)
    <a href="{{ route('admin.index') }}" class="d2-rail-link {{ ($active ?? '') == 'admin' ? 'active' : '' }}" title="Admin"><i data-lucide="shield" class="w-4 h-4"></i></a>
    @endif
    @if($navRailIsCC)
    <a href="{{ route('customer-care.index') }}" class="d2-rail-link {{ ($active ?? '') == 'customer-care' ? 'active' : '' }}" title="Customer Care"><i data-lucide="headset" class="w-4 h-4"></i></a>
    @endif
    <button type="button" class="d2-rail-link d2-rail-theme-btn" title="Theme" style="margin-top:auto;"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
</aside>

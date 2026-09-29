@php
    $navRailAvatar = auth()->user()?->avatar_url;
    $navRailName = auth()->user()?->name ?? 'Guest';
    $navRailInitial = strtoupper(substr($navRailName, 0, 1));
    $navRailIsAdmin = auth()->user()?->is_admin ?? false;
    $navRailIsCC = auth()->user()?->role === 'customer_care';
    $navRailLinks = collect([
        ['route' => 'dashboard', 'key' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Home'],
        ['route' => 'gigs.index', 'key' => 'gigs', 'icon' => 'briefcase', 'label' => 'Hustles'],
        ['route' => 'marketplace.index', 'key' => 'marketplace', 'icon' => 'shopping-bag', 'label' => 'Market'],
        ['route' => 'pulse.index', 'key' => 'pulse', 'icon' => 'zap', 'label' => 'Pulse'],
        ['route' => 'profile.index', 'key' => 'profile', 'icon' => 'user-circle', 'label' => 'Profile'],
        ['route' => 'levels-badges.show', 'key' => 'levels-badges', 'icon' => 'award', 'label' => 'Levels'],
    ]);
    if ($navRailIsAdmin) $navRailLinks->push(['route' => 'admin.index', 'key' => 'admin', 'icon' => 'shield', 'label' => 'Admin']);
    if ($navRailIsCC) $navRailLinks->push(['route' => 'customer-care.index', 'key' => 'customer-care', 'icon' => 'headset', 'label' => 'Support']);
@endphp
<div class="d2-global-rail">
    {{-- Card 1: icon-only, always visible --}}
    <aside class="d2-rail-icons">
        <div class="d2-rail-header-slot">
            <div class="d2-rail-avatar">
                @if($navRailAvatar)<img src="{{ $navRailAvatar }}" alt="">@else<span>{{ $navRailInitial }}</span>@endif
            </div>
        </div>
        @foreach($navRailLinks as $link)
        <a href="{{ route($link['route']) }}" class="d2-rail-link {{ ($active ?? '') == $link['key'] ? 'active' : '' }}" title="{{ $link['label'] }}"><i data-lucide="{{ $link['icon'] }}" class="w-4 h-4"></i></a>
        @endforeach
        <form method="POST" action="{{ route('profile.theme.toggle') }}" style="margin:0;margin-top:auto;">
            @csrf
            <button type="submit" class="d2-rail-link d2-rail-theme-btn" title="Theme"><i data-lucide="sun-moon" class="w-4 h-4"></i></button>
        </form>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="d2-rail-link" title="Log Out"><i data-lucide="log-out" class="w-4 h-4"></i></button>
        </form>
    </aside>

    {{-- Card 2: icon + label, plain theme only --}}
    <aside class="d2-rail-labels">
        <div class="d2-rail-header-slot" style="flex-direction:column;align-items:stretch;gap:8px;">
            <div class="d2-rail-brand"><img src="{{ asset('images/qelvo-mark.png') }}" alt="NOURVEX" class="d2-rail-logo"><span>NOURVEX</span></div>
            <a href="{{ route('gigs.create') }}" class="d2-rail-create"><i data-lucide="plus" class="w-4 h-4"></i><span>Post a Hustle</span></a>
        </div>
        <nav class="d2-rail-nav">
            @foreach($navRailLinks as $link)
            <a href="{{ route($link['route']) }}" class="d2-rail-link-labeled {{ ($active ?? '') == $link['key'] ? 'active' : '' }}"><i data-lucide="{{ $link['icon'] }}" class="w-4 h-4"></i><span>{{ $link['label'] }}</span></a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('profile.theme.toggle') }}" style="margin:0;">
            @csrf
            <button type="submit" class="d2-rail-toggle-row d2-rail-theme-btn" title="Theme">
                <i data-lucide="sun-moon" class="w-4 h-4"></i><span>Dark mode</span>
                <span class="d2-rail-toggle-pill {{ (auth()->user()->theme_style ?? 'glass') === 'qelvo-dark' ? 'is-on' : '' }}"><span class="d2-rail-toggle-dot"></span></span>
            </button>
        </form>
        <div class="d2-rail-user-row">
            <div class="d2-rail-avatar">
                @if($navRailAvatar)<img src="{{ $navRailAvatar }}" alt="">@else<span>{{ $navRailInitial }}</span>@endif
            </div>
            <span class="d2-rail-username">{{ $navRailName }}</span>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="d2-rail-logout" title="Log Out"><i data-lucide="log-out" class="w-4 h-4"></i></button>
            </form>
        </div>
    </aside>
</div>

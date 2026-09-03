@if(auth()->user()?->role === 'customer_care' && !auth()->user()?->is_admin)
<aside class="admin-sidebar">
    <a href="{{ route('dashboard') }}" class="admin-sidebar-exit" title="Switch to Normal View">
        <i data-lucide="arrow-left"></i>
    </a>
    <div class="admin-sidebar-divider"></div>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('customer-care.index') }}" class="admin-sidebar-link {{ $activeRoute === 'customer-care.index' ? 'active' : '' }}" title="Overview">
            <i data-lucide="layout-dashboard"></i><span>Overview</span>
        </a>
        <a href="{{ route('customer-care.orders') }}" class="admin-sidebar-link {{ $activeRoute === 'customer-care.orders' ? 'active' : '' }}" title="Orders">
            <i data-lucide="package"></i><span>Orders</span>
        </a>
        <a href="{{ route('customer-care.gig-disputes') }}" class="admin-sidebar-link {{ $activeRoute === 'customer-care.gig-disputes' ? 'active' : '' }}" title="Gig Disputes">
            <i data-lucide="scale"></i><span>Gig Disp.</span>
        </a>
        <a href="{{ route('customer-care.listing-disputes') }}" class="admin-sidebar-link {{ $activeRoute === 'customer-care.listing-disputes' ? 'active' : '' }}" title="Market Disputes">
            <i data-lucide="shield-alert"></i><span>Mkt Disp.</span>
        </a>
        <a href="{{ route('customer-care.user-lookup') }}" class="admin-sidebar-link {{ $activeRoute === 'customer-care.user-lookup' ? 'active' : '' }}" title="User Lookup">
            <i data-lucide="search"></i><span>Lookup</span>
        </a>
    </nav>
    <button type="button" class="admin-sidebar-toggle-btn" onclick="toggleAdminNavStyle()" title="Switch nav style">
        <i data-lucide="arrow-left-right"></i>
    </button>
</aside>

<nav class="admin-topbar-float">
    <a href="{{ route('dashboard') }}" class="admin-topbar-float-exit" title="Switch to Normal View"><i data-lucide="arrow-left"></i></a>
    <a href="{{ route('customer-care.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'customer-care.index' ? 'active' : '' }}" title="Overview"><i data-lucide="layout-dashboard"></i></a>
    <a href="{{ route('customer-care.orders') }}" class="admin-topbar-float-link {{ $activeRoute === 'customer-care.orders' ? 'active' : '' }}" title="Orders"><i data-lucide="package"></i></a>
    <a href="{{ route('customer-care.gig-disputes') }}" class="admin-topbar-float-link {{ $activeRoute === 'customer-care.gig-disputes' ? 'active' : '' }}" title="Gig Disputes"><i data-lucide="scale"></i></a>
    <a href="{{ route('customer-care.listing-disputes') }}" class="admin-topbar-float-link {{ $activeRoute === 'customer-care.listing-disputes' ? 'active' : '' }}" title="Market Disputes"><i data-lucide="shield-alert"></i></a>
    <a href="{{ route('customer-care.user-lookup') }}" class="admin-topbar-float-link {{ $activeRoute === 'customer-care.user-lookup' ? 'active' : '' }}" title="User Lookup"><i data-lucide="search"></i></a>
    <button type="button" class="admin-topbar-float-toggle" onclick="toggleAdminNavStyle()" title="Switch nav style"><i data-lucide="arrow-left-right"></i></button>
</nav>
@else
<aside class="admin-sidebar">
    <a href="{{ route('dashboard') }}" class="admin-sidebar-exit" title="Exit Admin">
        <i data-lucide="arrow-left"></i>
    </a>
    <div class="admin-sidebar-divider"></div>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('admin.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.index' ? 'active' : '' }}" title="Overview">
            <i data-lucide="layout-dashboard"></i><span>Overview</span>
        </a>
        <a href="{{ route('admin.users') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.users' ? 'active' : '' }}" title="Users">
            <i data-lucide="users"></i><span>Users</span>
        </a>
        <a href="{{ route('admin.grant-access') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.grant-access' ? 'active' : '' }}" title="Grant Access">
            <i data-lucide="key"></i><span>Access</span>
        </a>
        <a href="{{ route('admin.gigs') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.gigs' ? 'active' : '' }}" title="Gigs">
            <i data-lucide="briefcase"></i><span>Gigs</span>
        </a>
        <a href="{{ route('admin.listings') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.listings' ? 'active' : '' }}" title="Listings">
            <i data-lucide="shopping-bag"></i><span>Listings</span>
        </a>
        <a href="{{ route('admin.transactions') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.transactions' ? 'active' : '' }}" title="Transactions">
            <i data-lucide="arrow-left-right"></i><span>Trans.</span>
        </a>
        <a href="{{ route('admin.revenue') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.revenue' ? 'active' : '' }}" title="Revenue">
            <i data-lucide="trending-up"></i><span>Revenue</span>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.disputes.index' ? 'active' : '' }}" title="Gig Disputes">
            <i data-lucide="scale"></i><span>Gig Disp.</span>
        </a>
        <a href="{{ route('admin.marketplace-disputes.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.marketplace-disputes.index' ? 'active' : '' }}" title="Market Disputes">
            <i data-lucide="shield-alert"></i><span>Mkt Disp.</span>
        </a>
        <a href="{{ route('admin.subscriptions') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.subscriptions' ? 'active' : '' }}" title="Subscriptions">
            <i data-lucide="credit-card"></i><span>Subs</span>
        </a>
        <a href="{{ route('admin.news') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.news' ? 'active' : '' }}" title="News">
            <i data-lucide="newspaper"></i><span>News</span>
        </a>
        <a href="{{ route('admin.blog') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.blog' ? 'active' : '' }}" title="Blog">
            <i data-lucide="file-text"></i><span>Blog</span>
        </a>
        <a href="{{ route('admin.levels.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.levels.index' ? 'active' : '' }}" title="Levels">
            <i data-lucide="trending-up"></i><span>Levels</span>
        </a>
        <a href="{{ route('admin.badges.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.badges.index' ? 'active' : '' }}" title="Badges">
            <i data-lucide="award"></i><span>Badges</span>
        </a>
        <a href="{{ route('admin.feature-gates.index') }}" class="admin-sidebar-link {{ $activeRoute === 'admin.feature-gates.index' ? 'active' : '' }}" title="Feature Gates">
            <i data-lucide="toggle-left"></i><span>Features</span>
        </a>
    </nav>
    <button type="button" class="admin-sidebar-toggle-btn" onclick="toggleAdminNavStyle()" title="Switch nav style">
        <i data-lucide="arrow-left-right"></i>
    </button>
</aside>

<nav class="admin-topbar-float">
    <a href="{{ route('dashboard') }}" class="admin-topbar-float-exit" title="Exit Admin"><i data-lucide="arrow-left"></i></a>
    <a href="{{ route('admin.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.index' ? 'active' : '' }}" title="Overview"><i data-lucide="layout-dashboard"></i></a>
    <a href="{{ route('admin.users') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.users' ? 'active' : '' }}" title="Users"><i data-lucide="users"></i></a>
    <a href="{{ route('admin.gigs') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.gigs' ? 'active' : '' }}" title="Gigs"><i data-lucide="briefcase"></i></a>
    <a href="{{ route('admin.listings') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.listings' ? 'active' : '' }}" title="Listings"><i data-lucide="shopping-bag"></i></a>
    <a href="{{ route('admin.transactions') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.transactions' ? 'active' : '' }}" title="Transactions"><i data-lucide="arrow-left-right"></i></a>
    <a href="{{ route('admin.disputes.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.disputes.index' ? 'active' : '' }}" title="Gig Disputes"><i data-lucide="scale"></i></a>
    <a href="{{ route('admin.marketplace-disputes.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.marketplace-disputes.index' ? 'active' : '' }}" title="Market Disputes"><i data-lucide="shield-alert"></i></a>
    <a href="{{ route('admin.levels.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.levels.index' ? 'active' : '' }}" title="Levels"><i data-lucide="trending-up"></i></a>
    <a href="{{ route('admin.badges.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.badges.index' ? 'active' : '' }}" title="Badges"><i data-lucide="award"></i></a>
    <a href="{{ route('admin.feature-gates.index') }}" class="admin-topbar-float-link {{ $activeRoute === 'admin.feature-gates.index' ? 'active' : '' }}" title="Feature Gates"><i data-lucide="toggle-left"></i></a>
    <button type="button" class="admin-topbar-float-toggle" onclick="toggleAdminNavStyle()" title="Switch nav style"><i data-lucide="arrow-left-right"></i></button>
</nav>
@endif

<div class="admin-edge-logo"><div class="logo-badge"></div></div>
<div class="admin-edge-selected" id="admin-topbar-selected-user">
    <span class="admin-topbar-selected-avatar">—</span>
</div>

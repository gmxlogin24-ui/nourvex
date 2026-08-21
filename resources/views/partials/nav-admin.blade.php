<div class="admin-topbar">
    <a href="{{ route('dashboard') }}" class="admin-exit-link">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Exit Admin
    </a>
    <span class="admin-topbar-title"><i data-lucide="shield" class="w-4 h-4"></i> Admin</span>
</div>
<nav class="admin-section-nav">
    <a href="{{ route('admin.index') }}" class="{{ $activeRoute === 'admin.index' ? 'active' : '' }}">Overview</a>
    <a href="{{ route('admin.users') }}" class="{{ $activeRoute === 'admin.users' ? 'active' : '' }}">Users</a>
    <a href="{{ route('admin.grant-access') }}" class="{{ $activeRoute === 'admin.grant-access' ? 'active' : '' }}">Grant Access</a>
    <a href="{{ route('admin.gigs') }}" class="{{ $activeRoute === 'admin.gigs' ? 'active' : '' }}">Gigs</a>
    <a href="{{ route('admin.listings') }}" class="{{ $activeRoute === 'admin.listings' ? 'active' : '' }}">Listings</a>
    <a href="{{ route('admin.transactions') }}" class="{{ $activeRoute === 'admin.transactions' ? 'active' : '' }}">Transactions</a>
    <a href="{{ route('admin.revenue') }}" class="{{ $activeRoute === 'admin.revenue' ? 'active' : '' }}">Revenue</a>
    <a href="{{ route('admin.disputes.index') }}" class="{{ $activeRoute === 'admin.disputes.index' ? 'active' : '' }}">Gig Disputes</a>
    <a href="{{ route('admin.marketplace-disputes.index') }}" class="{{ $activeRoute === 'admin.marketplace-disputes.index' ? 'active' : '' }}">Market Disputes</a>
    <a href="{{ route('admin.subscriptions') }}" class="{{ $activeRoute === 'admin.subscriptions' ? 'active' : '' }}">Subscriptions</a>
    <a href="{{ route('admin.news') }}" class="{{ $activeRoute === 'admin.news' ? 'active' : '' }}">News</a>
    <a href="{{ route('admin.blog') }}" class="{{ $activeRoute === 'admin.blog' ? 'active' : '' }}">Blog</a>
</nav>

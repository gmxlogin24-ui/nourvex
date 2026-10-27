@extends('layouts.world-shell')
@section('world', 'gigs')
@section('content')
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}"><link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">


<main class="wp-page wp-gigs wp-wide {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-page' : '' }}" style="{{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'padding-top:0 !important;' : '' }}">
    <h1 class="text-2xl font-semibold mb-1">My Posts</h1>
    <p class="text-muted text-sm mb-6">Manage your hustles and campus market listings</p>

    @if (session('status'))
    <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card p-6 mb-6 reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <h2 class="font-semibold mb-4">My Gigs ({{ count($gigs) }})</h2>
        @forelse($gigs as $gig)
        <div class="tx-table-row">
            <div class="flex items-center gap-3">
                <div class="icon-badge icon-badge-gigs" style="width:2rem;height:2rem;"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                <div>
                    <p class="text-sm font-medium">{{ $gig->title }}</p>
                    <p class="text-muted text-xs">₦{{ number_format($gig->amount) }}</p>
                </div>
            </div>
            <span class="tx-status {{ $gig->status === 'open' ? 'done' : 'pending' }}">{{ $gig->status === 'open' ? 'Open' : 'Taken' }}</span>
            @if($gig->status === 'open')
            <form method="POST" action="{{ route('posts.gigs.delete', $gig->id) }}" onsubmit="return confirm('Cancel this gig and refund yourself?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 text-xs">Cancel</button>
            </form>
            @else
            <span></span>
            @endif
        </div>
        @empty
        <p class="text-muted text-sm">You haven't posted any gigs yet.</p>
        @endforelse
    </div>

    <div class="glass-card p-6 reveal-on-scroll {{ (auth()->user()->theme_style ?? 'glass') === 'plain' ? 'dqm-card' : '' }}">
        <h2 class="font-semibold mb-4">My Campus Market Listings ({{ count($listings) }})</h2>
        @forelse($listings as $item)
        <div class="tx-table-row">
            <div class="flex items-center gap-3">
                <div class="icon-badge icon-badge-market" style="width:2rem;height:2rem;"><i data-lucide="shopping-bag" class="w-4 h-4"></i></div>
                <div>
                    <p class="text-sm font-medium">{{ $item->title }}</p>
                    <p class="text-muted text-xs">₦{{ number_format($item->amount) }} · {{ ucfirst($item->listing_type) }}</p>
                </div>
            </div>
            <span></span>
            <form method="POST" action="{{ route('posts.listings.delete', $item->id) }}" onsubmit="return confirm('Remove this listing?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 text-xs">Remove</button>
            </form>
        </div>
        @empty
        <p class="text-muted text-sm">You haven't posted any listings yet.</p>
        @endforelse
    </div>
</main>
@endsection

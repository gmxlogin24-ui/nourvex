@php
    $gmAvatar = auth()->user()?->avatar_url;
    $gmOpenCount = $gigs->where('status', 'open')->count();
    $gmAvgReward = $gigs->count() ? $gigs->avg('amount') : 0;
    $gmMyPosted = $gigs->where('user_id', auth()->id())->count();
    $gmStackSource = $gigs->where('user_id', auth()->id())->count() >= 2
        ? $gigs->where('user_id', auth()->id())
        : $gigs;
    $gmStackItems = $gmStackSource->take(6)->map(fn($g) => ['title' => $g->title, 'image_url' => $g->image_url ?? null, 'description' => $g->description ?? null]);
    if ($gmStackItems->count() < 2) {
        $gmUsed = $gmStackItems->pluck('title');
        $gmExtra = $gigs->reject(fn($g) => $gmUsed->contains($g->title))->take(3 - $gmStackItems->count())
            ->map(fn($g) => ['title' => $g->title, 'image_url' => $g->image_url ?? null, 'description' => $g->description ?? null]);
        $gmStackItems = $gmStackItems->merge($gmExtra);
    }
    $gmFeatured = $gmStackItems->first();
    $gmCategoryIcons = [
        'Design' => 'palette', 'Writing' => 'pencil', 'Tech' => 'cpu',
        'Errands & Delivery' => 'truck', 'Tutoring' => 'graduation-cap', 'Photography' => 'camera',
    ];
    $gmChipColors = ['#2ba84a', '#0a84ff', '#af52de', '#ff9500', '#ff375f', '#30b0c7'];
    $ghSlides = $gigs->sortByDesc('created_at')->take(6)->values()->map(function ($g) {
        return [
            'id' => $g->id,
            'title' => $g->title,
            'amount' => $g->amount,
            'poster' => $g->poster_name ?? 'A student',
            'image_url' => $g->image_url ?? null,
            'seed' => 'gig' . $g->id,
        ];
    });
@endphp

<style>
.gh-hero { position: relative; width: 100%; height: 165px; border-radius: 18px; overflow: hidden; margin-bottom: 16px; }
.gh-slide { position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .6s ease; text-decoration: none; }
.gh-slide.active { opacity: 1; visibility: visible; z-index: 1; }
.gh-slide img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: blur(1px); }
.gh-slide-overlay { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; padding: 16px; background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.05) 35%, rgba(0,0,0,0.55) 100%); }
.gh-eyebrow { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--user-accent, #38D9E8); margin: 0 0 4px; }
.gh-slide-title { font-size: 15px; font-weight: 800; color: #fff; line-height: 1.2; margin: 0; }
.gh-slide-sub { font-size: 11.5px; color: rgba(255,255,255,0.85); margin: 3px 0 0; }
.gh-dots { position: absolute; bottom: 10px; right: 12px; z-index: 2; display: flex; gap: 5px; }
.gh-dot { width: 6px; height: 6px; border-radius: 999px; background: rgba(255,255,255,0.45); border: none; padding: 0; cursor: pointer; }
.gh-dot.active { background: #fff; width: 16px; }
</style>

<div class="dqm-page">
    @if($ghSlides->isNotEmpty())
    <div class="gh-hero" id="ghHero">
        @foreach($ghSlides as $i => $slide)
        <a href="{{ route('gigs.show', $slide['id']) }}" class="gh-slide {{ $i === 0 ? 'active' : '' }}">
            <img src="{{ $slide['image_url'] ?? 'https://picsum.photos/seed/' . $slide['seed'] . '/700/400' }}" alt="{{ $slide['title'] }}">
            <div class="gh-slide-overlay">
                <p class="gh-eyebrow">Just Posted</p>
                <p class="gh-slide-title">{{ $slide['title'] }}</p>
                <p class="gh-slide-sub">₦{{ number_format($slide['amount']) }} · {{ $slide['poster'] }}</p>
            </div>
        </a>
        @endforeach
        <div class="gh-dots">
            @foreach($ghSlides as $i => $slide)
            <button type="button" class="gh-dot {{ $i === 0 ? 'active' : '' }}" onclick="ghShowSlide({{ $i }})"></button>
            @endforeach
        </div>
    </div>
    <script>
    (function () {
        var slides = document.querySelectorAll('#ghHero .gh-slide');
        var dots = document.querySelectorAll('#ghHero .gh-dot');
        var current = 0;
        window.ghShowSlide = function (i) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = i;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
        };
        if (slides.length > 1) {
            setInterval(function () { ghShowSlide((current + 1) % slides.length); }, 4500);
        }
    })();
    </script>
    @endif


    <div class="dqm-card" style="margin-bottom:16px;">
        <form method="GET" style="display:flex;align-items:center;gap:8px;">
            <i data-lucide="search" class="w-4 h-4" style="color:var(--dqm-text-3);flex-shrink:0;"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search hustles..." style="border:none;background:none;outline:none;flex:1;font-size:13px;color:var(--dqm-text);">
        </form>
    </div>

    <div class="dqm-service-row" style="margin-bottom:16px;">
        <a href="{{ route('gigs.index') }}" class="dqm-service" style="{{ !request('category') ? 'background:var(--user-accent, #0a84ff);color:#fff;' : '' }}">
            <i data-lucide="grid" class="w-4 h-4" style="background:{{ !request('category') ? 'rgba(255,255,255,0.25)' : 'var(--dqm-text)' }};color:#fff;border-radius:50%;"></i><span>All</span>
        </a>
        @foreach($gmCategoryIcons as $cat => $icon)
        <a href="{{ route('gigs.index', ['category' => $cat]) }}" class="dqm-service" style="{{ request('category') === $cat ? 'background:var(--user-accent, #0a84ff);color:#fff;' : '' }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4" style="background:{{ request('category') === $cat ? 'rgba(255,255,255,0.25)' : $gmChipColors[$loop->index % count($gmChipColors)] }};color:#fff;border-radius:50%;"></i><span>{{ $cat }}</span>
        </a>
        @endforeach
    </div>

    <div class="dqm-stat-row" style="max-width:none;grid-template-columns:repeat(3,1fr);margin-bottom:16px;">
        <div class="dqm-stat dqm-tint-green"><i data-lucide="briefcase" class="w-4 h-4"></i><span class="v">{{ $gmOpenCount }}</span><span class="l">Open Hustles</span></div>
        <div class="dqm-stat dqm-tint-blue"><i data-lucide="banknote" class="w-4 h-4"></i><span class="v">₦{{ number_format($gmAvgReward) }}</span><span class="l">Avg Reward</span></div>
        <div class="dqm-stat dqm-tint-purple"><i data-lucide="user" class="w-4 h-4"></i><span class="v">{{ $gmMyPosted }}</span><span class="l">Posted by You</span></div>
    </div>

    <div class="dqm-card-head"><span>Available Hustles</span>@auth@if(auth()->user()->planAllows('gigs_post'))<a href="{{ route('gigs.create') }}">Post</a>@endif@else<a href="{{ route('register') }}">Sign Up</a>@endauth</div>
    <div class="dqm-grid dqm-listing-grid" style="grid-template-columns:1fr;gap:8px;">
        @forelse($gigs as $gig)
        <a href="{{ route('gigs.show', $gig->id) }}" class="dqm-card dqm-gig-row" style="padding:10px;border-top:none;{{ $gig->status !== 'open' ? 'opacity:0.6;' : '' }}">
            <span class="dqm-gig-title">{{ $gig->title }}</span>
            <span class="dqm-gig-status">{{ ucfirst($gig->status ?? '') }}</span>
            <span class="dqm-gig-amount">₦{{ number_format($gig->amount) }}</span>
        </a>
        @empty
        <p class="dqm-empty">No hustles match — try a different search or category.</p>
        @endforelse
    </div>
</div>

<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard2', $this->getData());
    }

    protected function getData()
    {
        $weekStart = now()->startOfWeek();

        $gigs = \App\Models\Gig::leftJoin('gig_views', function ($join) {
                $join->on('gigs.id', '=', 'gig_views.gig_id')
                     ->where('gig_views.user_id', '=', auth()->id());
            })
            ->select('gigs.*', 'gig_views.views_count')
            ->orderByDesc('gig_views.views_count')
            ->orderByDesc('gigs.created_at')
            ->take(3)
            ->get();

        $userId = auth()->id();

        $earnings = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'gig_earning')
            ->sum('amount');

        $allReferrals = \App\Models\User::where('referred_by', $userId)->get();
        $activeReferrals = $allReferrals->where('tier', '!=', 'free');
        $inactiveReferrals = $allReferrals->where('tier', '=', 'free');

        $totalIn = \App\Models\Transaction::where('user_id', $userId)->where('amount', '>', 0)->sum('amount');
        $totalOut = abs(\App\Models\Transaction::where('user_id', $userId)->where('amount', '<', 0)->sum('amount'));

        $myOpenGigs = \App\Models\Gig::where('user_id', $userId)->where('status', 'open')->count();
        $myCompletedGigs = \App\Models\Gig::where('accepted_by', $userId)->where('status', 'completed')->count();
        $myListings = \App\Models\Listing::where('user_id', $userId)->count();

        $recentTx = \App\Models\Transaction::where('user_id', $userId)->latest()->take(6)->get();

        $categoryTotals = \App\Models\Transaction::where('user_id', $userId)
            ->selectRaw('type, SUM(ABS(amount)) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $userInterests = auth()->user()->interests;
        $userInterests = is_array($userInterests) ? $userInterests : [];
        $feedGigs = \App\Models\Gig::where('status', 'open')
            ->when(count($userInterests) > 0, fn($q) => $q->whereIn('category', $userInterests))
            ->latest()->take(4)->get()->map(fn($g) => ['type' => 'gig', 'title' => $g->title, 'meta' => '₦' . number_format($g->amount), 'url' => route('gigs.show', $g->id), 'time' => $g->created_at]);
        $feedNews = \App\Models\News::latest('published_at')->take(3)->get()->map(fn($n) => ['type' => 'news', 'title' => $n->title, 'meta' => $n->category, 'url' => route('news.index'), 'time' => $n->published_at]);
        $feedListings = \App\Models\Listing::where('status', 'available')->latest()->take(3)->get()->map(fn($l) => ['type' => 'listing', 'title' => $l->title, 'meta' => '₦' . number_format($l->amount), 'url' => route('marketplace.show', $l->id), 'time' => $l->created_at]);
        $todayFeed = $feedGigs->concat($feedNews)->concat($feedListings)->sortByDesc('time')->take(8)->values();

        $myReviews = \App\Models\Review::with(['reviewer', 'reviewable'])->where('reviewed_user_id', $userId)->latest()->take(5)->get();
        $avgRating = \App\Models\Review::where('reviewed_user_id', $userId)->avg('rating');
        $reviewCount = \App\Models\Review::where('reviewed_user_id', $userId)->count();

        $streakMilestones = [7, 30, 100, 365];
        $nextStreakMilestone = collect($streakMilestones)->first(fn($m) => $m > (auth()->user()->streak_days ?? 0));

        $referralEarnings = \App\Models\Transaction::where('user_id', $userId)->where('type', 'referral_bonus')->sum('amount');
        $canEarnReferralBonus = in_array(auth()->user()->tier, ['tier2', 'tier3'], true);

        $leaderboardRank = null;
        if (auth()->user()->leaderboard_opt_in) {
            $rankedUsers = \App\Models\Transaction::where('transactions.created_at', '>=', $weekStart)
                ->where('amount', '>', 0)
                ->whereIn('type', ['gig_earning', 'marketplace_sale', 'referral_bonus'])
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->where('users.leaderboard_opt_in', true)
                ->selectRaw('users.id, SUM(transactions.amount) as total')
                ->groupBy('users.id')
                ->orderByDesc('total')
                ->pluck('users.id')
                ->values();
            $rankIndex = $rankedUsers->search($userId);
            $leaderboardRank = $rankIndex === false ? null : $rankIndex + 1;
        }

        $currentSubscription = \App\Models\Subscription::currentFor(auth()->user());

        $recentConversations = \App\Models\Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->where('type', 'business')
            ->with(['participants.user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->take(5)
            ->get()
            ->map(function ($conv) use ($userId) {
                $other = $conv->participants->pluck('user')->filter(fn($u) => $u && $u->id !== $userId)->first();
                return [
                    'other_name' => $other->name ?? 'Unknown',
                    'other_avatar' => $other->avatar_url ?? null,
                    'last_body' => $conv->latestMessage->body ?? '',
                    'last_at' => $conv->last_message_at,
                    'unread' => $conv->unreadCountFor($userId),
                ];
            });

        return [
            'isVerified' => (bool) auth()->user()->virtual_account_number,
            'walletCapRemaining' => auth()->user()->virtual_account_number ? null : max(0, \App\Models\User::NO_BVN_WALLET_CAP - auth()->user()->wallet_balance),
            'user' => auth()->user(),
            'balance' => auth()->user()->wallet_balance,
            'qCoinBalance' => auth()->user()->q_coin_balance,
            'qCoinClaimedToday' => auth()->user()->q_coin_last_claim_date === now()->toDateString(),
            'currentSubscription' => $currentSubscription,
            'earnings' => $earnings,
            'todayFeed' => $todayFeed,
            'recentConversations' => $recentConversations,
            'streakDays' => auth()->user()->streak_days,
            'latestNews' => \App\Models\News::latest('published_at')->take(6)->get(),
            'gigCount' => \App\Models\Gig::where('status', 'open')->count(),
            'listingCount' => \App\Models\Listing::count(),
            'referralCount' => $activeReferrals->count(),
            'referralTotal' => $allReferrals->count(),
            'referralActive' => $activeReferrals->values(),
            'referralInactive' => $inactiveReferrals->values(),
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netFlow' => $totalIn - $totalOut,
            'myOpenGigs' => $myOpenGigs,
            'myCompletedGigs' => $myCompletedGigs,
            'myListings' => $myListings,
            'recentTx' => $recentTx,
            'categoryTotals' => $categoryTotals,
            'topGigs' => $gigs,
            'topListings' => \App\Models\Listing::latest()->take(3)->get(),
            'trend' => [4000, 6500, 5200, 8100, 7300, 9200],
            'breakdown' => [55, 30, 15],
            'myReviews' => $myReviews,
            'avgRating' => $avgRating,
            'reviewCount' => $reviewCount,
            'nextStreakMilestone' => $nextStreakMilestone,
            'referralEarnings' => $referralEarnings,
            'canEarnReferralBonus' => $canEarnReferralBonus,
            'leaderboardRank' => $leaderboardRank,
        ];
    }
}

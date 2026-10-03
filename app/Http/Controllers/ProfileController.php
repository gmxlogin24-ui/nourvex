<?php

namespace App\Http\Controllers;

use App\Services\LevelBadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(protected LevelBadgeService $levelBadgeService) {}

    public function index()
    {
        $user = Auth::user();

        $result = $this->levelBadgeService->refresh($user);
        $earnedBadges = $user->badges()->get();

        $allReferrals = \App\Models\User::where('referred_by', $user->id)->get();
        $activeReferrals = $allReferrals->where('tier', '!=', 'free');

        $recentReviews = \App\Models\Review::with('reviewer')
            ->where('reviewed_user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        $myGigsPosted = \App\Models\Gig::where('user_id', $user->id)->count();
        $myListingsPosted = \App\Models\Listing::where('user_id', $user->id)->count();

        $monthStart = now()->startOfMonth();
        $monthIn = \App\Models\Transaction::where('user_id', $user->id)->where('amount', '>', 0)->where('created_at', '>=', $monthStart)->sum('amount');
        $monthOut = abs(\App\Models\Transaction::where('user_id', $user->id)->where('amount', '<', 0)->where('created_at', '>=', $monthStart)->sum('amount'));

        $leaderboardRank = null;
        if ($user->leaderboard_opt_in) {
            $weekStart = now()->startOfWeek();
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
            $rankIndex = $rankedUsers->search($user->id);
            $leaderboardRank = $rankIndex === false ? null : $rankIndex + 1;
        }

        return view('profile.index', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
            'currentLevel' => $result['level'],
            'earnedBadges' => $earnedBadges->take(6),
            'totalBadgesEarned' => $earnedBadges->count(),
            'allReferralsCount' => $allReferrals->count(),
            'activeReferralsCount' => $activeReferrals->count(),
            'recentReferrals' => $allReferrals->sortByDesc('created_at')->take(4)->values(),
            'recentReviews' => $recentReviews,
            'myGigsPosted' => $myGigsPosted,
            'myListingsPosted' => $myListingsPosted,
            'monthIn' => $monthIn,
            'monthOut' => $monthOut,
            'profileLeaderboardRank' => $leaderboardRank,
        ]);
    }

    public function preview()
    {
        $user = Auth::user();

        $result = $this->levelBadgeService->refresh($user);
        $earnedBadges = $user->badges()->get();

        return view('profile-preview', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
            'currentLevel' => $result['level'],
            'earnedBadges' => $earnedBadges->take(6),
            'totalBadgesEarned' => $earnedBadges->count(),
        ]);
    }

    public function reputation()
    {
        $user = Auth::user();

        return view('profile.reputation', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
        ]);
    }

    public function account()
    {
        return view('profile.account', ['user' => Auth::user()]);
    }

    public function appearance()
    {
        return view('profile.appearance', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        \Log::info('PROFILE UPDATE RAW accent_color: ' . var_export($request->input('accent_color'), true));
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'accent_color' => 'nullable|string|max:7',
            'theme_style' => 'nullable|string|in:glass,neumorphic,plain,aurora',
            'mobile_nav_style' => 'nullable|string|in:painted_pill,edge_dock,halo_dock',
            'sidebar_style' => 'nullable|string|in:classic,rail',
            'avatar' => 'nullable|image|max:4096',
            'background' => 'nullable|image|max:8192',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_url'] = '/storage/' . $path;
        }

        if ($request->hasFile('background')) {
            $path = $request->file('background')->store('backgrounds', 'public');
            $validated['background_url'] = '/storage/' . $path;
        }

        unset($validated['avatar'], $validated['background']);

        $user->update($validated);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('status', 'Password updated.');
    }

    public function toggleThemeStyle(Request $request)
    {
        $user = $request->user();
        $user->theme_style = ($user->theme_style ?? 'glass') === 'qelvo-dark' ? 'glass' : 'qelvo-dark';
        $user->save();
        return back()->with('status', $user->theme_style === 'qelvo-dark' ? 'NOURVEX Dark enabled' : 'NOURVEX Dark disabled');
    }
}

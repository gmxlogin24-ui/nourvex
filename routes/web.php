<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GigController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\VirtualAccountController;
use App\Http\Controllers\FlutterwaveWebhookController;
use App\Http\Controllers\MyPostsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\PulseController;
use App\Http\Controllers\PlanGateController;

Route::get('/', function () {
    $allNews = \App\Models\News::latest('published_at')->take(20)->get();

    $adsFeed = $allNews->where('type', 'ad')->filter(fn($n) => !$n->expires_at || $n->expires_at->isFuture())->sortByDesc('is_featured')->take(6)->map(fn($n) => [
        'title' => $n->title,
        'body' => $n->body,
        'image_url' => $n->image_url ?? null,
    ])->values();

    $newsFeed = $allNews->where('type', '!=', 'ad')->take(10)->map(fn($n) => [
        'title' => $n->title,
        'body' => $n->body,
        'tag' => 'Campus Buzz',
        'time' => $n->published_at,
    ])->values();

    return view('welcome', ['newsFeed' => $newsFeed, 'adsFeed' => $adsFeed]);
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/plans', [PlanGateController::class, 'choose'])->name('plans.choose');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/register/vendor', [AuthController::class, 'showVendorRegister'])->name('register.vendor');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/vendor', [AuthController::class, 'registerVendor'])->name('register.vendor.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/webhooks/flutterwave', [FlutterwaveWebhookController::class, 'handle']);

// Public pages
Route::get('/features', [FeaturesController::class, 'index'])->name('features');
Route::view('/safety', 'safety')->name('safety');

// Public browsing — guests can look, not act
Route::get('/buzz', function () {
    if (!auth()->check() || !auth()->user()->is_admin) {
        return redirect()->route('pulse.index');
    }
    return app(NewsController::class)->index(request());
})->name('news.index');
Route::get('/pulse', [PulseController::class, 'index'])->name('pulse.index');
Route::get('/gigs', [GigController::class, 'index'])->name('gigs.index');
Route::get('/gigs/{gig}', [GigController::class, 'show'])->name('gigs.show');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{listing}', [MarketplaceController::class, 'show'])->name('marketplace.show');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
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

        $userInterests = $user_interests = auth()->user()->interests ?? [];
        $feedGigs = \App\Models\Gig::where('status', 'open')
            ->when(count($userInterests) > 0, fn($q) => $q->whereIn('category', $userInterests))
            ->latest()->take(4)->get()->map(fn($g) => ['type' => 'gig', 'title' => $g->title, 'meta' => '₦' . number_format($g->amount), 'url' => route('gigs.show', $g->id), 'time' => $g->created_at]);
        $feedNews = \App\Models\News::latest('published_at')->take(3)->get()->map(fn($n) => ['type' => 'news', 'title' => $n->title, 'meta' => $n->category, 'url' => route('news.index'), 'time' => $n->published_at]);
        $feedListings = \App\Models\Listing::where('status', 'available')->latest()->take(3)->get()->map(fn($l) => ['type' => 'listing', 'title' => $l->title, 'meta' => '₦' . number_format($l->amount), 'url' => route('marketplace.show', $l->id), 'time' => $l->created_at]);
        $todayFeed = $feedGigs->concat($feedNews)->concat($feedListings)->sortByDesc('time')->take(8)->values();

        $currentSubscription = \App\Models\Subscription::currentFor(auth()->user());

        return view('dashboard', [
            'user' => auth()->user(),
            'balance' => auth()->user()->wallet_balance,
            'currentSubscription' => $currentSubscription,
            'earnings' => $earnings,
            'todayFeed' => $todayFeed,
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
        ]);
    })->name('dashboard');

    Route::post('/gigs/{gig}/complete', [GigController::class, 'complete'])->name('gigs.complete');
    Route::post('/gigs/{gig}/reject', [GigController::class, 'rejectDelivery'])->name('gigs.reject');
    Route::post('/gigs/{gig}/message', [GigController::class, 'sendMessage'])->name('gigs.message');
    Route::post('/gigs/{gig}/review', [ReviewController::class, 'storeForGig'])->name('gigs.review');

    // Gigs - posting requires Tier 1+
    Route::middleware('tier')->group(function () {
        Route::get('/gigs/create', [GigController::class, 'create'])->name('gigs.create');
        Route::post('/gigs', [GigController::class, 'store'])->name('gigs.store');
        Route::get('/gigs/{gig}/edit', [GigController::class, 'edit'])->name('gigs.edit');
        Route::put('/gigs/{gig}', [GigController::class, 'update'])->name('gigs.update');
    });

    Route::post('/marketplace/{listing}/purchase', [MarketplaceController::class, 'purchase'])->name('marketplace.purchase');
    Route::post('/marketplace/{listing}/confirm', [MarketplaceController::class, 'confirmDelivery'])->name('marketplace.confirm');

    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{listing}', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove/{listing}', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/marketplace/{listing}/review', [ReviewController::class, 'storeForListing'])->name('marketplace.review');

    // Marketplace - posting requires Tier 1+
    Route::middleware('tier')->group(function () {
        Route::get('/marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
        Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
    });

    Route::get('/my-posts', [MyPostsController::class, 'index'])->name('posts.index');
    Route::delete('/my-posts/gigs/{gig}', [MyPostsController::class, 'deleteGig'])->name('posts.gigs.delete');
    Route::delete('/my-posts/listings/{listing}', [MyPostsController::class, 'deleteListing'])->name('posts.listings.delete');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::post('/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('toggle-ban');
        Route::post('/users/{user}/adjust-balance', [AdminController::class, 'adjustBalance'])->name('adjust-balance');

        Route::get('/gigs', [AdminController::class, 'gigs'])->name('gigs');
        Route::delete('/gigs/{gig}', [AdminController::class, 'deleteGig'])->name('gigs.delete');

        Route::get('/listings', [AdminController::class, 'listings'])->name('listings');
        Route::delete('/listings/{listing}', [AdminController::class, 'deleteListing'])->name('listings.delete');

        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');

        Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions/{subscription}/cancel', [AdminController::class, 'cancelSubscription'])->name('subscriptions.cancel');

        Route::get('/news', [AdminController::class, 'news'])->name('news');
        Route::delete('/news/{news}', [AdminController::class, 'deleteNews'])->name('news.delete');

        Route::get('/blog', [AdminController::class, 'blog'])->name('blog');
        Route::delete('/blog/{blog}', [AdminController::class, 'deleteBlog'])->name('blog.delete');
    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/vendor/activate', function () { return view('auth.vendor-activate'); })->name('vendor.activate');
    Route::post('/vendor/pay', [FlutterwaveController::class, 'initiateVendor'])->name('vendor.pay');
    Route::get('/vendor/callback', [FlutterwaveController::class, 'vendorCallback'])->name('vendor.callback');

    Route::get('/onboarding/interests', [OnboardingController::class, 'show'])->name('onboarding.interests');
    Route::post('/onboarding/interests', [OnboardingController::class, 'store'])->name('onboarding.interests.store');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::post('/leaderboard/toggle', [LeaderboardController::class, 'toggleOptIn'])->name('leaderboard.toggle');

    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::get('/services/data-plans', [ServicesController::class, 'dataPlans'])->name('services.data-plans');
    Route::post('/services/airtime', [ServicesController::class, 'buyAirtime'])->name('services.airtime');
    Route::post('/services/data', [ServicesController::class, 'buyData'])->name('services.data');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/callback', [SubscriptionController::class, 'flutterwaveCallback'])->name('subscriptions.callback');

    // Old links to /tier still work
    Route::redirect('/tier', '/subscriptions')->name('tier.index');

    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/money-tracker', [WalletController::class, 'moneyTracker'])->name('money-tracker.index');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/flutterwave/initiate', [FlutterwaveController::class, 'initiate'])->name('flutterwave.initiate');
    Route::get('/wallet/flutterwave/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.callback');
    Route::get('/wallet/virtual-account', [VirtualAccountController::class, 'create'])->name('wallet.virtual-account');
    Route::post('/wallet/virtual-account', [VirtualAccountController::class, 'store'])->name('wallet.virtual-account.store');
    Route::post('/wallet/topup', [App\Http\Controllers\WalletActionController::class, 'topup'])->name('wallet.topup');
    Route::post('/wallet/withdraw', [App\Http\Controllers\WalletActionController::class, 'withdraw'])->name('wallet.withdraw');

    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/ad/create', [NewsController::class, 'createAd'])->name('news.ad.create');
    Route::post('/news/ad', [NewsController::class, 'storeAd'])->name('news.ad.store');

    // Blogging
    Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/create', [App\Http\Controllers\BlogController::class, 'create'])->name('blog.create');
    Route::post('/blog', [App\Http\Controllers\BlogController::class, 'store'])->name('blog.store');
    Route::get('/blog/{blog}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

    Route::get('/search', [SearchController::class, 'search'])->name('search');
});

// Gig accept/deliver: work for both logged-in users and guests (guest identity via campulsy_guest_token cookie, checked inside the controller)
Route::post('/gigs/{gig}/accept', [App\Http\Controllers\GigController::class, 'accept'])->name('gigs.accept');
Route::post('/gigs/{gig}/deliver', [App\Http\Controllers\GigController::class, 'deliver'])->name('gigs.deliver');

// Signup fee (₦1,000 account activation) - requires login (user exists) but not signup_fee_paid
Route::middleware('auth')->group(function () {
    Route::get('/activate', function () { return view('auth.signup-fee-activate'); })->name('signup-fee.activate');
    Route::get('/signup-fee', [App\Http\Controllers\FlutterwaveController::class, 'initiateSignupFee'])->name('signup-fee.initiate');
    Route::get('/signup-fee/callback', [App\Http\Controllers\FlutterwaveController::class, 'signupFeeCallback'])->name('signup-fee.callback');
});

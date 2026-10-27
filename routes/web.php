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
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\VirtualAccountController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MyPostsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerCareController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\TransactionPinController;
use App\Http\Controllers\SavedNumberController;
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
        'tag' => 'Naurvex',
        'time' => $n->published_at,
    ])->values();

    return view('welcome', ['newsFeed' => $newsFeed, 'adsFeed' => $adsFeed]);
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/plans', [PlanGateController::class, 'choose'])->name('plans.choose');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/register/vendor', [AuthController::class, 'showVendorRegister'])->name('register.vendor');
Route::post('/register/vendor', [AuthController::class, 'registerVendor'])->name('register.vendor.store')->middleware('throttle:5,1');
Route::get('/register/invite/{code}', [AuthController::class, 'showInviteRegister'])->name('register.invite');
Route::post('/register/invite/{code}', [AuthController::class, 'registerInvite'])->name('register.invite.store')->middleware('throttle:5,1');
Route::get('/register/pay', [AuthController::class, 'showRegistrationPay'])->name('register.pay');
Route::get('/register/pay/initiate', [App\Http\Controllers\PaystackController::class, 'initiateRegistrationFee'])->name('register.pay.initiate')->middleware('throttle:5,1');
Route::get('/register/pay/callback', [App\Http\Controllers\PaystackController::class, 'registrationFeeCallback'])->name('register.pay.callback');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/google', [App\Http\Controllers\SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/webhooks/paystack', [PaystackWebhookController::class, 'handle']);

// Public pages
Route::get('/features', [FeaturesController::class, 'index'])->middleware('auth')->name('features');
Route::view('/safety', 'safety')->middleware('auth')->name('safety');

// Public browsing — guests can look, not act
Route::get('/buzz', function () {
    if (!auth()->check() || !auth()->user()->is_admin) {
        return redirect()->route('dashboard');
    }
    return app(NewsController::class)->index(request());
})->name('news.index');
Route::redirect('/pulse', '/dashboard')->name('pulse.index');
Route::post('/pulse/{news}/view', [PulseController::class, 'view'])->middleware('throttle:180,1')->middleware('auth')->name('pulse.view');
Route::get('/pulse/{news}/go', [PulseController::class, 'go'])->middleware('throttle:60,1')->middleware('auth')->name('pulse.go');
Route::get('/pulse/{news}/comments', [\App\Http\Controllers\PulseCommentController::class, 'index'])->middleware('throttle:120,1')->middleware('auth')->name('pulse.comments');
Route::post('/pulse/{news}/comments', [\App\Http\Controllers\PulseCommentController::class, 'store'])->middleware(['auth', 'throttle:12,1'])->name('pulse.comments.store');
Route::delete('/pulse/{news}/comments/{comment}', [\App\Http\Controllers\PulseCommentController::class, 'destroy'])->middleware('auth')->name('pulse.comments.destroy');
Route::post('/pulse/{news}/like', [\App\Http\Controllers\PulseLikeController::class, 'toggle'])->middleware(['auth', 'throttle:60,1'])->name('pulse.like');
Route::post('/pulse/{news}/comments/{comment}/like', [\App\Http\Controllers\PulseCommentController::class, 'like'])->middleware(['auth', 'throttle:60,1'])->name('pulse.comments.like');


Route::middleware(['auth', 'signup.paid'])->group(function () {

    Route::get('/gigs', [GigController::class, 'index'])->name('gigs.index');
    Route::get('/gigs/{gig}', [GigController::class, 'show'])->name('gigs.show')->where('gig', '[0-9]+');
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/{listing}', [MarketplaceController::class, 'show'])->name('marketplace.show')->where('listing', '[0-9]+');
    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/tour/complete', [\App\Http\Controllers\TourController::class, 'complete'])->middleware('auth')->name('tour.complete');
    Route::get('/dashboard', [\App\Http\Controllers\OverviewController::class, 'index'])->name('dashboard');
Route::get('/profile-preview', [ProfileController::class, 'preview'])->middleware('auth')->name('profile.preview');
Route::get('/u/{user}', [ProfileController::class, 'show'])->middleware('auth')->name('profile.public');
Route::get('/welcome', [\App\Http\Controllers\WelcomeTourController::class, 'show'])->middleware('auth')->name('welcome.show');

    Route::get('/q-coin', [\App\Http\Controllers\QCoinController::class, 'index'])->name('qcoin.index');
    Route::post('/q-coin/claim', [\App\Http\Controllers\QCoinController::class, 'claim'])->name('qcoin.claim');
    Route::post('/concierge/message', [\App\Http\Controllers\ConciergeController::class, 'sendMessage'])->name('concierge.message');
    Route::get('/concierge', [\App\Http\Controllers\ConciergeController::class, 'show'])->name('concierge.show');
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->whereNumber('conversation')->name('chat.show');
    Route::get('/chat/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->whereNumber('conversation')->name('chat.messages');
    Route::post('/chat/{conversation}/message', [\App\Http\Controllers\ChatController::class, 'send'])->whereNumber('conversation')->name('chat.send');


    Route::post('/gigs/{gig}/complete', [GigController::class, 'complete'])->name('gigs.complete');
    Route::post('/gigs/{gig}/reject', [GigController::class, 'rejectDelivery'])->name('gigs.reject');
    Route::post('/gigs/{gig}/dispute', [App\Http\Controllers\GigDisputeController::class, 'store'])->name('gigs.dispute.store');
    Route::post('/gigs/{gig}/message', [GigController::class, 'sendMessage'])->name('gigs.message');
    Route::get('/gigs/{gig}/messages', [GigController::class, 'messages'])->name('gigs.messages');
    Route::post('/gigs/{gig}/review', [ReviewController::class, 'storeForGig'])->name('gigs.review');

    // Gigs - posting requires Tier 1+
    Route::middleware('plan:gigs_post')->group(function () {
        Route::get('/gigs/create', [GigController::class, 'create'])->name('gigs.create');
        Route::post('/gigs', [GigController::class, 'store'])->name('gigs.store');
        Route::get('/gigs/{gig}/edit', [GigController::class, 'edit'])->name('gigs.edit');
        Route::put('/gigs/{gig}', [GigController::class, 'update'])->name('gigs.update');
    });

    Route::post('/marketplace/{listing}/purchase', [MarketplaceController::class, 'purchase'])->name('marketplace.purchase')->middleware('plan:market_buy');
    Route::post('/marketplace/{listing}/confirm', [MarketplaceController::class, 'confirmDelivery'])->name('marketplace.confirm');
    Route::post('/marketplace/{listing}/dispute', [App\Http\Controllers\ListingDisputeController::class, 'store'])->name('marketplace.dispute');
    Route::post('/marketplace/{listing}/ship', [MarketplaceController::class, 'markShipped'])->name('marketplace.ship');
    Route::post('/marketplace/orders/{order}/confirm', [MarketplaceController::class, 'confirmOrderDelivery'])->name('marketplace.order.confirm');

    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{listing}', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add')->middleware('plan:market_buy');
    Route::post('/cart/remove/{listing}', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout')->middleware('plan:market_buy');
    Route::post('/marketplace/{listing}/review', [ReviewController::class, 'storeForListing'])->name('marketplace.review');
    Route::post('/marketplace/{listing}/message', [MarketplaceController::class, 'sendMessage'])->name('marketplace.message');
    Route::get('/marketplace/{listing}/messages', [MarketplaceController::class, 'messages'])->name('marketplace.messages');
    Route::post('/marketplace/{listing}/meetup-propose', [MarketplaceController::class, 'proposeMeetup'])->name('marketplace.meetup.propose');
    Route::post('/marketplace/{listing}/meetup-respond/{message}', [MarketplaceController::class, 'respondMeetup'])->name('marketplace.meetup.respond');
    Route::post('/marketplace/{listing}/favorite', [MarketplaceController::class, 'toggleFavorite'])->name('marketplace.favorite');
Route::post('/marketplace/{listing}/love', [\App\Http\Controllers\MarketplaceController::class, 'love'])->name('marketplace.love');
    Route::get('/marketplace/inbox', [MarketplaceController::class, 'inbox'])->name('marketplace.inbox');
    Route::get('/marketplace/favorites', [MarketplaceController::class, 'favorites'])->name('marketplace.favorites');
    Route::get('/marketplace/orders', [MarketplaceController::class, 'myOrders'])->name('marketplace.orders');

    // Marketplace - posting requires Tier 1+
    Route::middleware('plan:market_sell')->group(function () {
        Route::get('/marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
        Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
        Route::get('/marketplace/{listing}/edit', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
        Route::put('/marketplace/{listing}', [MarketplaceController::class, 'update'])->name('marketplace.update');
    });

    Route::get('/my-posts', [MyPostsController::class, 'index'])->name('posts.index');
    Route::delete('/my-posts/gigs/{gig}', [MyPostsController::class, 'deleteGig'])->name('posts.gigs.delete');
    Route::delete('/my-posts/listings/{listing}', [MyPostsController::class, 'deleteListing'])->name('posts.listings.delete');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/disputes', [App\Http\Controllers\GigDisputeController::class, 'index'])->name('disputes.index');
        Route::post('/disputes/{dispute}/pay', [App\Http\Controllers\GigDisputeController::class, 'resolvePaid'])->name('disputes.pay');
        Route::post('/disputes/{dispute}/partial', [App\Http\Controllers\GigDisputeController::class, 'resolvePartial'])->name('disputes.partial');
        Route::post('/disputes/{dispute}/dismiss', [App\Http\Controllers\GigDisputeController::class, 'dismiss'])->name('disputes.dismiss');

        Route::get('/marketplace-disputes', [App\Http\Controllers\ListingDisputeController::class, 'index'])->name('marketplace-disputes.index');
        Route::post('/marketplace-disputes/{dispute}/pay', [App\Http\Controllers\ListingDisputeController::class, 'resolvePaid'])->name('marketplace-disputes.pay');
        Route::post('/marketplace-disputes/{dispute}/refund', [App\Http\Controllers\ListingDisputeController::class, 'resolveRefunded'])->name('marketplace-disputes.refund');
        Route::post('/marketplace-disputes/{dispute}/dismiss', [App\Http\Controllers\ListingDisputeController::class, 'dismiss'])->name('marketplace-disputes.dismiss');
        Route::post('/disputes/assign', [App\Http\Controllers\Admin\DisputeAssignmentController::class, 'assign'])->name('disputes.assign');

        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::post('/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('toggle-ban');
        Route::post('/users/{user}/toggle-customer-care', [AdminController::class, 'toggleCustomerCare'])->name('toggle-customer-care');
        Route::put('/users/{user}/cc-permissions', [App\Http\Controllers\Admin\CustomerCarePermissionController::class, 'update'])->name('users.cc-permissions');
        Route::get('/invites', [AdminController::class, 'invites'])->name('invites');
        Route::post('/invites', [AdminController::class, 'invitesStore'])->name('invites.store');
        Route::delete('/invites/{invite}', [AdminController::class, 'invitesRevoke'])->name('invites.revoke');
        Route::post('/users/{user}/adjust-balance', [AdminController::class, 'adjustBalance'])->name('adjust-balance');

        Route::get('/grant-access', [AdminController::class, 'grantAccess'])->name('grant-access');
        Route::post('/grant-access', [AdminController::class, 'grantAccessStore'])->name('grant-access.store');

        Route::get('/gigs', [AdminController::class, 'gigs'])->name('gigs');
        Route::get('/conversations', [\App\Http\Controllers\Admin\ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversations/{conversation}', [\App\Http\Controllers\Admin\ConversationController::class, 'show'])->name('conversations.show');
        Route::delete('/conversations/{conversation}/messages/{message}', [\App\Http\Controllers\Admin\ConversationController::class, 'destroyMessage'])->name('conversations.messages.delete');
        Route::delete('/gigs/{gig}', [AdminController::class, 'deleteGig'])->name('gigs.delete');

        Route::get('/listings', [AdminController::class, 'listings'])->name('listings');
        Route::delete('/listings/{listing}', [AdminController::class, 'deleteListing'])->name('listings.delete');

        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
        Route::get('/revenue', [AdminController::class, 'revenue'])->name('revenue');

        Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions/{subscription}/cancel', [AdminController::class, 'cancelSubscription'])->name('subscriptions.cancel');

        Route::get('/news', [AdminController::class, 'news'])->name('news');
        Route::delete('/news/{news}', [AdminController::class, 'deleteNews'])->name('news.delete');

        Route::get('/blog', [AdminController::class, 'blog'])->name('blog');
        Route::delete('/blog/{blog}', [AdminController::class, 'deleteBlog'])->name('blog.delete');

        // Feature gates - admin on/off toggle + tier requirement per feature
        Route::get('/feature-gates', [App\Http\Controllers\Admin\FeatureGateController::class, 'index'])->name('feature-gates.index');
        Route::post('/feature-gates/{featureGate}/toggle', [App\Http\Controllers\Admin\FeatureGateController::class, 'toggle'])->name('feature-gates.toggle');
        Route::put('/feature-gates/{featureGate}', [App\Http\Controllers\Admin\FeatureGateController::class, 'update'])->name('feature-gates.update');

        Route::get('/settings/appearance', [App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])->name('settings.appearance');
        Route::post('/settings/appearance/background', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateBackground'])->name('settings.appearance.background');
        Route::post('/settings/appearance/background/reset', [App\Http\Controllers\Admin\SiteSettingsController::class, 'resetBackground'])->name('settings.appearance.background.reset');
        Route::post('/settings/appearance/animations', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateAnimations'])->name('settings.appearance.animations');
    });

        Route::prefix('customer-care')->name('customer-care.')->middleware('customer_care')->group(function () {
        Route::get('/', [CustomerCareController::class, 'index'])->name('index');

        Route::get('/orders', [CustomerCareController::class, 'orders'])->name('orders');
        Route::post('/orders/{order}/confirm-delivery', [CustomerCareController::class, 'confirmDelivery'])->name('orders.confirm-delivery');

        Route::get('/gig-disputes', [CustomerCareController::class, 'gigDisputes'])->name('gig-disputes');
        Route::post('/gig-disputes/{dispute}/note', [CustomerCareController::class, 'addGigDisputeNote'])->name('gig-disputes.note');
        Route::post('/gig-disputes/{dispute}/escalate', [CustomerCareController::class, 'escalateGigDispute'])->name('gig-disputes.escalate');

        Route::get('/listing-disputes', [CustomerCareController::class, 'listingDisputes'])->name('listing-disputes');
        Route::post('/listing-disputes/{dispute}/note', [CustomerCareController::class, 'addListingDisputeNote'])->name('listing-disputes.note');
        Route::post('/listing-disputes/{dispute}/escalate', [CustomerCareController::class, 'escalateListingDispute'])->name('listing-disputes.escalate');

        Route::get('/gigs/{gig}/thread', [CustomerCareController::class, 'gigThread'])->name('gigs.thread');
        Route::post('/gigs/{gig}/message', [CustomerCareController::class, 'sendGigMessage'])->name('gigs.message');

        Route::get('/listings/{listing}/thread', [CustomerCareController::class, 'listingThread'])->name('listings.thread');
        Route::post('/listings/{listing}/message', [CustomerCareController::class, 'sendListingMessage'])->name('listings.message');

        Route::post('/flag', [CustomerCareController::class, 'flag'])->name('flag');

        Route::get('/user-lookup', [CustomerCareController::class, 'userLookup'])->name('user-lookup');
    });

Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/theme-toggle', [ProfileController::class, 'toggleThemeStyle'])->name('profile.theme.toggle');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/reputation', [ProfileController::class, 'reputation'])->name('profile.reputation');
    Route::get('/profile/account', [ProfileController::class, 'account'])->name('profile.account');
    Route::get('/profile/appearance', [ProfileController::class, 'appearance'])->name('profile.appearance');

    Route::get('/vendor/activate', function () { return view('auth.vendor-activate'); })->name('vendor.activate');
    Route::post('/vendor/pay', [PaystackController::class, 'initiateVendor'])->name('vendor.pay');
    Route::get('/vendor/callback', [PaystackController::class, 'vendorCallback'])->name('vendor.callback');

    Route::get('/onboarding/interests', [OnboardingController::class, 'show'])->name('onboarding.interests');
    Route::post('/onboarding/interests', [OnboardingController::class, 'store'])->name('onboarding.interests.store');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::post('/leaderboard/toggle', [LeaderboardController::class, 'toggleOptIn'])->name('leaderboard.toggle');

    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServicesController::class, 'show'])->where('service', 'airtime|data|cable')->name('services.show');
    Route::get('/services/data-plans', [ServicesController::class, 'dataPlans'])->name('services.data-plans');
    Route::post('/services/airtime', [ServicesController::class, 'buyAirtime'])->name('services.airtime');
    Route::post('/services/data', [ServicesController::class, 'buyData'])->name('services.data');
    Route::get('/services/cable-plans', [ServicesController::class, 'cablePlans'])->name('services.cable-plans');
    Route::post('/services/cable', [ServicesController::class, 'buyCable'])->name('services.cable');
    Route::get('/services/receipt/{purchase}', [ServicesController::class, 'receipt'])->name('services.receipt');
    Route::get('/wallet/pin', [TransactionPinController::class, 'edit'])->name('wallet.pin.edit');
    Route::post('/wallet/pin', [TransactionPinController::class, 'update'])->name('wallet.pin.update')->middleware('throttle:5,1');
    Route::post('/saved-numbers', [SavedNumberController::class, 'store'])->name('saved-numbers.store');
    Route::delete('/saved-numbers/{savedNumber}', [SavedNumberController::class, 'destroy'])->name('saved-numbers.destroy');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/callback', [SubscriptionController::class, 'paystackCallback'])->name('subscriptions.callback');

    // Old links to /tier still work
    Route::redirect('/tier', '/subscriptions')->name('tier.index');

    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/money-tracker', [WalletController::class, 'moneyTracker'])->name('money-tracker.index');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [\App\Http\Controllers\WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::get('/wallet/deposit/status/{reference}', [\App\Http\Controllers\WalletController::class, 'depositStatus'])->name('wallet.deposit.status');
    Route::post('/wallet/send', [WalletController::class, 'sendMoney'])->name('wallet.send');
    Route::get('/wallet/transactions/{id}', [WalletController::class, 'showTransaction'])->name('wallet.transaction.show');
    Route::get('/wallet/send', [WalletController::class, 'sendMoneyForm'])->name('wallet.send.form');
    Route::post('/wallet/send/resolve', [WalletController::class, 'resolveRecipient'])->name('wallet.send.resolve');
    Route::post('/wallet/paystack/initiate', [PaystackController::class, 'initiate'])->name('paystack.initiate');
    Route::get('/wallet/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');
    Route::get('/wallet/virtual-account', [VirtualAccountController::class, 'create'])->name('wallet.virtual-account');
    Route::post('/wallet/virtual-account', [VirtualAccountController::class, 'store'])->name('wallet.virtual-account.store');
    Route::get('/wallet/bank-account', [App\Http\Controllers\BankAccountController::class, 'edit'])->name('wallet.bank-account.edit');
    Route::post('/wallet/bank-account/resolve', [App\Http\Controllers\BankAccountController::class, 'resolve'])->name('wallet.bank-account.resolve');
    Route::post('/wallet/bank-account', [App\Http\Controllers\BankAccountController::class, 'store'])->name('wallet.bank-account.store');
    Route::post('/wallet/withdraw', [App\Http\Controllers\WalletActionController::class, 'withdraw'])->name('wallet.withdraw')->middleware('throttle:5,1');

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

// Gig accept/deliver/withdraw: work for both logged-in users and guests (guest identity via qelvo_guest_token cookie, checked inside the controller).
// Only 'web' middleware here - 'auth'/'signup.paid' would block guests entirely, defeating the guest-claim feature the controller implements.
Route::post('/gigs/{gig}/accept', [App\Http\Controllers\GigController::class, 'accept'])->name('gigs.accept')->middleware(['web']);
Route::post('/gigs/{gig}/deliver', [App\Http\Controllers\GigController::class, 'deliver'])->name('gigs.deliver')->middleware(['web']);
Route::post('/gigs/{gig}/withdraw', [App\Http\Controllers\GigController::class, 'withdraw'])->name('gigs.withdraw')->middleware(['web']);

// Signup fee (₦1,000 account activation) - requires login (user exists) but not signup_fee_paid
Route::middleware('auth')->group(function () {
    Route::get('/activate', function () { return view('auth.signup-fee-activate'); })->name('signup-fee.activate');
    Route::get('/signup-fee', [App\Http\Controllers\PaystackController::class, 'initiateSignupFee'])->name('signup-fee.initiate');
    Route::get('/signup-fee/callback', [App\Http\Controllers\PaystackController::class, 'signupFeeCallback'])->name('signup-fee.callback');
});

use App\Http\Controllers\AppearanceController;
Route::middleware('auth')->group(function () {
    Route::post('/settings/appearance', [AppearanceController::class, 'update'])->name('appearance.update');
    Route::get('/settings/appearance', function () {
        return view('settings.appearance');
    })->name('appearance.edit');
});

// Level & Badge routes
Route::middleware(['auth', 'signup.paid'])->group(function () {
    Route::prefix('levels-badges')->name('levels-badges.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LevelBadgeController::class, 'show'])->name('show');
        Route::get('/badges', [\App\Http\Controllers\LevelBadgeController::class, 'badges'])->name('badges');
        Route::get('/levels', [\App\Http\Controllers\LevelBadgeController::class, 'levels'])->name('levels');
    });
});

// Admin Level & Badge Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Levels
    Route::prefix('levels')->name('levels.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'levels'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdminLevelBadgeController::class, 'createLevel'])->name('create');
        Route::post('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'storeLevel'])->name('store');
        Route::get('/{level}/edit', [\App\Http\Controllers\AdminLevelBadgeController::class, 'editLevel'])->name('edit');
        Route::put('/{level}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'updateLevel'])->name('update');
        Route::delete('/{level}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'deleteLevel'])->name('destroy');
    });

    // Badges
    Route::prefix('badges')->name('badges.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'badges'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdminLevelBadgeController::class, 'createBadge'])->name('create');
        Route::post('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'storeBadge'])->name('store');
        Route::get('/{badge}/edit', [\App\Http\Controllers\AdminLevelBadgeController::class, 'editBadge'])->name('edit');
        Route::put('/{badge}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'updateBadge'])->name('update');
        Route::delete('/{badge}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'deleteBadge'])->name('destroy');
    });

    // Refresh user levels/badges
    Route::post('/users/{user}/refresh-badges', [\App\Http\Controllers\AdminLevelBadgeController::class, 'refreshUser'])->name('users.refresh-badges');
});

Route::middleware(['auth','admin'])->group(function () {
    Route::get('/admin/concierge-misses', [\App\Http\Controllers\Admin\ConciergeMissController::class, 'index'])->name('admin.concierge-misses.index');
    Route::post('/admin/concierge-misses/{miss}/resolve', [\App\Http\Controllers\Admin\ConciergeMissController::class, 'resolve'])->name('admin.concierge-misses.resolve');
});


Route::middleware('auth')->prefix('kyc')->name('kyc.')->group(function () {
    Route::get('/', [App\Http\Controllers\KycController::class, 'show'])->name('show');
    Route::post('/submit', [App\Http\Controllers\KycController::class, 'submit'])->name('submit');
    Route::post('/{step}', [App\Http\Controllers\KycController::class, 'save'])->name('save');
});


// ---- Free signup, area, plan gates ----
Route::aliasMiddleware('plan', \App\Http\Middleware\EnsurePlanAllows::class);
Route::post('/register', [\App\Http\Controllers\FreeRegisterController::class, 'store'])->middleware('throttle:5,1');
Route::middleware('auth')->group(function () {
    Route::get('/area', [\App\Http\Controllers\AreaController::class, 'show'])->name('area.show');
    Route::post('/area', [\App\Http\Controllers\AreaController::class, 'update'])->name('area.update');
});

// ===== NAURVEX SEO pages =====
Route::view('/about', 'about')->middleware('auth')->name('about');
Route::view('/faq', 'faq')->middleware('auth')->name('faq');
Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots']);
Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap']);

Route::redirect('/overview', '/dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/profit', [\App\Http\Controllers\Admin\ProfitStatsController::class, 'index'])->name('profit.index');
    Route::get('/profit/users/{user}', [\App\Http\Controllers\Admin\ProfitStatsController::class, 'user'])->name('profit.user');
});

Route::middleware(['auth', 'signup.paid'])->prefix('investment')->name('investment.')->group(function () {
    Route::get('/', [\App\Http\Controllers\InvestmentController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\InvestmentController::class, 'create'])->name('create');
    Route::post('/projects', [\App\Http\Controllers\InvestmentController::class, 'store'])->name('store');
    Route::get('/projects/{project}', [\App\Http\Controllers\InvestmentController::class, 'show'])->name('show');
    Route::post('/projects/{project}/invest', [\App\Http\Controllers\InvestmentController::class, 'invest'])->name('invest')->middleware('throttle:20,1');
    Route::post('/verification', [\App\Http\Controllers\InvestmentController::class, 'submitVerification'])->name('verification');
});

Route::middleware(['auth', 'admin'])->prefix('admin/investment')->name('admin.investment.')->group(function () {
    Route::get('/', [\App\Http\Controllers\InvestmentAdminController::class, 'index'])->name('index');
    Route::post('/verifications/{verification}', [\App\Http\Controllers\InvestmentAdminController::class, 'reviewVerification'])->name('verifications.review');
    Route::post('/projects/{project}/approve', [\App\Http\Controllers\InvestmentAdminController::class, 'approve'])->name('projects.approve');
    Route::post('/projects/{project}/release', [\App\Http\Controllers\InvestmentAdminController::class, 'release'])->name('projects.release');
    Route::post('/projects/{project}/returns', [\App\Http\Controllers\InvestmentAdminController::class, 'reportReturns'])->name('projects.returns');
    Route::post('/projects/{project}/distribute', [\App\Http\Controllers\InvestmentAdminController::class, 'distribute'])->name('projects.distribute');
    Route::post('/projects/{project}/cancel', [\App\Http\Controllers\InvestmentAdminController::class, 'cancel'])->name('projects.cancel');
});

Route::middleware(['auth', 'signup.paid'])->group(function () {
    Route::get('/golfvex', [\App\Http\Controllers\GolfVexController::class, 'index'])->name('golfvex.index');
    Route::get('/games', [\App\Http\Controllers\GamesController::class, 'index'])->name('games.index');
    Route::get('/games/play/{slug}', [\App\Http\Controllers\GamesController::class, 'play'])->name('games.play');
    Route::post('/golfvex/play', [\App\Http\Controllers\GolfVexController::class, 'play'])->name('golfvex.play')->middleware('throttle:30,1');
});

Route::middleware('auth')->get('/all-services', fn () => view('all-services'))->name('services.all');

Route::middleware('auth')->get('/profile-new', fn () => view('profile-layout'))->name('profile.layout');

Route::middleware('auth')->get('/assets-new', function () {
    $r = app('router')->getRoutes()->getByName('wallet.index');
    $res = $r ? app()->call($r->getAction('uses')) : null;
    if ($res instanceof \Illuminate\Http\RedirectResponse) { return $res; }
    $data = ($res instanceof \Illuminate\View\View) ? $res->getData() : [];
    return view('assets-layout', $data);
})->name('assets.new');

Route::get('/gx/{slug}/{path?}', [\App\Http\Controllers\GamesController::class, 'file'])->where('path', '.*')->name('games.file');

// ANNOUNCEMENTS (admin posts, shown on the overview)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}/toggle', [\App\Http\Controllers\Admin\AnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
});

// ANNOUNCEMENTS (all, for users)
Route::middleware('auth')->get('/announcements', [\App\Http\Controllers\AnnouncementsController::class, 'index'])->name('announcements.index');

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Gig;
use App\Models\Listing;
use App\Models\Subscription;
use App\Models\News;
use App\Models\Blog;
use App\Models\PlatformRevenue;
use App\Models\Invite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function revenue()
    {
        $this->guard();

        $totalAllTime = PlatformRevenue::sum('amount');
        $totalThisMonth = PlatformRevenue::where('created_at', '>=', now()->startOfMonth())->sum('amount');
        $totalThisWeek = PlatformRevenue::where('created_at', '>=', now()->startOfWeek())->sum('amount');
        $totalToday = PlatformRevenue::where('created_at', '>=', now()->startOfDay())->sum('amount');

        $bySource = PlatformRevenue::selectRaw('source, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        $recent = PlatformRevenue::latest()->take(50)->get();

        return view('admin.revenue', [
            'totalAllTime' => $totalAllTime,
            'totalThisMonth' => $totalThisMonth,
            'totalThisWeek' => $totalThisWeek,
            'totalToday' => $totalToday,
            'bySource' => $bySource,
            'recent' => $recent,
        ]);
    }

    private function guard()
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);
    }

    public function index()
    {
        $this->guard();
        $subStatusCounts = Subscription::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $subMethodRevenue = Subscription::where('status', 'active')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return view('admin.index', [
            'userCount' => User::count(),
            'bannedCount' => User::where('is_banned', true)->count(),
            'tierCounts' => User::selectRaw('tier, COUNT(*) as total')->groupBy('tier')->pluck('total', 'tier'),
            'totalWalletBalance' => User::sum('wallet_balance'),
            'gigCount' => Gig::count(),
            'openGigCount' => Gig::where('status', 'open')->count(),
            'listingCount' => Listing::count(),
            'newsCount' => News::count(),
            'blogCount' => Blog::count(),
            'recentUsers' => User::latest()->take(8)->get(),
            'recentTx' => Transaction::latest()->take(10)->get(),
            'totalRevenue' => Transaction::whereIn('type', ['tier_upgrade'])->sum('amount') * -1,
            'subActive' => $subStatusCounts['active'] ?? 0,
            'subPending' => $subStatusCounts['pending_payment'] ?? 0,
            'subExpired' => $subStatusCounts['expired'] ?? 0,
            'subCancelled' => $subStatusCounts['cancelled'] ?? 0,
            'subWalletRevenue' => $subMethodRevenue['wallet'] ?? 0,
            'subFlutterwaveRevenue' => $subMethodRevenue['flutterwave'] ?? 0,
            'expiringSoon' => Subscription::active()
                ->whereBetween('expires_at', [now(), now()->addDays(14)])
                ->with('user')->orderBy('expires_at')->take(10)->get(),
            'stuckPending' => Subscription::where('status', 'pending_payment')
                ->where('created_at', '<', now()->subHours(2))
                ->with('user')->latest()->take(10)->get(),
        ]);
    }

    // ===== USERS =====
    public function users(Request $r)
    {
        $this->guard();
        $q = User::query();
        if ($r->filled('search')) {
            $q->where(fn($w) => $w->where('name', 'like', "%{$r->search}%")->orWhere('email', 'like', "%{$r->search}%"));
        }
        if ($r->filled('tier')) $q->where('tier', $r->tier);
        if ($r->filled('banned')) $q->where('is_banned', $r->banned === '1');

        return view('admin.users', ['users' => $q->latest()->paginate(25)->withQueryString()]);
    }

    public function grantAccess(Request $r)
    {
        $this->guard();
        $users = collect();
        if ($r->filled('search')) {
            $users = User::where(fn($w) => $w->where('name', 'like', "%{$r->search}%")->orWhere('email', 'like', "%{$r->search}%"))
                ->limit(20)->get();
        }
        return view('admin.grant-access', ['users' => $users, 'search' => $r->search]);
    }

    public function grantAccessStore(Request $r)
    {
        $this->guard();
        $validated = $r->validate([
            'user_id' => 'required|exists:users,id',
            'tier' => 'required|in:tier1,tier2,tier3',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update([
            'signup_fee_paid' => true,
            'badge_tier' => 'bronze',
            'tier' => $validated['tier'],
            'tier_upgraded_at' => now(),
        ]);

        return back()->with('status', "Granted {$validated['tier']} access to {$user->name}.");
    }

    public function toggleAdmin(User $user)
    {
        $this->guard();
        abort_if($user->id === Auth::id(), 403, 'You cannot remove your own admin access.');
        $user->update(['is_admin' => !$user->is_admin]);
        return back()->with('status', $user->name . ' admin status updated.');
    }

    public function toggleBan(User $user)
    {
        $this->guard();
        abort_if($user->id === Auth::id(), 403, 'You cannot ban yourself.');
        $user->update(['is_banned' => !$user->is_banned]);
        if ($user->is_banned) {
            $user->notify(new \App\Notifications\AccountBanned());
        }
        return back()->with('status', $user->name . ($user->is_banned ? ' banned.' : ' unbanned.'));
    }

    // Flips a user in/out of the customer_care role. Does not touch is_admin -
    // customer_care is a separate, lesser-privileged role, not an admin variant.
    public function toggleCustomerCare(User $user)
    {
        $this->guard();
        abort_if($user->id === Auth::id(), 403, 'You cannot change your own role.');
        abort_if($user->isAdmin(), 403, 'Admins are not eligible for the customer_care role.');

        $newRole = $user->role === 'customer_care' ? 'user' : 'customer_care';
        $user->update(['role' => $newRole]);

        return back()->with('status', $user->name . ' is now ' . ($newRole === 'customer_care' ? 'Customer Care.' : 'a regular user.'));
    }

    // ===== CUSTOMER CARE INVITES =====
    public function invites()
    {
        $this->guard();
        return view('admin.invites', ['invites' => Invite::with(['creator', 'usedBy'])->latest()->paginate(25)]);
    }

    public function invitesStore(Request $r)
    {
        $this->guard();
        $r->validate(['expires_in_days' => 'nullable|integer|min:1|max:30']);
        Invite::generate(Auth::id(), 'customer_care', $r->input('expires_in_days', 7));
        return back()->with('status', 'Invite link generated.');
    }

    public function invitesRevoke(Invite $invite)
    {
        $this->guard();
        abort_if($invite->used_at, 403, 'This invite has already been used and cannot be revoked.');
        $invite->delete();
        return back()->with('status', 'Invite revoked.');
    }

    // Locked to prevent this admin write from racing with a concurrent user-side wallet
    // operation (topup, purchase, gig payout) on the same account and losing an update.
    public function adjustBalance(Request $r, User $user)
    {
        $this->guard();
        $data = $r->validate(['amount' => 'required|numeric', 'note' => 'nullable|string|max:255']);

        DB::transaction(function () use ($data, $user) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            $lockedUser->increment('wallet_balance', $data['amount']);
            Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'admin_adjustment',
                'amount' => $data['amount'],
                'description' => $data['note'] ?? 'Admin balance adjustment',
            ]);
            $lockedUser->notify(new \App\Notifications\AdminBalanceAdjusted($data['amount'], $data['note'] ?? null));
        });

        return back()->with('status', 'Balance adjusted for ' . $user->name);
    }

    // ===== GIGS =====
    public function gigs(Request $r)
    {
        $this->guard();
        $q = Gig::with('user')->latest();
        if ($r->filled('status')) $q->where('status', $r->status);
        return view('admin.gigs', ['gigs' => $q->paginate(25)->withQueryString()]);
    }

    public function deleteGig(Gig $gig)
    {
        $this->guard();
        $gig->delete();
        return back()->with('status', 'Gig removed.');
    }

    // ===== LISTINGS =====
    public function listings(Request $r)
    {
        $this->guard();
        $q = Listing::with('user')->latest();
        if ($r->filled('status')) $q->where('status', $r->status);
        return view('admin.listings', ['listings' => $q->paginate(25)->withQueryString()]);
    }

    public function deleteListing(Listing $listing)
    {
        $this->guard();
        $listing->delete();
        return back()->with('status', 'Listing removed.');
    }

    // ===== TRANSACTIONS =====
    public function transactions(Request $r)
    {
        $this->guard();
        $q = Transaction::with('user')->latest();
        if ($r->filled('type')) $q->where('type', $r->type);
        if ($r->filled('search')) {
            $q->whereHas('user', fn($w) => $w->where('name', 'like', "%{$r->search}%"));
        }
        return view('admin.transactions', ['transactions' => $q->paginate(30)->withQueryString()]);
    }

    // ===== SUBSCRIPTIONS =====
    public function subscriptions(Request $r)
    {
        $this->guard();
        $q = Subscription::with('user')->latest();
        if ($r->filled('status')) $q->where('status', $r->status);
        return view('admin.subscriptions', ['subscriptions' => $q->paginate(25)->withQueryString()]);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $this->guard();
        $subscription->update(['status' => 'cancelled']);
        return back()->with('status', 'Subscription cancelled.');
    }

    // ===== NEWS =====
    public function news()
    {
        $this->guard();
        return view('admin.news', ['posts' => News::latest('published_at')->paginate(25)]);
    }

    public function deleteNews(News $news)
    {
        $this->guard();
        $news->delete();
        return back()->with('status', 'Post removed.');
    }

    // ===== BLOG =====
    public function blog()
    {
        $this->guard();
        return view('admin.blog', ['posts' => Blog::with('user')->latest()->paginate(25)]);
    }

    public function deleteBlog(Blog $blog)
    {
        $this->guard();
        $blog->delete();
        return back()->with('status', 'Blog post removed.');
    }
}

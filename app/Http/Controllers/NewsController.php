<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    // Duration options in days => price in naira. Posting needs Gold (tier2) or above; admins post free.
    const AD_PRICING = [3 => 1500, 7 => 3000, 14 => 5000];
    const AD_CATEGORIES = ['Food', 'Fashion', 'Electronics', 'Books', 'Services', 'Rentals', 'Events', 'Beauty', 'Education', 'Other'];

    private function canPostAds($user): bool
    {
        return $user->is_admin || in_array($user->tier, ['tier2', 'tier3'], true);
    }

    public function index()
    {
        $newsItems = News::where('type', '!=', 'ad')
            ->orWhere(function ($q) {
                $q->where('type', 'ad')->where(function ($q2) {
                    $q2->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            })
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->get();

        return view('news.index', ['newsItems' => $newsItems]);
    }

    public function createAd()
    {
        abort_unless($this->canPostAds(Auth::user()), 403, 'Posting is available on the Gold plan and above.');
        return view('news.create-ad', ['pricing' => \App\Support\Pricing::adPrices()]);
    }

    public function storeAd(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->canPostAds($user), 403, 'Posting is available on the Gold plan and above.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', self::AD_CATEGORIES),
            'body' => 'required|string|max:600',
            'duration' => 'required|integer|in:3,7,30',
            'start_at' => 'nullable|date',
            'image' => 'nullable|image|max:6144',
        ]);

        $free = (bool) $user->is_admin;
        $cost = $free ? 0 : \App\Support\Pricing::adPrices()[(int) $validated['duration']];

        $start = now();
        if ($start->lt(now())) {
            $start = now();
        }
        $scheduled = $start->gt(now()->addMinute());

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = '/storage/' . $request->file('image')->store('news', 'public');
        }

        // Balance check + decrement + ad creation are one atomic unit so
        // concurrent submissions cannot overspend the wallet.
        $news = DB::transaction(function () use ($user, $validated, $cost, $free, $imageUrl, $start) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();

            if (!$free) {
                if ($locked->wallet_restricted) {
                    return null;
                }
                $qCost = (int) round($cost * (float) config('qcoin.q_per_naira_display', 20));
                $fromWallet = $locked->wallet_balance >= $cost
                    && !\App\Services\SpendLimit::blockedReason($locked, (float) $cost);
                if ($fromWallet) {
                    $locked->decrement('wallet_balance', $cost);
                    Transaction::create([
                        'user_id' => $locked->id,
                        'type' => 'ad_wallet',
                        'amount' => -$cost,
                        'description' => $validated['duration'] . '-day ad (wallet): ' . $validated['title'],
                    ]);
                } elseif ($locked->q_coin_balance >= $qCost) {
                    $locked->decrement('q_coin_balance', $qCost);
                    Transaction::create([
                        'user_id' => $locked->id,
                        'type' => 'ad_post',
                        'amount' => -$qCost,
                        'description' => $validated['duration'] . '-day ad (Q Coin): ' . $validated['title'],
                    ]);
                } else {
                    return null;
                }
            }

            return News::create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'body' => $validated['body'],
                'image_url' => $imageUrl,
                'type' => 'ad',
                'created_by' => $locked->id,
                'published_at' => $start,
                'expires_at' => $start->copy()->addDays((int) $validated['duration']),
                'is_featured' => $locked->tier === 'tier3' || (bool) $locked->is_admin,
            ]);
        });

        if (!$news) {
            $user->refresh();
            if ($user->wallet_restricted) {
                return back()->withErrors(['balance' => 'Your wallet is restricted due to an unresolved payment issue. Clear your balance to post ads.'])->withInput();
            }
            return back()->withErrors(['balance' => 'Not enough Q Coins. This ad costs ' . number_format($cost) . ' Q Coins.'])->withInput();
        }

        return redirect()
            ->route('pulse.index', $scheduled ? ['filter' => 'upcoming'] : [])
            ->with('status', $scheduled ? 'Ad scheduled.' : 'Ad is live for ' . $validated['duration'] . ' days.');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'body' => 'required|string',
            'type' => 'nullable|in:news,ad',
            'source_url' => 'nullable|url',
            'publish_at' => 'nullable|date',
            'image' => 'nullable|image|max:6144',
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = '/storage/' . $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            $validated['video_url'] = '/storage/' . $request->file('video')->store('news', 'public');
        }
        unset($validated['image'], $validated['video'], $validated['publish_at']);

        News::create([
            ...$validated,
            'type' => $validated['type'] ?? 'news',
            'created_by' => Auth::id(),
            'published_at' => $request->filled('publish_at') ? Carbon::parse($request->input('publish_at'), 'Africa/Lagos')->setTimezone('UTC') : now(),
        ]);

        return redirect()->route('news.index');
    }
}

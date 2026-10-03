<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    // Duration options in days => price in naira. tier1 (Plus) cannot post ads — requires tier2+.
    const AD_PRICING = [3 => 1500, 7 => 3000, 14 => 5000];

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
        abort_if(Auth::user()->tier === 'tier1', 403, 'Ads aren\'t available on the Silver plan — upgrade to Gold to post one.');
        return view('news.create-ad', ['pricing' => self::AD_PRICING]);
    }

    public function storeAd(Request $request)
    {
        $user = Auth::user();
        abort_if($user->tier === 'tier1', 403, 'Ads aren\'t available on the Silver plan — upgrade to Gold to post one.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'body' => 'required|string',
            'duration' => 'required|integer|in:3,7,14',
            'image' => 'nullable|image|max:6144',
        ]);

        $cost = self::AD_PRICING[(int) $validated['duration']];

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = '/storage/' . $request->file('image')->store('news', 'public');
        }

        // Balance check + decrement + ad creation must be one atomic unit,
        // otherwise concurrent submissions can both pass the balance check
        // before either decrements (race condition -> negative balance).
        $news = DB::transaction(function () use ($user, $validated, $cost, $imageUrl) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->wallet_restricted) {
                return null;
            }
            if ($lockedUser->wallet_balance < $cost) {
                return null;
            }

            $lockedUser->decrement('wallet_balance', $cost);

            Transaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'ad_post',
                'amount' => -$cost,
                'description' => $validated['duration'] . '-day ad: ' . $validated['title'],
            ]);

            return News::create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'body' => $validated['body'],
                'image_url' => $imageUrl,
                'type' => 'ad',
                'created_by' => $lockedUser->id,
                'published_at' => now(),
                'expires_at' => now()->addDays((int) $validated['duration']),
                'is_featured' => $lockedUser->tier === 'tier3',
            ]);
        });

        if (!$news) {
            $user->refresh();
            if ($user->wallet_restricted) {
                return back()->withErrors(['balance' => 'Your wallet is restricted due to an unresolved payment issue. Clear your balance to post ads.'])->withInput();
            }
            return back()->withErrors(['balance' => 'Insufficient wallet balance. This ad costs ₦' . number_format($cost) . '. Top up your wallet first.'])->withInput();
        }

        return redirect()->route('news.index')->with('status', 'Ad is live for ' . $validated['duration'] . ' days.');
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
            'image' => 'nullable|image|max:6144',
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = '/storage/' . $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            $validated['video_url'] = '/storage/' . $request->file('video')->store('news', 'public');
        }
        unset($validated['image'], $validated['video']);

        News::create([
            ...$validated,
            'type' => $validated['type'] ?? 'news',
            'created_by' => Auth::id(),
            'published_at' => now(),
        ]);

        return redirect()->route('news.index');
    }
}

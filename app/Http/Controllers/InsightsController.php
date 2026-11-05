<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsightsController extends Controller
{
    private function owner(string $table): ?string
    {
        if (!Schema::hasTable($table)) return null;
        foreach (['user_id', 'poster_id', 'created_by', 'owner_id'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    private function views($uid, $a, $b): int
    {
        if (!Schema::hasTable('profile_views')) return 0;
        $q = DB::table('profile_views')->where('profile_user_id', $uid);
        if (Schema::hasColumn('profile_views', 'created_at')) $q->whereBetween('created_at', [$a, $b]);
        return (int) $q->count();
    }

    private function followers($uid, $a, $b): int
    {
        if (!Schema::hasTable('follows')) return 0;
        return (int) DB::table('follows')->where('followee_id', $uid)->whereBetween('created_at', [$a, $b])->count();
    }

    private function content($uid, $a, $b): int
    {
        $n = 0;
        foreach (['gigs', 'listings', 'news'] as $t) {
            $o = $this->owner($t);
            if (!$o) continue;
            $q = DB::table($t)->where($o, $uid);
            if (Schema::hasColumn($t, 'created_at')) $q->whereBetween('created_at', [$a, $b]);
            $n += (int) $q->count();
        }
        return $n;
    }

    private function trend($now, $prev): string
    {
        return $now > $prev ? 'up' : ($now < $prev ? 'down' : 'flat');
    }

    private function tools(): array
    {
        return [
            ['slug' => 'monthly-recap', 'title' => 'Monthly recap', 'sub' => 'See what you made happen last month.', 'icon' => 'history', 'new' => true],
            ['slug' => 'best-practices', 'title' => 'Best practices', 'sub' => null, 'icon' => 'graduation-cap', 'new' => false],
            ['slug' => 'inspiration', 'title' => 'Inspiration', 'sub' => null, 'icon' => 'lightbulb', 'new' => false],
            ['slug' => 'ad-tools', 'title' => 'Ad tools', 'sub' => null, 'icon' => 'trending-up', 'new' => false],
            ['slug' => 'trial-reels', 'title' => 'Trial reels', 'sub' => null, 'icon' => 'play-square', 'new' => true],
            ['slug' => 'saved-replies', 'title' => 'Saved replies', 'sub' => 'Save replies to common questions.', 'icon' => 'send', 'new' => false],
        ];
    }

    public function dashboard()
    {
        $uid = Auth::id();
        $from = now()->subDays(29)->startOfDay();
        $to = now();
        $pFrom = $from->copy()->subDays(30);
        $pTo = $from->copy()->subSecond();

        $v = $this->views($uid, $from, $to);
        $f = $this->followers($uid, $from, $to);
        $c = $this->content($uid, $from, $to);

        return view('insights.dashboard', [
            'all' => false,
            'label' => $from->format('j M') . '-' . $to->format('j M'),
            'views' => $v, 'vt' => $this->trend($v, $this->views($uid, $pFrom, $pTo)),
            'followers' => $f, 'ft' => $this->trend($f, $this->followers($uid, $pFrom, $pTo)),
            'content' => $c,
            'tools' => $this->tools(),
        ]);
    }

    public function tools_page()
    {
        return view('insights.dashboard', ['all' => true, 'tools' => $this->tools()]);
    }

    public function page(string $slug)
    {
        $uid = Auth::id();
        $titles = [
            'monthly-recap' => 'Monthly recap', 'best-practices' => 'Best practices', 'inspiration' => 'Inspiration',
            'ad-tools' => 'Ad tools', 'trial-reels' => 'Trial reels', 'saved-replies' => 'Saved replies',
            'followers' => 'New followers', 'content' => 'Content you shared',
        ];
        abort_unless(isset($titles[$slug]), 404);
        $d = ['slug' => $slug, 'title' => $titles[$slug]];

        if ($slug === 'monthly-recap') {
            $a = now()->subMonthNoOverflow()->startOfMonth();
            $b = $a->copy()->endOfMonth();
            $d['month'] = $a->format('F Y');
            $d['recap'] = ['views' => $this->views($uid, $a, $b), 'followers' => $this->followers($uid, $a, $b), 'content' => $this->content($uid, $a, $b)];
        } elseif ($slug === 'best-practices') {
            $d['tips'] = [
                ['Reply fast', 'Buyers and employers pick whoever answers first. Aim to reply within an hour.'],
                ['Use a clear photo', 'A bright, simple picture on your profile and listings gets more taps.'],
                ['Write plain titles', 'Say exactly what you offer. "Math tutoring, 2000 per hour" beats "Great deal".'],
                ['Collect reviews', 'After every job or sale, ask for a review. Reputation drives your profile views.'],
                ['Post on a rhythm', 'Posting a little every week keeps you visible more than one big burst.'],
            ];
        } elseif ($slug === 'inspiration') {
            $d['ideas'] = [
                ['Gig idea', 'Offer assignment proofreading to first-year students.'],
                ['Market idea', 'Sell used textbooks with a short note on the condition.'],
                ['Pulse idea', 'Share a quick tip about surviving exam week.'],
                ['Profile idea', 'Add a short bio listing your three best skills.'],
                ['Offer idea', 'Give a small discount for your first ten customers.'],
            ];
        } elseif ($slug === 'ad-tools') {
            $posts = Schema::hasTable('news') ? DB::table('news')->where('created_by', $uid)->orderByDesc('id')->get() : collect();
            $d['posts'] = $posts;
            $d['tv'] = (int) $posts->sum('views');
            $d['tc'] = (int) $posts->sum('clicks');
        } elseif ($slug === 'saved-replies') {
            $d['replies'] = Schema::hasTable('saved_replies') ? DB::table('saved_replies')->where('user_id', $uid)->orderByDesc('id')->get() : collect();
        } elseif ($slug === 'followers') {
            $ids = Schema::hasTable('follows') ? DB::table('follows')->where('followee_id', $uid)->orderByDesc('created_at')->limit(50)->pluck('follower_id') : collect();
            $d['people'] = \App\Models\User::whereIn('id', $ids)->get();
        } elseif ($slug === 'content') {
            $items = [];
            $defs = [['gigs', 'Gig', 'briefcase'], ['listings', 'Listing', 'shopping-bag'], ['news', 'Pulse post', 'newspaper']];
            foreach ($defs as [$t, $kind, $icon]) {
                $o = $this->owner($t);
                if (!$o) continue;
                foreach (DB::table($t)->where($o, $uid)->orderByDesc('id')->limit(30)->get() as $r) {
                    $url = $t === 'gigs' ? route('gigs.show', $r->id) : ($t === 'listings' ? route('marketplace.show', $r->id) : url('/dashboard'));
                    $items[] = [
                        'title' => (string) ($r->title ?? ('#' . $r->id)), 'kind' => $kind, 'icon' => $icon, 'url' => $url,
                        'at' => !empty($r->created_at) ? Carbon::parse($r->created_at)->diffForHumans() : '',
                    ];
                }
            }
            $d['items'] = $items;
        }
        return view('insights.page', $d);
    }

    public function storeReply(Request $request)
    {
        $data = $request->validate(['body' => 'required|string|max:500']);
        DB::table('saved_replies')->insert(['user_id' => Auth::id(), 'body' => $data['body'], 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('insights.page', 'saved-replies');
    }

    public function destroyReply($id)
    {
        DB::table('saved_replies')->where('id', $id)->where('user_id', Auth::id())->delete();
        return redirect()->route('insights.page', 'saved-replies');
    }
}

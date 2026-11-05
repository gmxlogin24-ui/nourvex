<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminStudioController extends AdminOverviewController
{
    public function studio()
    {
        $d = $this->data();
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $vol = Transaction::where('created_at', '>=', now()->subDays(13)->startOfDay())->get()
            ->groupBy(fn ($x) => $x->created_at->format('Y-m-d'))
            ->map(fn ($g) => round($g->sum(fn ($x) => abs($x->amount)), 2));
        $d['volSeries'] = $days->map(fn ($k) => $vol[$k] ?? 0)->values();
        $d['gigSeries'] = $this->series(Gig::class, $days);
        $d['listSeries'] = $this->series(Listing::class, $days);

        $pct = function ($now, $prev) {
            return $prev > 0 ? round(($now - $prev) / $prev * 100, 1) : ($now > 0 ? 100 : 0);
        };
        $tw = Transaction::where('created_at', '>=', now()->subDays(7))->count();
        $tl = Transaction::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $uw = User::where('created_at', '>=', now()->subDays(7))->count();
        $ul = User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $d['txPct'] = $pct($tw, $tl);
        $d['txWeek'] = $tw;
        $d['userPct'] = $pct($uw, $ul);
        $d['userWeek'] = $uw;

        $ev = $d['events']->take(6)->sortBy(fn ($e) => $e['t']->timestamp)->values();
        $ts = $ev->map(fn ($e) => $e['t']->timestamp);
        $min = $ts->min() ?? 0;
        $span = max(1, ($ts->max() ?? 1) - $min);
        $d['gantt'] = $ev->map(function ($e) use ($min, $span) {
            $letters = preg_replace('/[^A-Za-z]/', '', $e['text']);
            return [
                'text' => $e['text'],
                'time' => $e['t']->format('H:i'),
                'left' => round(($e['t']->timestamp - $min) / $span * 40),
                'k' => $e['icon'] === 'arrow-left-right' ? 'd' : ($e['icon'] === 'user-plus' ? 'm' : 'l'),
                'ini' => strtoupper(substr($letters ?: 'A', 0, 1)),
            ];
        });

        $tabs = [
            ['Users', 'admin.users', 'users'], ['Transactions', 'admin.transactions', 'arrow-left-right'],
            ['Subscriptions', 'admin.subscriptions', 'credit-card'], ['Gigs', 'admin.gigs', 'briefcase'],
            ['Listings', 'admin.listings', 'store'], ['Disputes', 'admin.disputes.index', 'shield-alert'],
            ['Revenue', 'admin.revenue', 'bar-chart-3'], ['Coin Lab', 'admin.coin', 'coins'],
            ['Audit', 'admin.audit', 'shield-check'], ['Blog', 'admin.blog', 'file-text'],
            ['News', 'admin.news', 'newspaper'], ['Invites', 'admin.invites', 'mail'],
        ];
        $d['tabs'] = collect($tabs)->filter(fn ($t) => Route::has($t[1]))
            ->map(fn ($t) => ['label' => $t[0], 'url' => route($t[1]), 'icon' => $t[2]])->values();

        $d['pinUsers'] = $d['recentUsers']->take(5)->values();
        $d['pinTx'] = $d['recentTx']->take(4)->values();
        $d['pinGigs'] = Gig::latest()->take(4)->get();
        $d['pinListings'] = Listing::latest()->take(2)->get();
        $d['me'] = auth()->user();
        $d['links']['studio'] = route('admin.index');
        $d['links']['map'] = Route::has('admin.map') ? route('admin.map') : '#';
        $d['links']['combined'] = Route::has('admin.overview.combined') ? route('admin.overview.combined') : '#';

        return view('admin.overview-studio', $d);
    }
}

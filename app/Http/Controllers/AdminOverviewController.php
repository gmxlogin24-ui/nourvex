<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\GigDispute;
use App\Models\Listing;
use App\Models\ListingDispute;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class AdminOverviewController extends Controller
{
    protected function link($name)
    {
        return Route::has($name) ? route($name) : '#';
    }

    protected function series($model, $days)
    {
        $rows = $model::where('created_at', '>=', now()->subDays(13)->startOfDay())->pluck('created_at');
        $by = $rows->groupBy(fn ($d) => $d->format('Y-m-d'))->map->count();
        return $days->map(fn ($d) => $by[$d] ?? 0)->values();
    }

    protected function openDisputes()
    {
        $n = 0;
        foreach ([GigDispute::class, ListingDispute::class] as $m) {
            try {
                $t = (new $m)->getTable();
                $q = $m::query();
                if (Schema::hasColumn($t, 'status')) {
                    $q->whereIn('status', ['open', 'pending', 'under_review', 'escalated']);
                }
                $n += $q->count();
            } catch (\Throwable $e) {
            }
        }
        return $n;
    }

    protected function data()
    {
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));
        $subs = Subscription::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $stuck = Subscription::where('status', 'pending_payment')->where('created_at', '<', now()->subHours(2))->with('user')->latest()->take(10)->get();
        $expiring = Subscription::active()->whereBetween('expires_at', [now(), now()->addDays(14)])->with('user')->orderBy('expires_at')->take(10)->get();
        $banned = User::where('is_banned', true)->count();
        $disputes = $this->openDisputes();

        $events = collect();
        foreach (User::latest()->take(6)->get() as $u) {
            $events->push(['t' => $u->created_at, 'icon' => 'user-plus', 'text' => $u->name . ' joined']);
        }
        foreach (Transaction::with('user')->latest()->take(8)->get() as $x) {
            $events->push(['t' => $x->created_at, 'icon' => 'arrow-left-right', 'text' => ($x->user->name ?? 'User') . ' · ' . str_replace('_', ' ', $x->type) . ' · ₦' . number_format(abs($x->amount), 2)]);
        }
        foreach (Gig::latest()->take(4)->get() as $g) {
            $events->push(['t' => $g->created_at, 'icon' => 'briefcase', 'text' => 'New gig posted']);
        }
        foreach (Listing::latest()->take(4)->get() as $l) {
            $events->push(['t' => $l->created_at, 'icon' => 'store', 'text' => 'New listing added']);
        }

        return [
            'userCount' => User::count(),
            'bannedCount' => $banned,
            'tierCounts' => User::selectRaw('tier, COUNT(*) as total')->groupBy('tier')->pluck('total', 'tier'),
            'totalWalletBalance' => User::sum('wallet_balance'),
            'gigCount' => Gig::count(),
            'openGigCount' => Gig::where('status', 'open')->count(),
            'listingCount' => Listing::count(),
            'recentUsers' => User::latest()->take(10)->get(),
            'recentTx' => Transaction::with('user')->latest()->take(8)->get(),
            'totalRevenue' => Transaction::whereIn('type', ['tier_upgrade'])->sum('amount') * -1,
            'subActive' => $subs['active'] ?? 0,
            'subPending' => $subs['pending_payment'] ?? 0,
            'subExpired' => $subs['expired'] ?? 0,
            'subCancelled' => $subs['cancelled'] ?? 0,
            'expiringSoon' => $expiring,
            'stuckPending' => $stuck,
            'labels' => $days->map(fn ($d) => substr($d, 5))->values(),
            'signups' => $this->series(User::class, $days),
            'txSeries' => $this->series(Transaction::class, $days),
            'events' => $events->sortByDesc('t')->take(14)->values(),
            'attention' => [
                ['label' => 'Stuck payments', 'count' => $stuck->count(), 'hint' => 'pending over 2 hours', 'url' => $this->link('admin.subscriptions'), 'level' => $stuck->count() ? 'bad' : 'ok'],
                ['label' => 'Open disputes', 'count' => $disputes, 'hint' => 'gigs and marketplace', 'url' => $this->link('admin.disputes.index'), 'level' => $disputes ? 'bad' : 'ok'],
                ['label' => 'Expiring soon', 'count' => $expiring->count(), 'hint' => 'subscriptions, 14 days', 'url' => $this->link('admin.subscriptions'), 'level' => $expiring->count() ? 'mid' : 'ok'],
                ['label' => 'Banned users', 'count' => $banned, 'hint' => 'review list', 'url' => $this->link('admin.users'), 'level' => 'mid'],
            ],
            'links' => [
                'map' => $this->link('admin.index'),
                'bento' => $this->link('admin.overview.bento'),
                'mission' => $this->link('admin.overview.mission'),
                'users' => $this->link('admin.users'),
                'tx' => $this->link('admin.transactions'),
                'subs' => $this->link('admin.subscriptions'),
            ],
        ];
    }


}

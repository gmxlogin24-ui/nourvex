<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class AdminHomeController extends Controller
{
    private function cnt($t, $f = null)
    {
        try {
            if (!Schema::hasTable($t)) return null;
            $q = DB::table($t);
            if ($f) $f($q);
            return $q->count();
        } catch (\Throwable $e) { return null; }
    }

    private function sum($t, $col, $f = null)
    {
        try {
            if (!Schema::hasTable($t) || !Schema::hasColumn($t, $col)) return null;
            $q = DB::table($t);
            if ($f) $f($q);
            return round((float) $q->sum($col), 2);
        } catch (\Throwable $e) { return null; }
    }

    private function L(array $p)
    {
        $o = [];
        foreach ($p as $l) { if (Route::has($l[1])) $o[] = ['n' => $l[0], 'url' => route($l[1])]; }
        return $o;
    }

    public function index()
    {
        $d7 = now()->subDays(7);
        $today = now()->startOfDay();
        $on = $this->cnt('users', fn ($q) => $q->where('last_seen_at', '>=', now()->subMinutes(5)));
        $txT = $this->cnt('transactions', fn ($q) => $q->where('created_at', '>=', $today));
        $qT = $this->cnt('q_coin_transactions', fn ($q) => $q->where('created_at', '>=', $today));
        $act = $this->cnt('admin_audit_logs', fn ($q) => $q->where('created_at', '>=', now()->subDay()));
        $gd = $this->cnt('gig_disputes');
        $ld = $this->cnt('listing_disputes');
        $newU = $this->cnt('users', fn ($q) => $q->where('created_at', '>=', $today));
        $gT = $this->cnt('gigs', fn ($q) => $q->where('created_at', '>=', $today));
        $lT = $this->cnt('listings', fn ($q) => $q->where('created_at', '>=', $today));
        $calls = $this->cnt('cx_calls');

        $mods = [
            ['k' => 'users', 'n' => 'Users', 'i' => 'users', 'main' => $this->cnt('users'), 'act' => (int) $newU, 'url' => Route::has('admin.users') ? route('admin.users') : '#',
                'sub' => [['New this week', $this->cnt('users', fn ($q) => $q->where('created_at', '>=', $d7))], ['Online now', $on], ['Admins', $this->cnt('users', fn ($q) => $q->where('is_admin', 1))]],
                'links' => $this->L([['Users', 'admin.users'], ['Grant access', 'admin.grant-access'], ['Invites', 'admin.invites'], ['Levels', 'admin.levels.index'], ['Badges', 'admin.badges.index']])],
            ['k' => 'money', 'n' => 'Money', 'i' => 'arrow-left-right', 'main' => $this->cnt('transactions'), 'act' => (int) $txT, 'url' => Route::has('admin.transactions') ? route('admin.transactions') : '#',
                'sub' => [['Today', $txT], ['Volume today', $this->sum('transactions', 'amount', fn ($q) => $q->where('created_at', '>=', $today))]],
                'links' => $this->L([['Transactions', 'admin.transactions'], ['Revenue', 'admin.revenue'], ['Profit', 'admin.profit.index'], ['Investment', 'admin.investment.index'], ['Subscriptions', 'admin.subscriptions']])],
            ['k' => 'coin', 'n' => 'Coin Lab', 'i' => 'coins', 'main' => $this->sum('users', 'q_coin_balance'), 'act' => (int) $qT, 'url' => Route::has('admin.coin') ? route('admin.coin') : '#',
                'sub' => [['Q txs today', $qT], ['Q txs total', $this->cnt('q_coin_transactions')], ['Holders', $this->cnt('users', fn ($q) => $q->where('q_coin_balance', '>', 0))]],
                'links' => $this->L([['Coin Lab', 'admin.coin'], ['Investment', 'admin.investment.index']])],
            ['k' => 'gigs', 'n' => 'Gigs', 'i' => 'briefcase', 'main' => $this->cnt('gigs'), 'act' => (int) $gT, 'url' => Route::has('admin.gigs') ? route('admin.gigs') : '#',
                'sub' => [['New this week', $this->cnt('gigs', fn ($q) => $q->where('created_at', '>=', $d7))], ['Today', $gT]],
                'links' => $this->L([['Gigs', 'admin.gigs'], ['Gig disputes', 'admin.disputes.index']])],
            ['k' => 'market', 'n' => 'Market', 'i' => 'shopping-bag', 'main' => $this->cnt('listings'), 'act' => (int) $lT, 'url' => Route::has('admin.listings') ? route('admin.listings') : '#',
                'sub' => [['New this week', $this->cnt('listings', fn ($q) => $q->where('created_at', '>=', $d7))], ['Today', $lT]],
                'links' => $this->L([['Listings', 'admin.listings'], ['Market disputes', 'admin.marketplace-disputes.index']])],
            ['k' => 'disputes', 'n' => 'Disputes', 'i' => 'scale', 'main' => ($gd === null && $ld === null) ? null : (int) $gd + (int) $ld, 'act' => 0, 'url' => Route::has('admin.disputes.index') ? route('admin.disputes.index') : '#',
                'sub' => [['Gig disputes', $gd], ['Market disputes', $ld]],
                'links' => $this->L([['Gig disputes', 'admin.disputes.index'], ['Market disputes', 'admin.marketplace-disputes.index']])],
            ['k' => 'subs', 'n' => 'Subs', 'i' => 'credit-card', 'main' => $this->cnt('subscriptions'), 'act' => 0, 'url' => Route::has('admin.subscriptions') ? route('admin.subscriptions') : '#',
                'sub' => [['New this week', $this->cnt('subscriptions', fn ($q) => $q->where('created_at', '>=', $d7))]],
                'links' => $this->L([['Subscriptions', 'admin.subscriptions']])],
            ['k' => 'chat', 'n' => 'Chat', 'i' => 'messages-square', 'main' => $this->cnt('conversations'), 'act' => (int) $calls, 'url' => Route::has('admin.conversations.index') ? route('admin.conversations.index') : '#',
                'sub' => [['Calls', $calls], ['Missed calls', $this->cnt('cx_calls', fn ($q) => $q->where('status', 'missed'))]],
                'links' => $this->L([['Chats', 'admin.conversations.index'], ['Alerts', 'admin.announcements.index']])],
            ['k' => 'security', 'n' => 'Security', 'i' => 'shield-check', 'main' => $act, 'act' => (int) $act, 'url' => Route::has('admin.audit') ? route('admin.audit') : '#',
                'sub' => [['Actions (24h)', $act], ['All logged', $this->cnt('admin_audit_logs')], ['Admins', $this->cnt('users', fn ($q) => $q->where('is_admin', 1))]],
                'links' => $this->L([['Audit log', 'admin.audit'], ['Grant access', 'admin.grant-access']])],
            ['k' => 'control', 'n' => 'Platform', 'i' => 'sliders-horizontal', 'main' => null, 'act' => 0, 'url' => Route::has('admin.feature-gates.index') ? route('admin.feature-gates.index') : '#',
                'sub' => [['Levels', $this->cnt('levels')], ['Badges', $this->cnt('badges')], ['Announcements', $this->cnt('announcements')]],
                'links' => $this->L([['Features', 'admin.feature-gates.index'], ['Appearance', 'admin.settings.appearance'], ['News', 'admin.news'], ['Blog', 'admin.blog'], ['Levels', 'admin.levels.index'], ['Badges', 'admin.badges.index']])],
        ];
        $strip = [['Online', $on], ['Tx today', $txT], ['Q txs today', $qT], ['Admin 24h', $act]];
        return view('admin.command', ['mods' => $mods, 'strip' => $strip]);
    }
}

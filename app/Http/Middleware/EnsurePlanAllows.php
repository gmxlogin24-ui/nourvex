<?php

namespace App\Http\Middleware;

use App\Models\Listing;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePlanAllows
{
    protected array $messages = [
        'gigs_post' => 'Posting gigs needs the Gold plan or above.',
        'market_sell' => 'Selling on the marketplace needs the Silver plan or above.',
        'market_buy' => 'Buying on the marketplace needs the Silver plan or above.',
    ];

    // Usage: ->middleware('plan:gigs_post')
    public function handle(Request $request, Closure $next, string $rule)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->planAllows($rule)) {
            return redirect()->route('subscriptions.index')->withErrors([
                'tier' => $this->messages[$rule] ?? 'Upgrade your plan to do this.',
            ]);
        }

        if ($rule === 'market_sell' && $request->isMethod('post') && !$user->isAdmin()) {
            $cap = $user->planRule('market_weekly_cap');
            if ($cap !== null) {
                $used = Listing::where('user_id', $user->id)->where('created_at', '>=', now()->startOfWeek())->count();
                if ($used >= $cap) {
                    return back()->withErrors([
                        'tier' => 'You have reached your weekly listing limit (' . $cap . ') on the ' . $user->planLabel() . ' plan. Upgrade for a higher limit.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}

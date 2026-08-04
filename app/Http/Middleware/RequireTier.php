<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Gig;
use App\Models\Listing;

class RequireTier
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->tier === 'free') {
            return redirect()->route('tier.index')->withErrors([
                'tier' => 'You need to upgrade to Tier 1 to post gigs or marketplace listings.'
            ]);
        }

        // Only check the limit on the actual submit (POST), not the form page (GET)
        if ($request->isMethod('post') && $user->tier === 'tier1') {
            $activeGigs = Gig::where('user_id', $user->id)->where('status', 'open')->count();
            $activeListings = Listing::where('user_id', $user->id)->count();

            if (($activeGigs + $activeListings) >= 5) {
                return back()->withErrors([
                    'tier' => 'Tier 1 allows up to 5 active posts. Upgrade to Tier 2 for unlimited posts.'
                ]);
            }
        }

        return $next($request);
    }
}

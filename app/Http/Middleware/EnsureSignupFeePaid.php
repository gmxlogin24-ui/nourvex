<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSignupFeePaid
{
    // Routes an unpaid-but-authenticated user still needs: paying the fee itself,
    // the vendor fee (which now happens right after the base fee), managing their
    // own profile, and logging out. Admin accounts bypass this entirely.
    protected array $exemptRouteNames = [
        'vendor.activate', 'vendor.pay', 'vendor.callback',
        'profile.index', 'profile.update', 'profile.password',
    ];

    // Unpaid users get exactly 1 free message (gigs or marketplace, combined)
    // before this middleware starts blocking them like everything else.
    protected array $freeMessageRouteNames = [
        'gigs.message', 'marketplace.message',
    ];

    protected int $freeMessageLimit = 1;

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->signup_fee_paid && !$user->isCustomerCareOrAdmin()) {
            $routeName = $request->route()?->getName();

            if (in_array($routeName, $this->exemptRouteNames, true)) {
                return $next($request);
            }

            if (in_array($routeName, $this->freeMessageRouteNames, true)) {
                if ($user->free_messages_used < $this->freeMessageLimit) {
                    $response = $next($request);
                    // Only count it if the request actually succeeded - a validation
                    // error or failure shouldn't burn the user's one free message.
                    if ($response->isSuccessful() || $response->isRedirection()) {
                        $user->increment('free_messages_used');
                    }
                    return $response;
                }
                // Free message already used - fall through to the paywall below.
            }

            return redirect()->route('signup-fee.activate')
                ->withErrors(['payment' => 'Activate your account (₦2,000) to continue.']);
        }

        return $next($request);
    }
}

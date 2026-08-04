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

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->signup_fee_paid && !$user->is_admin) {
            $routeName = $request->route()?->getName();
            if (!in_array($routeName, $this->exemptRouteNames, true)) {
                return redirect()->route('signup-fee.activate')
                    ->withErrors(['payment' => 'Activate your account (₦1,000) to continue.']);
            }
        }

        return $next($request);
    }
}
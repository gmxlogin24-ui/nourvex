<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class FreeRegisterController extends Controller
{
    public function store(Request $request)
    {
        $key = 'register|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => 'Too many signup attempts from this network. Please try again in ' . ceil($seconds / 60) . ' minute(s).'])->withInput();
        }
        RateLimiter::hit($key, 3600);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'referral_code' => 'nullable|string',
            'gender' => 'required|in:male,female,other',
            'spoken_language' => 'required|string|max:100',
            'area' => 'required|in:' . implode(',', array_keys(config('areas.list'))),
            'intended_plan' => 'nullable|in:free,tier1,tier2,tier3',
        ]);

        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', strtoupper(trim($validated['referral_code'])))->first();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'spoken_language' => $validated['spoken_language'],
            'area' => $validated['area'],
            'referral_code' => strtoupper(Str::random(6)),
            'referred_by' => $referrer?->id,
            'account_type' => 'guest',
            'tier' => 'free',
        ]);

        if ($referrer) {
            try {
                app(\App\Services\RankingService::class)->award($referrer, 'referral', null, 'referred:' . $user->id);
            } catch (\Throwable $e) {
                // never block signup because of ranking
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        $plan = $validated['intended_plan'] ?? 'free';
        if (in_array($plan, ['tier1', 'tier2', 'tier3'], true)) {
            $request->session()->put('intended_plan_after_onboarding', $plan);
        }

        return redirect()->route('onboarding.interests')->with('status', 'Welcome! Your free account is ready.');
    }
}

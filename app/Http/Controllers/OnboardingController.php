<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    // Categories now live in config/gig_categories.php (the single source
    // of truth also used by GigController and the create/edit forms) rather
    // than a separate hardcoded list that could drift out of sync.
    public static function categories(): array
    {
        return array_keys(config('gig_categories'));
    }

    public function show()
    {
        return view('onboarding.interests', ['categories' => self::categories()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'interests' => 'required|array|min:1|max:5',
        ]);

        Auth::user()->update(['interests' => $validated['interests']]);

        $intendedPlan = $request->session()->pull('intended_plan_after_onboarding');
        if (in_array($intendedPlan, ['tier1', 'tier2', 'tier3'], true)) {
            return redirect()->route('subscriptions.index', ['plan' => $intendedPlan])
                ->with('status', 'You\'re all set — complete payment below to activate your plan.');
        }

        return redirect()->route('plans.choose')
            ->with('status', 'You\'re all set! Now pick a plan to finish setting up your account.');
    }
}

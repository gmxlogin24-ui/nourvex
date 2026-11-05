<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        if (request()->filled('ref')) {
            session(['google_ref' => strtoupper(trim((string) request('ref')))]);
        }
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        $isNewUser = false;

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $isNewUser = true;
            $refCode = session()->pull('google_ref');
            $referrer = $refCode ? User::where('referral_code', $refCode)->first() : null;
            $user = User::create([
                'referred_by' => $referrer?->id,
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Student',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                'avatar_url' => $googleUser->getAvatar(),
                'account_type' => 'guest',
            ]);
        }

        Auth::login($user, true);

        // New Google signups haven't paid the signup fee yet - send them to the same
        // activation step normal signups hit, instead of straight to dashboard.
        // Existing unpaid users get caught by the signup.paid middleware automatically.
        if ($isNewUser) {
            return redirect()->route('area.show')
                ->with('status', 'Welcome! Tell us where you are based to finish setting up.');
        }

        return redirect()->route('dashboard');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showGuestRegister()
    {
        return view('auth.register-guest');
    }

    public function showVendorRegister()
    {
        return view('auth.register-vendor');
    }

    public function showInviteRegister(string $code)
    {
        $invite = \App\Models\Invite::where('code', $code)->first();
        if (!$invite || !$invite->isValid()) {
            abort(404, 'This invite link is invalid or has expired.');
        }
        return view('auth.register-invite', ['code' => $code]);
    }

    public function registerInvite(Request $request, string $code)
    {
        $invite = \App\Models\Invite::where('code', $code)->first();
        if (!$invite || !$invite->isValid()) {
            abort(404, 'This invite link is invalid or has expired.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:male,female,other',
            'spoken_language' => 'required|string|max:100',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'spoken_language' => $validated['spoken_language'],
            'referral_code' => strtoupper(Str::random(6)),
            'account_type' => 'guest',
            'role' => $invite->role,
        ]);

        $invite->update(['used_at' => now(), 'used_by' => $user->id]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Welcome to the team!');
    }

    public function register(Request $request)
    {
        $registerThrottleKey = 'register|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($registerThrottleKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($registerThrottleKey);
            return back()->withErrors(['email' => 'Too many signup attempts from this network. Please try again in ' . ceil($seconds / 60) . ' minute(s).'])->withInput();
        }
        \Illuminate\Support\Facades\RateLimiter::hit($registerThrottleKey, 3600);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'referral_code' => 'nullable|string',
            'gender' => 'required|in:male,female,other',
            'spoken_language' => 'required|string|max:100',
            'phonenumber' => 'nullable|string|max:15',
            'bvn' => 'nullable|string|size:11|required_with:phonenumber',
        ]);

        // No account is created yet. We hold the submitted details (encrypted)
        // in the session until payment is confirmed, so nobody ends up with a
        // half-registered account they feel entitled to before they've paid.
        $intendedPlan = $request->input('intended_plan');

        $pending = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'spoken_language' => $validated['spoken_language'],
            'referral_code_used' => $validated['referral_code'] ?? null,
            'phonenumber' => $validated['phonenumber'] ?? null,
            'bvn' => $validated['bvn'] ?? null,
            'intended_plan' => in_array($intendedPlan, ['tier1', 'tier2', 'tier3'], true) ? $intendedPlan : null,
        ];

        $request->session()->put(
            'pending_registration',
            \Illuminate\Support\Facades\Crypt::encryptString(serialize($pending))
        );
        $request->session()->forget('pending_registration_txref');

        return redirect()->route('register.pay');
    }

    public function showRegistrationPay(Request $request)
    {
        $pending = $this->getPendingRegistration($request);
        if (!$pending) {
            return redirect()->route('register')->withErrors(['email' => 'Your registration session expired. Please fill the form again.']);
        }

        return view('auth.register-pay', ['pendingName' => $pending['name']]);
    }

    protected function getPendingRegistration(Request $request): ?array
    {
        $encrypted = $request->session()->get('pending_registration');
        if (!$encrypted) {
            return null;
        }
        try {
            return unserialize(\Illuminate\Support\Facades\Crypt::decryptString($encrypted));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function trySetupVirtualAccountPublic($user, string $phonenumber, string $bvn): void
    {
        $this->trySetupVirtualAccount($user, $phonenumber, $bvn);
    }

    private function trySetupVirtualAccount($user, string $phonenumber, string $bvn): void
    {
        $nameParts = explode(' ', trim($user->name), 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? $nameParts[0];

        try {
            $secret = config('services.paystack.secret');

            $customerRes = Http::withToken($secret)->post('https://api.paystack.co/customer', [
                'email' => $user->email,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'phone' => $phonenumber,
            ]);
            $customerCode = $customerRes->json()['data']['customer_code'] ?? null;
            if (!$customerCode) {
                return;
            }

            Http::withToken($secret)->post("https://api.paystack.co/customer/{$customerCode}/identification", [
                'country' => 'NG',
                'type' => 'bvn',
                'value' => $bvn,
                'bvn' => $bvn,
                'first_name' => $firstname,
                'last_name' => $lastname,
            ]);
            // $bvn goes out of scope here and is never stored - only Paystack's response is saved

            $dvaRes = Http::withToken($secret)->post('https://api.paystack.co/dedicated_account', [
                'customer' => $customerCode,
                'preferred_bank' => 'wema-bank',
            ]);
            $dvaData = $dvaRes->json();

            if (($dvaData['status'] ?? false) === true) {
                $user->update([
                    'virtual_account_number' => $dvaData['data']['account_number'],
                    'virtual_account_bank' => $dvaData['data']['bank']['name'] ?? 'Wema Bank',
                    'virtual_account_ref' => $customerCode,
                ]);
            }
        } catch (\Throwable $e) {
            // Silently skip - user can still set this up later from their wallet page
        }
    }

    public function registerVendor(Request $request)
    {
        $registerThrottleKey = 'register|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($registerThrottleKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($registerThrottleKey);
            return back()->withErrors(['email' => 'Too many signup attempts from this network. Please try again in ' . ceil($seconds / 60) . ' minute(s).'])->withInput();
        }
        \Illuminate\Support\Facades\RateLimiter::hit($registerThrottleKey, 3600);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'business_name' => 'required|string|max:255',
            'business_whatsapp' => 'required|string|max:20',
            'business_logo' => 'nullable|image|max:4096',
            'referral_code' => 'nullable|string',
        ]);

        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = \App\Models\User::where('referral_code', $validated['referral_code'])->first();
        }

        $logoPath = null;
        if ($request->hasFile('business_logo')) {
            $logoPath = $request->file('business_logo')->store('business-logos', 'public');
        }

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'referral_code' => strtoupper(Str::random(6)),
            'referred_by' => $referrer?->id,
            'account_type' => 'vendor',
            'business_name' => $validated['business_name'],
            'business_whatsapp' => $validated['business_whatsapp'],
            'business_logo' => $logoPath ? '/storage/' . $logoPath : null,
            'is_vendor_active' => false,
        ]);

        Auth::login($user);

        return redirect()->route('vendor.activate');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $throttleKey = \Illuminate\Support\Str::lower($credentials['email']) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['email' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).']);
        }

        if (Auth::attempt($credentials)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            if (Auth::user()->role === 'customer_care') {
                return redirect()->route('customer-care.index');
            }
            return redirect()->route('dashboard');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => 'We could not find an account with that email.']);
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset($validated, function ($user, $password) {
            $user->update(['password' => $password]);
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password reset successfully. You can now log in.');
        }

        $message = match ($status) {
            Password::INVALID_USER => 'We could not find an account with that email.',
            Password::INVALID_TOKEN => 'This reset link is invalid or has already been used.',
            default => 'This reset link has expired. Please request a new one.',
        };

        return back()->withErrors(['email' => $message]);
    }
}

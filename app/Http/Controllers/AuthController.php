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

    public function register(Request $request)
    {
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

        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = \App\Models\User::where('referral_code', $validated['referral_code'])->first();
        }

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'],
            'spoken_language' => $validated['spoken_language'],
            'referral_code' => strtoupper(Str::random(6)),
            'referred_by' => $referrer?->id,
            'account_type' => 'guest',
        ]);

        Auth::login($user);

        // Optional: if they supplied BVN + phone, set up their virtual account immediately.
        // Never fails registration itself - just silently skips on any error, same as leaving it blank.
        if (!empty($validated['bvn']) && !empty($validated['phonenumber'])) {
            $this->trySetupVirtualAccount($user, $validated['phonenumber'], $validated['bvn']);
        }

        $intendedPlan = $request->input('intended_plan');
        if (in_array($intendedPlan, ['tier1', 'tier2', 'tier3'], true)) {
            $request->session()->put('intended_plan_after_onboarding', $intendedPlan);
        }

        return redirect()->route('signup-fee.activate')
            ->with('status', 'Welcome! One last step to activate your account.');
    }

    private function trySetupVirtualAccount($user, string $phonenumber, string $bvn): void
    {
        $nameParts = explode(' ', trim($user->name), 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? $nameParts[0];

        try {
            $response = Http::withToken(env('FLW_SECRET_KEY'))
                ->post('https://api.flutterwave.com/v3/virtual-account-numbers', [
                    'email' => $user->email,
                    'tx_ref' => 'VA-' . $user->id . '-' . time(),
                    'phonenumber' => $phonenumber,
                    'is_permanent' => true,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'narration' => 'Campulsy - ' . $user->name,
                    'bvn' => $bvn,
                ]);
            // $bvn goes out of scope here and is never stored - only Flutterwave's response is saved

            $data = $response->json();

            if (($data['status'] ?? null) === 'success') {
                $user->update([
                    'virtual_account_number' => $data['data']['account_number'],
                    'virtual_account_bank' => $data['data']['bank_name'],
                    'virtual_account_ref' => $data['data']['flw_ref'],
                ]);
            }
        } catch (\Throwable $e) {
            // Silently skip - user can still set this up later from their wallet page
        }
    }

    public function registerVendor(Request $request)
    {
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
            'password' => Hash::make($validated['password']),
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

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
            $user->update(['password' => Hash::make($password)]);
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset successfully. You can now log in.')
            : back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
    }
}

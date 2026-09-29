<?php

namespace App\Http\Controllers;

use App\Services\LevelBadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(protected LevelBadgeService $levelBadgeService) {}

    public function index()
    {
        $user = Auth::user();

        $result = $this->levelBadgeService->refresh($user);
        $earnedBadges = $user->badges()->get();

        return view('profile.index', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
            'currentLevel' => $result['level'],
            'earnedBadges' => $earnedBadges->take(6),
            'totalBadgesEarned' => $earnedBadges->count(),
        ]);
    }

    public function reputation()
    {
        $user = Auth::user();

        return view('profile.reputation', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
        ]);
    }

    public function account()
    {
        return view('profile.account', ['user' => Auth::user()]);
    }

    public function appearance()
    {
        return view('profile.appearance', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'accent_color' => 'nullable|string|max:7',
            'theme_style' => 'nullable|string|in:glass,neumorphic,plain,aurora',
            'mobile_nav_style' => 'nullable|string|in:painted_pill,edge_dock',
            'sidebar_style' => 'nullable|string|in:classic,rail',
            'avatar' => 'nullable|image|max:4096',
            'background' => 'nullable|image|max:8192',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_url'] = '/storage/' . $path;
        }

        if ($request->hasFile('background')) {
            $path = $request->file('background')->store('backgrounds', 'public');
            $validated['background_url'] = '/storage/' . $path;
        }

        unset($validated['avatar'], $validated['background']);

        $user->update($validated);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('status', 'Password updated.');
    }

    public function toggleThemeStyle(Request $request)
    {
        $user = $request->user();
        $user->theme_style = ($user->theme_style ?? 'glass') === 'qelvo-dark' ? 'glass' : 'qelvo-dark';
        $user->save();
        return back()->with('status', $user->theme_style === 'qelvo-dark' ? 'NOURVEX Dark enabled' : 'NOURVEX Dark disabled');
    }
}

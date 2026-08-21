<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', [
            'user' => $user,
            'workerLevel' => $user->workerLevel(),
            'completedGigsCount' => $user->completedGigsCount(),
            'averageRating' => $user->averageRatingReceived(),
            'reviewsReceivedCount' => $user->reviewsReceivedCount(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'accent_color' => 'nullable|string|max:7',
            'theme_style' => 'nullable|string|in:glass,neumorphic,plain',
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

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('status', 'Password updated.');
    }
}

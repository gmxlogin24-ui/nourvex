<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function show()
    {
        return view('onboarding.area', ['areas' => config('areas.list'), 'current' => auth()->user()->area]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'area' => 'required|in:' . implode(',', array_keys(config('areas.list'))),
        ]);

        $user = $request->user();
        $user->update(['area' => $validated['area'], 'area_verified_at' => null]);

        if (empty($user->interests)) {
            return redirect()->route('onboarding.interests');
        }
        return redirect()->route('dashboard')->with('status', 'Area updated.');
    }
}

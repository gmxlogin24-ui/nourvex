<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLevelBadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * List all levels
     */
    public function levels()
    {
        $levels = Level::orderBy('rank')->get();
        return view('admin.levels.index', ['pageKey' => 'admin-levels', 'levels' => $levels]);
    }

    /**
     * Show form to create level
     */
    public function createLevel()
    {
        return view('admin.levels.create', ['pageKey' => 'admin-levels']);
    }

    /**
     * Store new level
     */
    public function storeLevel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:levels',
            'rank' => 'required|integer|unique:levels',
            'min_q_coins' => 'required|integer|min:0',
            'min_gigs_completed' => 'required|integer|min:0',
            'min_referrals' => 'required|integer|min:0',
            'requires_verified' => 'boolean',
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        Level::create($validated);
        return redirect()->route('admin.levels.index')->with('success', 'Level created!');
    }

    /**
     * Show form to edit level
     */
    public function editLevel(Level $level)
    {
        return view('admin.levels.edit', ['pageKey' => 'admin-levels', 'level' => $level]);
    }

    /**
     * Update level
     */
    public function updateLevel(Request $request, Level $level)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:levels,name,' . $level->id,
            'rank' => 'required|integer|unique:levels,rank,' . $level->id,
            'min_q_coins' => 'required|integer|min:0',
            'min_gigs_completed' => 'required|integer|min:0',
            'min_referrals' => 'required|integer|min:0',
            'requires_verified' => 'boolean',
            'benefits' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $level->update($validated);
        return redirect()->route('admin.levels.index')->with('success', 'Level updated!');
    }

    /**
     * Delete level
     */
    public function deleteLevel(Level $level)
    {
        $level->delete();
        return redirect()->route('admin.levels.index')->with('success', 'Level deleted!');
    }

    /**
     * List all badges
     */
    public function badges()
    {
        $badges = Badge::orderBy('name')->get();
        return view('admin.badges.index', ['pageKey' => 'admin-badges', 'badges' => $badges]);
    }

    /**
     * Show form to create badge
     */
    public function createBadge()
    {
        return view('admin.badges.create', ['pageKey' => 'admin-badges']);
    }

    /**
     * Store new badge
     */
    public function storeBadge(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:badges',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'icon' => 'required|string|max:10',
            'criteria_type' => 'required|in:gigs_completed,referrals,q_coins,streak,verified,profile_complete,early_member',
            'criteria_value' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Badge::create($validated);
        return redirect()->route('admin.badges.index')->with('success', 'Badge created!');
    }

    /**
     * Show form to edit badge
     */
    public function editBadge(Badge $badge)
    {
        return view('admin.badges.edit', ['pageKey' => 'admin-badges', 'badge' => $badge]);
    }

    /**
     * Update badge
     */
    public function updateBadge(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:badges,key,' . $badge->id,
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'icon' => 'required|string|max:10',
            'criteria_type' => 'required|in:gigs_completed,referrals,q_coins,streak,verified,profile_complete,early_member',
            'criteria_value' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $badge->update($validated);
        return redirect()->route('admin.badges.index')->with('success', 'Badge updated!');
    }

    /**
     * Delete badge
     */
    public function deleteBadge(Badge $badge)
    {
        $badge->delete();
        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted!');
    }

    /**
     * Refresh user levels and badges
     */
    public function refreshUser(Request $request, \App\Models\User $user)
    {
        $service = app(\App\Services\LevelBadgeService::class);
        $result = $service->refresh($user);

        $message = 'User refreshed. ';
        if (count($result['new_badges']) > 0) {
            $message .= count($result['new_badges']) . ' new badge(s) earned. ';
        }
        if ($result['leveled_up']) {
            $message .= 'User leveled up to ' . $result['leveled_up']->name . '!';
        }

        return back()->with('success', $message);
    }
}

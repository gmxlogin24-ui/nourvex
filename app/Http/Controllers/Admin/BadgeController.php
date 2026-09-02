<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BadgeController extends Controller
{
    public function index()
    {
        return view('admin.badges.index', [
            'badges' => Badge::orderBy('sort_order')->withCount('users')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.badges.create', ['badge' => new Badge(['is_active' => true, 'criteria_value' => 1])]);
    }

    public function store(Request $request)
    {
        Badge::create($this->validated($request));

        return redirect()->route('admin.badges.index')->with('success', 'Badge created.');
    }

    public function edit(Badge $badge)
    {
        return view('admin.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge)
    {
        $badge->update($this->validated($request, $badge));

        return redirect()->route('admin.badges.index')->with('success', 'Badge updated.');
    }

    public function destroy(Badge $badge)
    {
        $badge->users()->detach();
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted.');
    }

    protected function validated(Request $request, ?Badge $badge = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('badges')->ignore($badge)],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
            'criteria_type' => ['required', Rule::in(array_keys(Badge::CRITERIA))],
            'criteria_value' => ['required', 'integer', 'min:1'],
            'q_coin_reward' => ['required', 'integer', 'min:0', 'max:100000'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

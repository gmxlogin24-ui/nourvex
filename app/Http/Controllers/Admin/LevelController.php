<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index()
    {
        return view('admin.levels.index', [
            'levels' => Level::orderBy('level_number')->withCount('users')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.levels.create', ['level' => new Level(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        Level::create($this->validated($request));

        return redirect()->route('admin.levels.index')->with('success', 'Level created.');
    }

    public function edit(Level $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $level->update($this->validated($request, $level));

        return redirect()->route('admin.levels.index')->with('success', 'Level updated.');
    }

    public function destroy(Level $level)
    {
        $level->users()->update(['current_level_id' => null]);
        $level->delete();

        return redirect()->route('admin.levels.index')->with('success', 'Level deleted.');
    }

    protected function validated(Request $request, ?Level $level = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('levels')->ignore($level)],
            'name' => ['required', 'string', 'max:80'],
            'level_number' => ['required', 'integer', 'min:1', 'max:255', Rule::unique('levels')->ignore($level)],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
            'min_q_coins' => ['required', 'integer', 'min:0'],
            'min_gigs_completed' => ['required', 'integer', 'min:0'],
            'min_sales' => ['required', 'integer', 'min:0'],
            'min_referrals' => ['required', 'integer', 'min:0'],
            'min_account_age_days' => ['required', 'integer', 'min:0'],
        ]);

        $data['requires_verified'] = $request->boolean('requires_verified');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

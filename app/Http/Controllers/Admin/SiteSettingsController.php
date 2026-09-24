<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        return view('admin.settings.appearance', [
            'currentBg' => AppSetting::get('auth_background_image'),
            'animatedEnabled' => AppSetting::get('auth_animated_elements', '1') === '1',
        ]);
    }

    public function updateBackground(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate(['image' => 'required|image|max:5120']);

        $path = $request->file('image')->store('branding', 'public');
        AppSetting::set('auth_background_image', Storage::url($path));

        return back()->with('status', 'Background image updated.');
    }

    public function resetBackground()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        AppSetting::set('auth_background_image', null);
        return back()->with('status', 'Reverted to default background.');
    }

    public function updateAnimations(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        AppSetting::set('auth_animated_elements', $request->boolean('enabled') ? '1' : '0');
        return back()->with('status', 'Animation setting updated.');
    }
}

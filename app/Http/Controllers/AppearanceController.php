<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'theme_style'   => 'nullable|in:glass,neumorphic,plain,aurora',
            'navbar_style'  => 'nullable|in:glass,neumorphic,plain,default',
            'topbar_style'  => 'nullable|in:glass,neumorphic,plain,default',
            'notif_style'   => 'nullable|in:glass,neumorphic,plain,default',
            'sidebar_style' => 'nullable|in:glass,neumorphic,plain,default',
            'card_style'    => 'nullable|in:glass,neumorphic,plain,default',
            'mobile_nav_style' => 'nullable|in:painted_pill,edge_dock',
            'accent_color'  => 'nullable|string|max:7',
        ]);

        foreach (['navbar_style','topbar_style','notif_style','sidebar_style','card_style'] as $f) {
            if (($data[$f] ?? null) === 'default') $data[$f] = null;
        }

        $request->user()->update($data);

        return response()->json(['ok' => true]);
    }
}

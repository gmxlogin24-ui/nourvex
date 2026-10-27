<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $live = Announcement::live()->latest()->get();
        $earlier = Announcement::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->latest()
            ->take(20)
            ->get();

        return view('announcements.index', [
            'live' => $live,
            'earlier' => $earlier,
            'openId' => (int) request('a', 0),
        ]);
    }
}

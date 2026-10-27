<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements', ['items' => Announcement::latest()->get()]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'title'      => 'required|string|max:80',
            'body'       => 'nullable|string|max:220',
            'link_url'   => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/)/'],
            'color'      => 'required|in:orange,teal,green,purple,blue,rose',
            'image'      => 'nullable|image|max:4096',
            'expires_at' => 'nullable|date',
        ]);

        $data = [
            'title'      => $v['title'],
            'body'       => $v['body'] ?? null,
            'link_url'   => $v['link_url'] ?? null,
            'color'      => $v['color'],
            'expires_at' => $v['expires_at'] ?? null,
            'is_active'  => true,
            'created_by' => $request->user()->id,
        ];

        if ($request->hasFile('image')) {
            $data['image_url'] = '/storage/' . $request->file('image')->store('announcements', 'public');
        }

        Announcement::create($data);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement posted.');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('status', $announcement->is_active ? 'Announcement is live.' : 'Announcement turned off.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_url && str_starts_with($announcement->image_url, '/storage/')) {
            Storage::disk('public')->delete(substr($announcement->image_url, 9));
        }
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', ['blogs' => Blog::with('author')->latest()->get()]);
    }

    public function show(Blog $blog)
    {
        return view('blog.show', ['blog' => $blog->load('author')]);
    }

    public function create()
    {
        return view('blog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:6144',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = '/storage/' . $request->file('cover_image')->store('blogs', 'public');
        }

        Blog::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'cover_image' => $validated['cover_image'] ?? null,
        ]);

        return redirect()->route('blog.index')->with('status', 'Blog post published!');
    }
}

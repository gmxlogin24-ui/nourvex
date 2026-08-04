<?php

namespace App\Http\Controllers;

class FeaturesController extends Controller
{
    public function index()
    {
        $news = \App\Models\News::latest('published_at')->take(20)->get()->map(fn($n) => [
            'kind' => 'news',
            'title' => $n->title,
            'body' => $n->body,
            'tag' => $n->type === 'ad' ? 'Sponsored' : $n->category,
            'time' => $n->published_at,
            'image_url' => $n->image_url ?? null,
            'video_url' => $n->video_url ?? null,
            'amount' => null,
            'link' => auth()->check() ? route('news.index') : route('register'),
        ]);

        $gigs = \App\Models\Gig::where('status', 'open')->latest()->take(20)->get()->map(fn($g) => [
            'kind' => 'gig',
            'title' => $g->title,
            'body' => $g->description,
            'tag' => 'Open Gig',
            'time' => $g->created_at,
            'image_url' => null,
            'video_url' => null,
            'amount' => $g->amount,
            'link' => auth()->check() ? route('gigs.show', $g) : route('register'),
        ]);

        $feed = $news->concat($gigs)->sortByDesc('time')->values();

        return view('features.index', ['feed' => $feed]);
    }
}

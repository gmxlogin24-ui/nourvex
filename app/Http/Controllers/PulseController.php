<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class PulseController extends Controller
{
    public function index(Request $request)
    {
        $filter = in_array($request->query('filter'), ['all', 'news', 'ads', 'upcoming'], true)
            ? $request->query('filter') : 'all';

        $q = News::query()->withCount(['comments', 'likes'])->withExists(['likes as liked' => fn ($l) => $l->where('user_id', auth()->id() ?? 0)]);

        if ($filter === 'upcoming') {
            $q->where('published_at', '>', now())->orderBy('published_at');
        } else {
            $q->live();
            if ($filter === 'ads') {
                $q->where('type', 'ad');
            } elseif ($filter === 'news') {
                $q->where(fn ($w) => $w->whereNull('type')->orWhere('type', '!=', 'ad'));
            }
            $q->orderByDesc('is_featured')->orderByDesc('published_at');
        }

        return view('pulse', [
            'posts'  => $q->take(30)->get(),
            'filter' => $filter,
        ]);
    }

    public function view(News $news)
    {
        $news->increment('views');
        return response()->noContent();
    }

    public function go(News $news)
    {
        abort_unless($news->source_url && preg_match('#^https?://#i', $news->source_url), 404);
        $news->increment('clicks');
        return redirect()->away($news->source_url);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\News;

class PulseController extends Controller
{
    /**
     * Show the Pulse feed — art/ad posts mixed with campus news.
     *
     * Today this reads straight from the News model (type: 'ad' | 'news'),
     * the same source welcome.blade.php already uses. Each post carries at
     * most one image_url or one video_url right now, so the feed renders a
     * single-media "count-1" tile per post. Once a posts/media table exists
     * (multiple photos per post), pass an array of media per post instead
     * and the CSS grid in pulse.css already handles counts 1-4+.
     */
    public function index()
    {
        $posts = News::latest('published_at')->take(30)->get();

        return view('pulse', ['posts' => $posts]);
    }
}

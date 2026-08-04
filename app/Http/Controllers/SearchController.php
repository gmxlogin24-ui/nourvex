<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\News;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $gigs = Gig::where('title', 'like', "%$q%")->orWhere('category', 'like', "%$q%")
            ->take(4)->get()->map(fn($g) => [
                'type' => 'Gig', 'title' => $g->title, 'meta' => '₦' . number_format($g->amount),
                'url' => route('gigs.show', $g->id),
            ]);

        $listings = Listing::where('title', 'like', "%$q%")
            ->take(4)->get()->map(fn($l) => [
                'type' => 'Marketplace', 'title' => $l->title, 'meta' => '₦' . number_format($l->amount),
                'url' => route('marketplace.show', $l->id),
            ]);

        $news = News::where('title', 'like', "%$q%")->orWhere('category', 'like', "%$q%")
            ->take(4)->get()->map(fn($n) => [
                'type' => 'News', 'title' => $n->title, 'meta' => $n->category,
                'url' => route('news.index'),
            ]);

        return response()->json($gigs->concat($listings)->concat($news)->values());
    }
}

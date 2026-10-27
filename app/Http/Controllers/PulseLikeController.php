<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsLike;
use Illuminate\Http\Request;

class PulseLikeController extends Controller
{
    public function toggle(Request $request, News $news)
    {
        abort_unless(self::canMark($request->user()), 403, 'Only verified members can mark posts.');
        $uid = $request->user()->id;
        $existing = NewsLike::where('news_id', $news->id)->where('user_id', $uid)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            NewsLike::firstOrCreate(['news_id' => $news->id, 'user_id' => $uid]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $news->likes()->count()]);
    }

    public static function canMark($u): bool
    {
        return (bool) ($u && ($u->is_admin || $u->tier === 'tier3' || $u->is_verified_seller));
    }
}

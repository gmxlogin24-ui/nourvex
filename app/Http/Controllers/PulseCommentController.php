<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\NewsCommentLike;
use Illuminate\Http\Request;

class PulseCommentController extends Controller
{
    private function shape(NewsComment $c, $viewer): array
    {
        return [
            'id'         => $c->id,
            'parent_id'  => $c->parent_id,
            'name'       => $c->user->name ?? 'Member',
            'avatar'     => $c->user->avatar_url ?? null,
            'body'       => $c->body,
            'ago'        => $c->created_at->diffForHumans(null, true, true),
            'mine'       => $viewer && $viewer->id === $c->user_id,
            'can_delete' => $viewer && ($viewer->id === $c->user_id || $viewer->is_admin),
            'likes'      => (int) ($c->likes_count ?? 0),
            'liked'      => (bool) ($c->liked ?? false),
            'replies'    => [],
        ];
    }

    public function index(Request $request, News $news)
    {
        $viewer = $request->user();
        $q = $news->comments()->with('user')->withCount('likes');
        if ($viewer) {
            $q->withExists(['likes as liked' => fn ($l) => $l->where('user_id', $viewer->id)]);
        }
        $all = $q->latest('id')->take(400)->get()->reverse()->values();

        $tops = [];
        $kids = [];
        foreach ($all as $c) {
            if ($c->parent_id) {
                $kids[$c->parent_id][] = $this->shape($c, $viewer);
            } else {
                $tops[] = $this->shape($c, $viewer);
            }
        }
        $tops = array_reverse($tops);
        foreach ($tops as &$t) {
            $t['replies'] = $kids[$t['id']] ?? [];
        }
        unset($t);

        return response()->json([
            'count'    => $news->comments()->count(),
            'comments' => $tops,
        ]);
    }

    public function store(Request $request, News $news)
    {
        $data = $request->validate([
            'body'      => 'required|string|max:300',
            'parent_id' => 'nullable|integer',
        ]);
        $body = trim(strip_tags($data['body']));
        abort_if($body === '', 422, 'Say something first.');

        $parentId = null;
        if (!empty($data['parent_id'])) {
            $p = $news->comments()->find($data['parent_id']);
            abort_unless($p, 404);
            $parentId = $p->parent_id ?: $p->id;
        }

        $c = $news->comments()->create([
            'user_id'   => $request->user()->id,
            'parent_id' => $parentId,
            'body'      => $body,
        ]);
        $c->load('user');

        return response()->json([
            'comment' => $this->shape($c, $request->user()),
            'count'   => $news->comments()->count(),
        ], 201);
    }

    public function like(Request $request, News $news, NewsComment $comment)
    {
        abort_unless($comment->news_id === $news->id, 404);
        $uid = $request->user()->id;
        $existing = NewsCommentLike::where('comment_id', $comment->id)->where('user_id', $uid)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            NewsCommentLike::firstOrCreate(['comment_id' => $comment->id, 'user_id' => $uid]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => NewsCommentLike::where('comment_id', $comment->id)->count(),
        ]);
    }

    public function destroy(Request $request, News $news, NewsComment $comment)
    {
        abort_unless($comment->news_id === $news->id, 404);
        abort_unless($request->user()->is_admin || $request->user()->id === $comment->user_id, 403);

        $ids = NewsComment::where('parent_id', $comment->id)->pluck('id')->push($comment->id);
        NewsCommentLike::whereIn('comment_id', $ids)->delete();
        NewsComment::whereIn('id', $ids)->delete();

        return response()->json(['count' => $news->comments()->count()]);
    }
}

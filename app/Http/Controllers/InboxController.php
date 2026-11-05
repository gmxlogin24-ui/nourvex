<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Gig;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    public function profile() { return $this->show('profile'); }
    public function hustles() { return $this->show('hustles'); }
    public function market() { return $this->show('market'); }

    // Number of unread chats in each world, used for the dock badges.
    public static function unreadByWorld(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $out = ['profile' => 0, 'hustles' => 0, 'market' => 0];
        try {
            $me = Auth::id();
            if (!$me) {
                return $cache = $out;
            }
            $convs = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $me))
                ->whereIn('type', ['business', 'direct'])
                ->limit(300)
                ->get();
            foreach ($convs as $c) {
                if ((int) $c->unreadCountFor($me) < 1) {
                    continue;
                }
                if ($c->subject_type === Gig::class) {
                    $out['hustles']++;
                } elseif ($c->subject_type === Listing::class) {
                    $out['market']++;
                } else {
                    $out['profile']++;
                }
            }
        } catch (\Throwable $e) {
        }
        return $cache = $out;
    }

    private function show(string $world)
    {
        $me = Auth::id();
        $want = ['profile' => 'direct', 'hustles' => 'hustle', 'market' => 'market'][$world];

        $rows = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $me))
            ->whereIn('type', ['business', 'direct'])
            ->with(['participants.user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->limit(150)
            ->get()
            ->map(function ($conv) use ($me) {
                $other = $conv->participants->pluck('user')->filter(fn ($u) => $u && $u->id !== $me)->first();
                $kind = 'direct';
                $link = route('chat.show', $conv->id);
                $title = '';
                $thumb = null;
                $mine = false;
                try {
                    if ($conv->subject_type === Gig::class) {
                        $kind = 'hustle';
                        $link = route('gigs.show', $conv->subject_id);
                        $g = $conv->subject;
                        $title = (string) (optional($g)->title ?? '');
                        $mine = optional(optional($g)->poster)->id === $me;
                    } elseif ($conv->subject_type === Listing::class) {
                        $kind = 'market';
                        $link = route('marketplace.show', $conv->subject_id);
                        $l = $conv->subject;
                        $title = (string) (optional($l)->title ?? '');
                        $mine = (int) optional($l)->user_id === (int) $me;
                        $thumb = optional($l)->image_url ?: optional(optional($l)->images)->first()->image_url ?? null;
                    }
                } catch (\Throwable $e) {
                }
                $last = $conv->latestMessage;
                $body = $last ? ($last->body ?: ($last->message_type === 'image' ? 'Photo' : '')) : '';
                if ($last && $last->sender_id === $me && $body !== '') {
                    $body = 'You: ' . $body;
                }
                return [
                    'id' => $conv->id,
                    'kind' => $kind,
                    'link' => $link,
                    'title' => $title,
                    'thumb' => $thumb,
                    'role' => $mine ? 'mine' : 'other',
                    'other_id' => $other->id ?? 0,
                    'name' => ($other->username ?? null) ? '@' . $other->username : ($other->name ?? 'Unknown'),
                    'avatar' => $other->avatar_url ?? null,
                    'body' => $body,
                    'at' => $conv->last_message_at,
                    'unread' => (int) $conv->unreadCountFor($me),
                ];
            })
            ->filter(fn ($r) => $r['kind'] === $want)
            ->values();

        $stories = $rows->unique('other_id')->take(10)->values();

        return view('inbox.world', [
            'world' => $world,
            'rows' => $rows,
            'stories' => $stories,
            'unreadTotal' => $rows->where('unread', '>', 0)->count(),
        ]);
    }
}

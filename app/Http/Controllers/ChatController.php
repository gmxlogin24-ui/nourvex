<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationParticipant;
use App\Models\Gig;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $conversations = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->whereIn('type', ['business', 'direct'])
            ->with(['participants.user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->limit(60)
            ->get()
            ->map(function ($conv) use ($userId) {
                $other = $conv->participants->pluck('user')->filter(fn ($u) => $u && $u->id !== $userId)->first();
                $kind = 'direct';
                $link = route('chat.show', $conv->id);
                if ($conv->subject_type === Gig::class) {
                    $kind = 'hustle';
                    $link = route('gigs.show', $conv->subject_id);
                } elseif ($conv->subject_type === Listing::class) {
                    $kind = 'market';
                    $link = route('marketplace.show', $conv->subject_id);
                }
                $title = '';
                if ($kind !== 'direct') {
                    try {
                        $title = (string) ($conv->subject->title ?? '');
                    } catch (\Throwable $e) {
                        $title = '';
                    }
                }
                $last = $conv->latestMessage;
                $body = $last ? ($last->body ?: ($last->message_type === 'image' ? 'Photo' : '')) : '';
                if ($last && $last->sender_id === $userId && $body !== '') {
                    $body = 'You: ' . $body;
                }
                return [
                    'id' => $conv->id,
                    'kind' => $kind,
                    'subject_title' => $title,
                    'other_name' => ($other->username ?? null) ? '@'.$other->username : ($other->name ?? 'Unknown'),
                    'other_avatar' => $other->avatar_url ?? null,
                    'last_body' => $body,
                    'last_at' => $conv->last_message_at,
                    'unread' => $conv->unreadCountFor($userId),
                    'link' => $link,
                ];
            });

        $scope = $this->contactScope(Auth::user());
        $peopleQuery = User::where('id', '!=', $userId);
        if (!$scope['all']) {
            $peopleQuery->whereIn('id', array_keys($scope['labels']));
        }
        $people = $peopleQuery->orderBy('name')->limit(200)->get(['id', 'name', 'avatar_url'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar_url,
                'tag' => $scope['labels'][$u->id] ?? 'Member',
            ])->values()->all();

        return view('chat.index', [
            'conversations' => $conversations,
            'unreadTotal' => $conversations->where('unread', '>', 0)->count(),
            'people' => $people,
        ]);
    }

    public function start(Request $request)
    {
        if (!app(\App\Services\TierService::class)->unlocked(Auth::user(), 'chat.enabled')) {
            return back()->withErrors(['to' => 'Chat is currently unavailable.']);
        }
        $scope = $this->contactScope(Auth::user());
        $other = null;

        if ($request->filled('user_id')) {
            $other = User::find((int) $request->input('user_id'));
            if (!$other || $other->id === Auth::id()) {
                return back()->withErrors(['to' => 'That person could not be found.']);
            }
        } else {
            $request->validate(['to' => 'required|string|max:60']);
            $needle = mb_strtolower(ltrim(trim($request->input('to')), '@'));
            if ($needle === '') {
                return back()->withErrors(['to' => 'Type who you want to message.'])->withInput();
            }
            $found = User::where('id', '!=', Auth::id())
                ->where(function ($w) use ($needle) {
                    if (Schema::hasColumn('users', 'username')) {
                        $w->whereRaw('LOWER(username) = ?', [$needle]);
                    }
                    $w->orWhereRaw('LOWER(name) = ?', [$needle]);
                })
                ->limit(2)
                ->get();
            if ($found->isEmpty()) {
                return back()->withErrors(['to' => 'No one found with that name.'])->withInput();
            }
            if ($found->count() > 1) {
                return back()->withErrors(['to' => 'More than one person has that name. Use their exact @username.'])->withInput();
            }
            $other = $found->first();
        }

        if (!$scope['all'] && !isset($scope['labels'][$other->id])) {
            return back()->withErrors(['to' => 'Your plan lets you message your contacts and team. Upgrade to message more people.'])->withInput();
        }

        $conv = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($p) => $p->where('user_id', Auth::id()))
            ->whereHas('participants', fn ($p) => $p->where('user_id', $other->id))
            ->first();
        if (!$conv) {
            $conv = Conversation::create(['type' => 'direct']);
            foreach ([Auth::id(), $other->id] as $uid) {
                ConversationParticipant::firstOrCreate(['conversation_id' => $conv->id, 'user_id' => $uid]);
            }
        }

        return redirect()->route('chat.show', $conv->id);
    }

    public function show(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        if ($conversation->subject_type === Gig::class) {
            return redirect()->route('gigs.show', $conversation->subject_id);
        }
        if ($conversation->subject_type === Listing::class) {
            return redirect()->route('marketplace.show', $conversation->subject_id);
        }
        $this->markRead($conversation);
        $other = $conversation->participants()->with('user')->get()->pluck('user')->filter(fn ($u) => $u && $u->id !== Auth::id())->first();

        return view('chat.thread', [
            'conversation' => $conversation,
            'other' => $other,
        ]);
    }

    public function messages(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        $this->markRead($conversation);

        return response()->json($conversation->messages()->get()->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'is_system' => $m->is_system,
            'message_type' => $m->message_type,
            'meta' => $m->meta,
            'created_at' => $m->created_at->diffForHumans(),
        ]));
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);
        if (!app(\App\Services\TierService::class)->unlocked(Auth::user(), 'chat.enabled')) {
            return response()->json(['error' => 'Chat is currently unavailable.'], 403);
        }
        $data = $request->validate(['body' => 'required|string|max:1000']);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message_type' => 'text',
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        $this->markRead($conversation);

        return response()->json([
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'message_type' => $message->message_type,
            'meta' => $message->meta,
            'created_at' => $message->created_at->diffForHumans(),
        ]);
    }

    private function contactScope($me): array
    {
        $tier = $me->tier ?? 'free';
        $labels = [];

        $mine = ConversationParticipant::where('user_id', $me->id)->pluck('conversation_id');
        $dealt = ConversationParticipant::whereIn('conversation_id', $mine)->where('user_id', '!=', $me->id)->pluck('user_id')->unique()->all();
        foreach ($dealt as $id) {
            $labels[$id] = 'Contact';
        }
        if ($me->referred_by) {
            $labels[$me->referred_by] = 'Upline';
        }

        $level1 = [];
        if (in_array($tier, ['tier1', 'tier2', 'tier3'], true)) {
            $level1 = User::where('referred_by', $me->id)->pluck('id')->all();
            foreach ($level1 as $id) {
                $labels[$id] = 'Team';
            }
        }
        if (in_array($tier, ['tier2', 'tier3'], true) && $level1) {
            foreach (User::whereIn('referred_by', $level1)->pluck('id')->all() as $id) {
                $labels[$id] = $labels[$id] ?? 'Team L2';
            }
        }
        unset($labels[$me->id]);

        return ['all' => $tier === 'tier3', 'labels' => $labels];
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless($conversation->participants()->where('user_id', Auth::id())->exists(), 403);
    }

    private function markRead(Conversation $conversation): void
    {
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);
    }
}

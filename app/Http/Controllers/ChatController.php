<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $conversations = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->where('type', 'business')
            ->with(['participants.user', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($userId) {
                $other = $conv->participants->pluck('user')->filter(fn($u) => $u && $u->id !== $userId)->first();
                return [
                    'id' => $conv->id,
                    'subject_type' => class_basename($conv->subject_type),
                    'subject_id' => $conv->subject_id,
                    'other_name' => $other->name ?? 'Unknown',
                    'other_avatar' => $other->avatar_url ?? null,
                    'last_body' => $conv->latestMessage->body ?? '',
                    'last_at' => $conv->last_message_at,
                    'unread' => $conv->unreadCountFor($userId),
                    'link' => $conv->subject_type === \App\Models\Gig::class
                        ? route('gigs.show', $conv->subject_id)
                        : route('marketplace.show', $conv->subject_id),
                ];
            });

        return view('chat.index', ['conversations' => $conversations]);
    }
}

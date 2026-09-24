<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\Concierge\KeywordConciergeService;
use App\Services\TierService;
use Illuminate\Http\Request;
use RuntimeException;

class ConciergeController extends Controller
{
    public function show(TierService $tiers)
    {
        $user = auth()->user();
        abort_unless($tiers->unlocked($user, 'concierge.actions'), 403, 'Upgrade to unlock the QLEVO Concierge.');

        $conversation = Conversation::findOrCreateConcierge($user);
        return view('concierge.index', ['messages' => $conversation->messages()->get()]);
    }

    public function sendMessage(Request $request, KeywordConciergeService $concierge, TierService $tiers)
    {
        $user = auth()->user();
        if (!$tiers->unlocked($user, 'concierge.actions')) {
            return response()->json(['error' => 'Upgrade to unlock the QLEVO Concierge.'], 403);
        }

        $data = $request->validate(['message' => 'required|string|max:2000']);
        $conversation = Conversation::findOrCreateConcierge($user);

        \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message_type' => 'text',
            'body' => $data['message'],
        ]);

        $result = $concierge->handleUserMessage($user, $data['message']);

        \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'is_ai' => true,
            'message_type' => 'text',
            'body' => $result['reply'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        return response()->json($result);
    }
}

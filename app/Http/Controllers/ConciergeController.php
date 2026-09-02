<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\Concierge\ConciergeService;
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

    public function sendMessage(Request $request, ConciergeService $concierge, TierService $tiers)
    {
        $user = auth()->user();
        abort_unless($tiers->unlocked($user, 'concierge.actions'), 403, 'Upgrade to unlock the QLEVO Concierge.');

        $data = $request->validate(['message' => 'required|string|max:2000']);
        $conversation = Conversation::findOrCreateConcierge($user);

        try {
            $result = $concierge->handleUserMessage($user, $conversation, $data['message']);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }

        return response()->json($result);
    }
}

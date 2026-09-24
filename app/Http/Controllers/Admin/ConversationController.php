<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
    }

    public function index(Request $r)
    {
        $this->guard();
        $q = Conversation::with('participants.user')->latest('last_message_at');
        if ($r->filled('type')) $q->where('type', $r->type);
        return view('admin.conversations.index', ['conversations' => $q->paginate(25)->withQueryString()]);
    }

    public function show(Conversation $conversation)
    {
        $this->guard();
        $messages = $conversation->messages()->with('sender')->get();
        return view('admin.conversations.show', ['conversation' => $conversation, 'messages' => $messages]);
    }

    public function destroyMessage(Conversation $conversation, ConversationMessage $message)
    {
        $this->guard();
        abort_unless($message->conversation_id === $conversation->id, 404);
        $message->delete();
        return back()->with('status', 'Message deleted.');
    }
}

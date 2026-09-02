<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'subject_type', 'subject_id', 'title', 'last_message_at'])]
class Conversation extends Model
{
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function findOrCreateConcierge(User $user): self
    {
        $participant = ConversationParticipant::where('user_id', $user->id)
            ->whereHas('conversation', fn ($q) => $q->where('type', 'concierge'))
            ->first();

        if ($participant) {
            return $participant->conversation;
        }

        $conversation = self::create(['type' => 'concierge', 'title' => 'LEVI']);
        $conversation->participants()->create(['user_id' => $user->id]);

        return $conversation;
    }
}

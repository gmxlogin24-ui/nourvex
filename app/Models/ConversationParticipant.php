<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['conversation_id', 'user_id', 'is_ai', 'last_read_at', 'muted_at'])]
class ConversationParticipant extends Model
{
    protected $casts = ['last_read_at' => 'datetime', 'muted_at' => 'datetime', 'is_ai' => 'boolean'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

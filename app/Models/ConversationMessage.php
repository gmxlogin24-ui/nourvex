<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['conversation_id', 'sender_id', 'is_ai', 'is_system', 'message_type', 'body', 'meta'])]
class ConversationMessage extends Model
{
    protected $casts = ['meta' => 'array', 'is_ai' => 'boolean', 'is_system' => 'boolean'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

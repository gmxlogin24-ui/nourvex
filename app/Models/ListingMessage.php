<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingMessage extends Model
{
    protected $fillable = ['listing_id', 'sender_id', 'recipient_id', 'body', 'read_at', 'is_system', 'message_type', 'meta'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'meta' => 'array'];
    }

    // Posts an automated event line into the buyer/seller thread. Attributed
    // to whoever triggered the action (there's no real "system" user, and
    // sender_id/recipient_id are required, non-nullable columns), so this
    // reuses the existing thread-lookup/polling logic with zero extra work.
    public static function postSystemEvent(int $listingId, int $actorId, int $counterpartyId, string $text): void
    {
        static::create([
            'listing_id' => $listingId,
            'sender_id' => $actorId,
            'recipient_id' => $counterpartyId,
            'body' => $text,
            'is_system' => true,
        ]);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}

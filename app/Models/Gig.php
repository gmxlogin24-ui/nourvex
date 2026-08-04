<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $fillable = [
        'title', 'category', 'description', 'amount',
        'user_id', 'accepted_by', 'status', 'whatsapp', 'phone',
        'guest_name', 'guest_token', 'delivery_note', 'delivered_at', 'rejection_note',
    ];

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}

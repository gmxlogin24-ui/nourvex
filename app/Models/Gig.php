<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'description', 'amount', 'escrowed_amount',
        'user_id', 'accepted_by', 'status', 'whatsapp', 'phone',
        'guest_name', 'guest_token', 'delivery_note', 'delivered_at',
        'rejection_note', 'payment_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'payment_reminder_sent_at' => 'datetime',
        ];
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function dispute()
    {
        return $this->hasOne(GigDispute::class);
    }
}

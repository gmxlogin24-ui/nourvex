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
        'rejection_note', 'payment_reminder_sent_at', 'revision_count',
        'deadline', 'location_type', 'location', 'details',
        'attachment_path', 'delivery_attachment_path',
        'has_item', 'item_name', 'item_qty', 'item_cost',
        'delivery_address', 'item_delivery_method',
        'packages', 'chosen_package',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'payment_reminder_sent_at' => 'datetime',
            'deadline' => 'date',
            'details' => 'array',
            'has_item' => 'boolean',
            'item_cost' => 'decimal:2',
            'packages' => 'array',
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

    // Resolves the WhatsApp number to show for this gig (gig-specific
    // number takes priority over the poster's profile number), and
    // normalizes it to bare digits with the 234 country code - handles
    // numbers stored as 0803..., 234803..., or +234803... consistently.
    // Returns null if there's no number to show at all.
    public function whatsappLink(): ?string
    {
        $raw = $this->whatsapp ?: ($this->poster->whatsapp ?? null);
        if (!$raw) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);
        if (str_starts_with($digits, '234')) {
            // already has country code
        } elseif (str_starts_with($digits, '0')) {
            $digits = '234' . substr($digits, 1);
        } else {
            $digits = '234' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }
}

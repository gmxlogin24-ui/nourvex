<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'title', 'seller_name', 'description', 'amount',
        'category', 'location', 'condition', 'image_url', 'is_verified_seller',
        'user_id', 'listing_type', 'store_name', 'whatsapp', 'phone',
        'status', 'buyer_id',
    ];

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}

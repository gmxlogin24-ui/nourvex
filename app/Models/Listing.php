<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'seller_name', 'description', 'amount', 'stock_quantity',
        'category', 'location', 'condition', 'image_url', 'is_verified_seller',
        'user_id', 'listing_type', 'store_name', 'whatsapp', 'phone',
        'status', 'buyer_id', 'delivery_method', 'shipping_note', 'shipped_at', 'brand', 'highlights', 'whats_in_box', 'warranty', 'return_policy', 'negotiable',
        'old_price', 'colors', 'sizes', 'specs',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
        ];
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'listing_favorites')->withTimestamps();
    }

    public function dispute()
    {
        return $this->hasOne(ListingDispute::class);
    }
}

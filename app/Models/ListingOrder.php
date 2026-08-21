<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingOrder extends Model
{
    protected $fillable = ['listing_id', 'buyer_id', 'amount', 'status'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}

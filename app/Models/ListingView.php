<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ListingView extends Model
{
    protected $fillable = ['listing_id', 'user_id', 'viewed_on'];

    protected $casts = ['viewed_on' => 'date'];

    // One row per viewer per listing per day, so refreshing never inflates the count.
    public static function record($listing): void
    {
        try {
            $uid = Auth::id();
            if (! $uid || (int) $listing->user_id === (int) $uid) {
                return;
            }
            $row = static::firstOrCreate([
                'listing_id' => $listing->id,
                'user_id'    => $uid,
                'viewed_on'  => now()->toDateString(),
            ]);
            if (! $row->wasRecentlyCreated) {
                $row->touch();
            }
        } catch (\Throwable $e) {
            // never break the listing page because of tracking
        }
    }
}

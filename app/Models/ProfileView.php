<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProfileView extends Model
{
    protected $fillable = ['profile_user_id', 'viewer_id', 'viewed_on'];

    // Call this from any page where someone opens another user's profile.
    // One row per viewer per profile per day, so refreshing never inflates the count.
    public static function record($profileUserId): void
    {
        try {
            $uid = Auth::id();
            if (! $uid || (int) $uid === (int) $profileUserId) { return; }
            static::firstOrCreate([
                'profile_user_id' => $profileUserId,
                'viewer_id' => $uid,
                'viewed_on' => now()->toDateString(),
            ]);
        } catch (\Throwable $e) {
            // never break a page because of tracking
        }
    }
}

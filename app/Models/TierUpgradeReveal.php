<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'from_tier', 'to_tier', 'unlocked_features', 'shown_at'])]
class TierUpgradeReveal extends Model
{
    protected $casts = [
        'unlocked_features' => 'array',
        'shown_at' => 'datetime',
    ];

    public function markShown(): void
    {
        $this->update(['shown_at' => now()]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRevenue extends Model
{
    protected $table = 'platform_revenue';

    protected $fillable = ['source', 'amount', 'description', 'related_type', 'related_id'];

    // Single entry point for recording platform earnings, so every payout
    // path (marketplace commission, gig commission, VTU margin) credits
    // revenue the same way instead of each duplicating this logic.
    public static function record(string $source, float $amount, string $description, ?string $relatedType = null, ?int $relatedId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        static::create([
            'source' => $source,
            'amount' => $amount,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }
}

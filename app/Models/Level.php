<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'name', 'rank', 'min_q_coins', 'min_gigs_completed',
        'min_referrals', 'requires_verified', 'benefits', 'is_active',
    ];

    protected $casts = [
        'rank' => 'integer',
        'min_q_coins' => 'integer',
        'min_gigs_completed' => 'integer',
        'min_referrals' => 'integer',
        'requires_verified' => 'boolean',
        'is_active' => 'boolean',
        'benefits' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'level_id');
    }

    /** All numeric gates as [metric_key => required_value]. */
    public function gates(): array
    {
        return array_filter([
            'q_coins_earned' => $this->min_q_coins,
            'gigs_completed' => $this->min_gigs_completed,
            'referrals' => $this->min_referrals,
        ]);
    }

    public function requirementsMet(array $metrics): bool
    {
        if ($this->requires_verified && empty($metrics['verified'])) {
            return false;
        }

        foreach ($this->gates() as $metric => $required) {
            if (($metrics[$metric] ?? 0) < $required) {
                return false;
            }
        }

        return true;
    }
}

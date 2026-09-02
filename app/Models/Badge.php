<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    public const CRITERIA = [
        'gigs_completed' => 'Gigs Completed',
        'sales' => 'Marketplace Sales',
        'referrals' => 'Referrals',
        'q_coins' => 'Q Coins Earned',
        'streak' => 'Streak Days',
        'level' => 'Level Reached',
        'verified' => 'Verified Seller',
        'profile_complete' => 'Profile Complete (%)',
        'early_member' => 'Early Member (first N users)',
    ];

    protected $fillable = [
        'key', 'name', 'description', 'icon',
        'criteria_type', 'criteria_value', 'is_active',
    ];

    protected $casts = [
        'criteria_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot('earned_at');
    }

    public function criteriaLabel(): string
    {
        return self::CRITERIA[$this->criteria_type] ?? ucfirst(str_replace('_', ' ', $this->criteria_type));
    }
}

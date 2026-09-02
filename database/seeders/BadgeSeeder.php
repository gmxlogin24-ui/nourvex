<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::truncate();

        $badges = [
            [
                'key' => 'first_gig',
                'name' => 'First Gig',
                'description' => 'Completed your first gig',
                'icon' => '🎯',
                'criteria_type' => 'gigs_completed',
                'criteria_value' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'gig_master',
                'name' => 'Gig Master',
                'description' => 'Completed 20 gigs',
                'icon' => '🏆',
                'criteria_type' => 'gigs_completed',
                'criteria_value' => 20,
                'is_active' => true,
            ],
            [
                'key' => 'top_seller',
                'name' => 'Top Seller',
                'description' => 'Completed 50 gigs',
                'icon' => '👑',
                'criteria_type' => 'gigs_completed',
                'criteria_value' => 50,
                'is_active' => true,
            ],
            [
                'key' => 'referral_pro',
                'name' => 'Referral Pro',
                'description' => 'Referred 5 active users',
                'icon' => '🤝',
                'criteria_type' => 'referrals',
                'criteria_value' => 5,
                'is_active' => true,
            ],
            [
                'key' => 'referral_champion',
                'name' => 'Referral Champion',
                'description' => 'Referred 20 active users',
                'icon' => '🌟',
                'criteria_type' => 'referrals',
                'criteria_value' => 20,
                'is_active' => true,
            ],
            [
                'key' => 'q_coin_collector',
                'name' => 'Q Coin Collector',
                'description' => 'Earned 1000 Q Coins',
                'icon' => '💎',
                'criteria_type' => 'q_coins',
                'criteria_value' => 1000,
                'is_active' => true,
            ],
            [
                'key' => 'q_coin_master',
                'name' => 'Q Coin Master',
                'description' => 'Earned 5000 Q Coins',
                'icon' => '💰',
                'criteria_type' => 'q_coins',
                'criteria_value' => 5000,
                'is_active' => true,
            ],
            [
                'key' => 'streak_warrior',
                'name' => 'Streak Warrior',
                'description' => 'Maintained 7 day claim streak',
                'icon' => '⚡',
                'criteria_type' => 'streak',
                'criteria_value' => 7,
                'is_active' => true,
            ],
            [
                'key' => 'daily_champion',
                'name' => 'Daily Champion',
                'description' => 'Maintained 30 day claim streak',
                'icon' => '🔥',
                'criteria_type' => 'streak',
                'criteria_value' => 30,
                'is_active' => true,
            ],
            [
                'key' => 'verified_pro',
                'name' => 'Verified Pro',
                'description' => 'Verified seller status',
                'icon' => '✅',
                'criteria_type' => 'verified',
                'criteria_value' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'profile_complete',
                'name' => 'Complete Profile',
                'description' => 'Completed your full profile',
                'icon' => '🎨',
                'criteria_type' => 'profile_complete',
                'criteria_value' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'early_member',
                'name' => 'Early Member',
                'description' => 'Joined in the early days',
                'icon' => '🚀',
                'criteria_type' => 'early_member',
                'criteria_value' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }

        echo "✅ Badges seeded!";
    }
}

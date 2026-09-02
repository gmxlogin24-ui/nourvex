<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['key' => 'starter', 'name' => 'Starter', 'level_number' => 1, 'icon' => 'sprout', 'color' => 'slate',
             'description' => 'Welcome to QLEVO. Your journey begins here.',
             'min_q_coins' => 0, 'min_gigs_completed' => 0, 'min_sales' => 0, 'min_referrals' => 0, 'min_account_age_days' => 0,
             'perks' => ['Daily Q Coin claim', 'Basic profile']],
            ['key' => 'active', 'name' => 'Active', 'level_number' => 2, 'icon' => 'activity', 'color' => 'blue',
             'description' => 'You are showing up and using the platform.',
             'min_q_coins' => 500, 'min_gigs_completed' => 1, 'min_sales' => 0, 'min_referrals' => 0, 'min_account_age_days' => 3,
             'perks' => ['Higher daily task cap', 'Profile badge display']],
            ['key' => 'contributor', 'name' => 'Contributor', 'level_number' => 3, 'icon' => 'users', 'color' => 'emerald',
             'description' => 'You are building real activity across QLEVO.',
             'min_q_coins' => 2500, 'min_gigs_completed' => 5, 'min_sales' => 2, 'min_referrals' => 1, 'min_account_age_days' => 14,
             'perks' => ['Priority in search', 'Extra task slots']],
            ['key' => 'pro', 'name' => 'Pro', 'level_number' => 4, 'icon' => 'briefcase', 'color' => 'violet',
             'description' => 'A trusted, high-output QLEVO professional.',
             'min_q_coins' => 10000, 'min_gigs_completed' => 20, 'min_sales' => 10, 'min_referrals' => 5, 'min_account_age_days' => 30,
             'requires_verified' => true, 'perks' => ['Lower fees', 'Pro badge', 'Featured listings']],
            ['key' => 'elite', 'name' => 'Elite', 'level_number' => 5, 'icon' => 'crown', 'color' => 'amber',
             'description' => 'Top tier. Unlocks USDT reward eligibility.',
             'min_q_coins' => 50000, 'min_gigs_completed' => 60, 'min_sales' => 30, 'min_referrals' => 15, 'min_account_age_days' => 90,
             'requires_verified' => true, 'perks' => ['USDT rewards', 'Lowest fees', 'Early feature access']],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['key' => $level['key']], $level);
        }

        $badges = [
            ['key' => 'first_gig', 'name' => 'First Gig', 'icon' => 'rocket', 'color' => 'blue', 'criteria_type' => 'gigs_completed', 'criteria_value' => 1, 'q_coin_reward' => 100, 'sort_order' => 1, 'description' => 'Completed your very first gig on QLEVO.'],
            ['key' => 'trusted_worker', 'name' => 'Trusted Worker', 'icon' => 'shield-check', 'color' => 'emerald', 'criteria_type' => 'gigs_completed', 'criteria_value' => 10, 'q_coin_reward' => 300, 'sort_order' => 2, 'description' => 'Delivered 10 completed gigs.'],
            ['key' => 'top_seller', 'name' => 'Top Seller', 'icon' => 'trending-up', 'color' => 'orange', 'criteria_type' => 'sales', 'criteria_value' => 25, 'q_coin_reward' => 500, 'sort_order' => 3, 'description' => 'Closed 25 marketplace sales.'],
            ['key' => 'marketplace_pro', 'name' => 'Marketplace Pro', 'icon' => 'store', 'color' => 'violet', 'criteria_type' => 'sales', 'criteria_value' => 50, 'q_coin_reward' => 800, 'sort_order' => 4, 'description' => 'A serious marketplace operator: 50 sales.'],
            ['key' => 'streak_7', 'name' => 'Week Warrior', 'icon' => 'flame', 'color' => 'red', 'criteria_type' => 'streak', 'criteria_value' => 7, 'q_coin_reward' => 150, 'sort_order' => 5, 'description' => 'Claimed your daily reward 7 days in a row.'],
            ['key' => 'streak_30', 'name' => 'Month of Fire', 'icon' => 'flame-kindling', 'color' => 'red', 'criteria_type' => 'streak', 'criteria_value' => 30, 'q_coin_reward' => 600, 'sort_order' => 6, 'description' => '30 day daily claim streak.'],
            ['key' => 'streak_100', 'name' => 'Unbreakable', 'icon' => 'infinity', 'color' => 'rose', 'criteria_type' => 'streak', 'criteria_value' => 100, 'q_coin_reward' => 2000, 'sort_order' => 7, 'description' => '100 consecutive days. Legendary.'],
            ['key' => 'referral_champion', 'name' => 'Referral Champion', 'icon' => 'user-plus', 'color' => 'cyan', 'criteria_type' => 'referrals', 'criteria_value' => 10, 'q_coin_reward' => 750, 'sort_order' => 8, 'description' => 'Brought 10 people into the QLEVO ecosystem.'],
            ['key' => 'community_builder', 'name' => 'Community Builder', 'icon' => 'heart-handshake', 'color' => 'pink', 'criteria_type' => 'referrals', 'criteria_value' => 25, 'q_coin_reward' => 1500, 'sort_order' => 9, 'description' => 'Grew the community with 25 referrals.'],
            ['key' => 'q_coin_collector', 'name' => 'Q Coin Collector', 'icon' => 'coins', 'color' => 'amber', 'criteria_type' => 'q_coins', 'criteria_value' => 10000, 'q_coin_reward' => 500, 'sort_order' => 10, 'description' => 'Earned 10,000 Q Coins in total.'],
            ['key' => 'profile_complete', 'name' => 'All Set Up', 'icon' => 'badge-check', 'color' => 'teal', 'criteria_type' => 'profile_complete', 'criteria_value' => 100, 'q_coin_reward' => 100, 'sort_order' => 11, 'description' => 'Filled out your entire QLEVO profile.'],
            ['key' => 'early_member', 'name' => 'Early Member', 'icon' => 'sunrise', 'color' => 'indigo', 'criteria_type' => 'early_member', 'criteria_value' => 1000, 'q_coin_reward' => 250, 'sort_order' => 12, 'description' => 'One of the first 1,000 members of QLEVO.'],
            ['key' => 'elite_user', 'name' => 'Elite User', 'icon' => 'crown', 'color' => 'yellow', 'criteria_type' => 'level', 'criteria_value' => 5, 'q_coin_reward' => 5000, 'sort_order' => 13, 'description' => 'Reached Level 5: Elite.'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['key' => $badge['key']], $badge + ['is_active' => true]);
        }
    }
}

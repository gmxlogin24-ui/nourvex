<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;
use App\Models\Badge;

class LevelBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Starter', 'rank' => 1, 'min_q_coins' => 0, 'min_gigs_completed' => 0, 'min_referrals' => 0, 'requires_verified' => false],
            ['name' => 'Active', 'rank' => 2, 'min_q_coins' => 200, 'min_gigs_completed' => 1, 'min_referrals' => 0, 'requires_verified' => false],
            ['name' => 'Contributor', 'rank' => 3, 'min_q_coins' => 800, 'min_gigs_completed' => 3, 'min_referrals' => 1, 'requires_verified' => false],
            ['name' => 'Pro', 'rank' => 4, 'min_q_coins' => 2500, 'min_gigs_completed' => 10, 'min_referrals' => 3, 'requires_verified' => true],
            ['name' => 'Elite', 'rank' => 5, 'min_q_coins' => 6000, 'min_gigs_completed' => 25, 'min_referrals' => 10, 'requires_verified' => true],
        ];

        foreach ($levels as $lvl) {
            Level::updateOrCreate(['rank' => $lvl['rank']], $lvl);
        }

        $badges = [
            ['key' => 'first_gig', 'name' => 'First Gig', 'description' => 'Completed your first gig', 'criteria_type' => 'gigs_completed', 'criteria_value' => 1],
            ['key' => 'gig_veteran', 'name' => 'Gig Veteran', 'description' => 'Completed 10 gigs', 'criteria_type' => 'gigs_completed', 'criteria_value' => 10],
            ['key' => 'referral_starter', 'name' => 'Referral Starter', 'description' => 'Referred your first user', 'criteria_type' => 'referrals', 'criteria_value' => 1],
            ['key' => 'referral_champion', 'name' => 'Referral Champion', 'description' => 'Referred 10 users', 'criteria_type' => 'referrals', 'criteria_value' => 10],
            ['key' => 'q_coin_collector', 'name' => 'Q Coin Collector', 'description' => 'Earned 1000 Q Coins total', 'criteria_type' => 'q_coins', 'criteria_value' => 1000],
            ['key' => 'daily_streak_7', 'name' => 'Daily Streak', 'description' => '7-day claim streak', 'criteria_type' => 'streak', 'criteria_value' => 7],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['key' => $b['key']], $b);
        }
    }
}

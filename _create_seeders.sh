echo "Creating LevelSeeder..."

mkdir -p database/seeders

cat > database/seeders/LevelSeeder.php << 'LEVELSEEDER'
<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        Level::truncate();

        $levels = [
            [
                'name' => 'Starter',
                'rank' => 1,
                'min_q_coins' => 0,
                'min_gigs_completed' => 0,
                'min_referrals' => 0,
                'requires_verified' => false,
                'benefits' => ['Access to basic features', 'Able to post gigs', 'Earn Q Coins'],
                'is_active' => true,
            ],
            [
                'name' => 'Active',
                'rank' => 2,
                'min_q_coins' => 100,
                'min_gigs_completed' => 1,
                'min_referrals' => 0,
                'requires_verified' => false,
                'benefits' => ['Increased visibility', 'Featured listings', '10% bonus on earnings', 'Priority support'],
                'is_active' => true,
            ],
            [
                'name' => 'Contributor',
                'rank' => 3,
                'min_q_coins' => 500,
                'min_gigs_completed' => 5,
                'min_referrals' => 1,
                'requires_verified' => false,
                'benefits' => ['Higher earning potential', 'Bulk discount tools', '15% bonus on earnings', 'Community badge'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'rank' => 4,
                'min_q_coins' => 1500,
                'min_gigs_completed' => 15,
                'min_referrals' => 3,
                'requires_verified' => false,
                'benefits' => ['Premium seller status', '25% bonus on earnings', 'Custom branding', 'Dedicated account manager'],
                'is_active' => true,
            ],
            [
                'name' => 'Elite',
                'rank' => 5,
                'min_q_coins' => 5000,
                'min_gigs_completed' => 50,
                'min_referrals' => 10,
                'requires_verified' => true,
                'benefits' => ['VIP treatment', '50% bonus on earnings', 'White label options', 'Direct platform support', 'Exclusive opportunities'],
                'is_active' => true,
            ],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }

        echo "✅ Levels seeded!";
    }
}
LEVELSEEDER

echo "✅ LevelSeeder created"

echo ""
echo "Creating BadgeSeeder..."

cat > database/seeders/BadgeSeeder.php << 'BADGESEEDER'
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
BADGESEEDER

echo "✅ BadgeSeeder created"


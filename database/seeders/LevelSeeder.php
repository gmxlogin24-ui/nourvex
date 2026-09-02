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

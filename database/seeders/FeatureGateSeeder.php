<?php

namespace Database\Seeders;

use App\Models\FeatureGate;
use Illuminate\Database\Seeder;

class FeatureGateSeeder extends Seeder
{
    public function run(): void
    {
        $gates = [
            ['feature_key' => 'gigs.post', 'label' => 'Post Gigs', 'required_tier' => 'tier1'],
            ['feature_key' => 'marketplace.post', 'label' => 'Post Marketplace Listings', 'required_tier' => 'tier1'],
            ['feature_key' => 'gigs.unlimited_posts', 'label' => 'Unlimited Active Posts', 'required_tier' => 'tier2'],
            ['feature_key' => 'qcoin.streak_bonus', 'label' => 'Q Coin Streak Bonuses', 'required_tier' => 'tier2'],
            ['feature_key' => 'qcoin.usdt_eligibility', 'label' => 'USDT Reward Eligibility', 'required_tier' => 'tier2'],
            ['feature_key' => 'marketplace.boost', 'label' => 'Listing Boosts', 'required_tier' => 'tier2'],
            ['feature_key' => 'concierge.actions', 'label' => 'AI Concierge (perform actions)', 'required_tier' => 'tier1'],
            ['feature_key' => 'concierge.voice', 'label' => 'AI Concierge Voice Mode', 'required_tier' => 'tier2'],
        ];

        foreach ($gates as $g) {
            FeatureGate::updateOrCreate(['feature_key' => $g['feature_key']], $g);
        }
    }
}

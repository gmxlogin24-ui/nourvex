<?php

namespace App\Services;

use App\Models\FeatureGate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TierService
{
    // Ranked order - defines what "tier2 and above" means.
    protected const RANK = ['free' => 0, 'tier1' => 1, 'tier2' => 2, 'tier3' => 3];

    public function rank(string $tier): int
    {
        return self::RANK[$tier] ?? 0;
    }

    public function meetsMinimum(User $user, string $requiredTier): bool
    {
        return $this->rank($user->tier ?? 'free') >= $this->rank($requiredTier);
    }

    /**
     * Check whether a user can use a given feature key. Falls back to
     * "locked" (fail closed) if the feature isn't registered yet.
     */
    public function unlocked(User $user, string $featureKey): bool
    {
        $gate = Cache::remember("feature_gate:{$featureKey}", 60, function () use ($featureKey) {
            return FeatureGate::where('feature_key', $featureKey)->first();
        });

        if (!$gate || !$gate->enabled) {
            return false;
        }

        return $this->meetsMinimum($user, $gate->required_tier);
    }

    /**
     * All feature keys unlocked at a given tier - used to build the
     * "here's what just unlocked" reveal on upgrade.
     */
    public function featuresUnlockedAtTier(string $tier): \Illuminate\Support\Collection
    {
        return FeatureGate::where('enabled', true)
            ->get()
            ->filter(fn ($gate) => $this->rank($tier) >= $this->rank($gate->required_tier))
            ->values();
    }

    /**
     * Features that become newly available when moving from $fromTier to $toTier.
     * This is what the upgrade animation will render.
     */
    public function newlyUnlocked(string $fromTier, string $toTier): \Illuminate\Support\Collection
    {
        $before = $this->featuresUnlockedAtTier($fromTier)->pluck('feature_key');
        return $this->featuresUnlockedAtTier($toTier)->reject(fn ($g) => $before->contains($g->feature_key))->values();
    }
}

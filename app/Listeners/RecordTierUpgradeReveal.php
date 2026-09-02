<?php

namespace App\Listeners;

use App\Events\TierUpgraded;
use App\Models\TierUpgradeReveal;
use App\Services\TierService;

class RecordTierUpgradeReveal
{
    public function __construct(protected TierService $tiers) {}

    public function handle(TierUpgraded $event): void
    {
        // Only worth a reveal if this is an upgrade, not a downgrade/admin correction.
        if ($this->tiers->rank($event->toTier) <= $this->tiers->rank($event->fromTier)) {
            return;
        }

        $newFeatures = $this->tiers->newlyUnlocked($event->fromTier, $event->toTier);

        if ($newFeatures->isEmpty()) {
            return;
        }

        TierUpgradeReveal::create([
            'user_id' => $event->user->id,
            'from_tier' => $event->fromTier,
            'to_tier' => $event->toTier,
            'unlocked_features' => $newFeatures->map(fn ($f) => [
                'key' => $f->feature_key,
                'label' => $f->label,
            ])->all(),
        ]);
    }
}

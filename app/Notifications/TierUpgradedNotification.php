<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TierUpgradedNotification extends Notification
{
    use Queueable;

    public function __construct(public string $fromTier, public string $toTier, public Collection $newFeatures)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'from_tier' => $this->fromTier,
            'to_tier' => $this->toTier,
            'message' => 'You have been upgraded to ' . strtoupper($this->toTier) . '!',
        ];
    }
}

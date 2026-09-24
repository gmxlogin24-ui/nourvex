<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpired extends Notification
{
    use Queueable;

    public function __construct(public string $previousTier)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'previous_tier' => $this->previousTier,
            'message' => 'Your ' . strtoupper($this->previousTier) . ' subscription has expired and your account has been moved back to the free tier. Renew to keep your features.',
        ];
    }
}

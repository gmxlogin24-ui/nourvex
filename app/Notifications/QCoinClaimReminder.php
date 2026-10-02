<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class QCoinClaimReminder extends Notification
{
    use Queueable;

    public function __construct(public int $streakDays)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->streakDays > 0
                ? "Don't lose your {$this->streakDays}-day streak! Claim your Q Coin bonus."
                : 'Your daily Q Coin bonus is waiting to be claimed.',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $body = $this->streakDays > 0
            ? "You're on a {$this->streakDays}-day streak — claim now before you lose it!"
            : 'Your daily bonus is ready to claim.';

        return (new WebPushMessage)
            ->title($this->streakDays > 0 ? '🔥 Streak at risk!' : '💰 Q Coin waiting')
            ->icon('/favicon.ico')
            ->body($body)
            ->data(['url' => route('qcoin.index')]);
    }
}

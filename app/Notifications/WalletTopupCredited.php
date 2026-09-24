<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WalletTopupCredited extends Notification
{
    use Queueable;

    public function __construct(public float $amount)
    {
    }

    public function via($notifiable): array
    {
        return ['database']; // WebPushChannel disabled until VAPID keys are configured — add back: ['database', WebPushChannel::class]
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your wallet was credited with ₦' . number_format($this->amount) . '.',
            'amount' => $this->amount,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Wallet funded')
            ->icon('/favicon.ico')
            ->body('₦' . number_format($this->amount) . ' has been added to your wallet.')
            ->data(['url' => route('wallet.index')]);
    }
}

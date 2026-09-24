<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class MoneyReceived extends Notification
{
    use Queueable;

    public function __construct(public User $sender, public float $amount, public ?string $note = null)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'You received ₦' . number_format($this->amount) . ' from ' . $this->sender->name . '.',
            'sender_id' => $this->sender->id,
            'amount' => $this->amount,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Money received')
            ->icon('/favicon.ico')
            ->body('₦' . number_format($this->amount) . ' from ' . $this->sender->name)
            ->data(['url' => route('wallet.index')]);
    }
}

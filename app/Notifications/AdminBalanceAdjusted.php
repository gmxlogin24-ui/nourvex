<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AdminBalanceAdjusted extends Notification
{
    use Queueable;

    public function __construct(public float $amount, public ?string $note = null)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', WebPushChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $verb = $this->amount >= 0 ? 'credited' : 'debited';
        return (new MailMessage)
            ->subject('Your wallet balance was adjusted')
            ->line('An admin has ' . $verb . ' your wallet by ₦' . number_format(abs($this->amount)) . '.')
            ->line($this->note ? 'Note: ' . $this->note : 'No note was provided.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Admin adjusted your balance by ₦' . number_format($this->amount) . '.',
            'amount' => $this->amount,
            'note' => $this->note,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Balance adjusted')
            ->icon('/favicon.ico')
            ->body('₦' . number_format($this->amount) . ' — ' . ($this->note ?? 'Admin adjustment'))
            ->data(['url' => route('wallet.index')]);
    }
}

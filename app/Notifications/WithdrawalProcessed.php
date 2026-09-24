<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WithdrawalProcessed extends Notification
{
    use Queueable;

    public function __construct(public float $amount, public float $fee = 0)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Withdrawal processed')
            ->line('Your withdrawal of ₦' . number_format($this->amount) . ' has been processed.');

        if ($this->fee > 0) {
            $mail->line('A fee of ₦' . number_format($this->fee, 2) . ' was applied.');
        }

        return $mail->line('If you did not request this, contact support immediately.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Withdrawal of ₦' . number_format($this->amount) . ' processed.',
            'amount' => $this->amount,
            'fee' => $this->fee,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Withdrawal processed')
            ->icon('/favicon.ico')
            ->body('₦' . number_format($this->amount) . ' was withdrawn from your wallet.')
            ->data(['url' => route('wallet.index')]);
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AccountBanned extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail', 'database', WebPushChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been suspended')
            ->line('Your account has been banned by an administrator.')
            ->line('If you believe this is a mistake, please contact support.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your account has been banned.',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Account suspended')
            ->icon('/favicon.ico')
            ->body('Your account has been banned. Contact support if this is a mistake.');
    }
}

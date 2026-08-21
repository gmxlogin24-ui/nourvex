<?php

namespace App\Notifications;

use App\Models\GigDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GigDisputeFiled extends Notification
{
    use Queueable;

    public function __construct(public GigDispute $dispute)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $gig = $this->dispute->gig;
        return (new MailMessage)
            ->subject('New gig dispute: "' . $gig->title . '"')
            ->line('A dispute was filed on the gig "' . $gig->title . '".')
            ->action('Review Dispute', route('admin.disputes.index'))
            ->line('Please review and resolve as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'gig_id' => $this->dispute->gig_id,
            'title' => $this->dispute->gig->title,
            'message' => 'A dispute was filed on "' . $this->dispute->gig->title . '".',
        ];
    }
}

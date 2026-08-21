<?php

namespace App\Notifications;

use App\Models\Gig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GigCancelledByPoster extends Notification
{
    use Queueable;

    public function __construct(public Gig $gig)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Gig cancelled: "' . $this->gig->title . '"')
            ->line('The gig "' . $this->gig->title . '" was cancelled by the poster before any work was marked delivered.')
            ->line('No payment was released for this gig, and none is owed.')
            ->action('Browse Other Gigs', route('gigs.index'));
    }

    public function toArray($notifiable): array
    {
        return [
            'gig_id' => $this->gig->id,
            'title' => $this->gig->title,
            'message' => 'The gig "' . $this->gig->title . '" was cancelled by the poster.',
        ];
    }
}

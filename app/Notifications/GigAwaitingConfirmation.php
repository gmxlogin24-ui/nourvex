<?php

namespace App\Notifications;

use App\Models\Gig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GigAwaitingConfirmation extends Notification
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
            ->subject('Action needed: confirm delivery on "' . $this->gig->title . '"')
            ->line('The gig "' . $this->gig->title . '" was marked delivered a few days ago and is still awaiting your confirmation.')
            ->line('Amount held in escrow: ₦' . number_format($this->gig->amount))
            ->action('Review & Confirm', route('gigs.show', $this->gig->id))
            ->line('If something is wrong with the delivered work, you can request changes instead of confirming.');
    }

    public function toArray($notifiable): array
    {
        return [
            'gig_id' => $this->gig->id,
            'title' => $this->gig->title,
            'message' => 'Please confirm delivery for "' . $this->gig->title . '" to release payment.',
        ];
    }
}

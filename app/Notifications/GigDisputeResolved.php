<?php

namespace App\Notifications;

use App\Models\GigDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GigDisputeResolved extends Notification
{
    use Queueable;

    public function __construct(public GigDispute $dispute, public string $outcome, public string $role)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $gig = $this->dispute->gig;
        $outcomeText = $this->outcome === 'paid'
            ? 'the worker was paid the remaining balance.'
            : 'the dispute was dismissed with no payment.';

        return (new MailMessage)
            ->subject('Dispute resolved: "' . $gig->title . '"')
            ->line('The dispute on "' . $gig->title . '" has been resolved — ' . $outcomeText)
            ->action('View Gig', route('gigs.show', $gig->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'gig_id' => $this->dispute->gig_id,
            'title' => $this->dispute->gig->title,
            'message' => 'Your dispute on "' . $this->dispute->gig->title . '" was resolved.',
        ];
    }
}

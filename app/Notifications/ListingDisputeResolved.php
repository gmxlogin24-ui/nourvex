<?php

namespace App\Notifications;

use App\Models\ListingDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ListingDisputeResolved extends Notification
{
    use Queueable;

    public function __construct(public ListingDispute $dispute, public string $outcome, public string $role)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $listing = $this->dispute->listing;
        $outcomeText = match ($this->outcome) {
            'paid' => 'the escrowed funds were released to the seller.',
            'refunded' => 'the buyer was refunded in full.',
            default => 'the dispute was closed with no action taken.',
        };

        return (new MailMessage)
            ->subject('Dispute resolved: "' . $listing->title . '"')
            ->line('The dispute on "' . $listing->title . '" has been resolved — ' . $outcomeText)
            ->action('View Listing', route('marketplace.show', $listing->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'listing_id' => $this->dispute->listing_id,
            'title' => $this->dispute->listing->title,
            'message' => 'Your dispute on "' . $this->dispute->listing->title . '" was resolved.',
        ];
    }
}

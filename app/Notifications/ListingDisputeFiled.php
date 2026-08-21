<?php

namespace App\Notifications;

use App\Models\ListingDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ListingDisputeFiled extends Notification
{
    use Queueable;

    public function __construct(public ListingDispute $dispute)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $listing = $this->dispute->listing;
        return (new MailMessage)
            ->subject('New marketplace dispute: "' . $listing->title . '"')
            ->line('A dispute was filed on the listing "' . $listing->title . '".')
            ->line('Amount held in escrow: ₦' . number_format($listing->amount))
            ->action('Review Dispute', route('admin.marketplace-disputes.index'))
            ->line('Please review and resolve as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'listing_id' => $this->dispute->listing_id,
            'title' => $this->dispute->listing->title,
            'message' => 'A dispute was filed on "' . $this->dispute->listing->title . '".',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarketplaceOrderShipped extends Notification
{
    use Queueable;

    public function __construct(public Listing $listing)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'listing_id' => $this->listing->id,
            'message' => '"' . $this->listing->title . '" was marked as shipped.',
        ];
    }
}

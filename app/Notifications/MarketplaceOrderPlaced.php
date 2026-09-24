<?php

namespace App\Notifications;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class MarketplaceOrderPlaced extends Notification
{
    use Queueable;

    public function __construct(public Listing $listing, public User $buyer)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->buyer->name . ' bought "' . $this->listing->title . '" — payment held in escrow.',
            'listing_id' => $this->listing->id,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('New order')
            ->icon('/favicon.ico')
            ->body($this->buyer->name . ' bought "' . $this->listing->title . '"')
            ->data(['url' => route('marketplace.show', $this->listing->id)]);
    }
}

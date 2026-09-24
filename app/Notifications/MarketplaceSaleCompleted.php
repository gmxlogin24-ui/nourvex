<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class MarketplaceSaleCompleted extends Notification
{
    use Queueable;

    public function __construct(public Listing $listing, public float $payout, public bool $auto = false)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        $reason = $this->auto
            ? 'Auto-released after 6 days with no dispute'
            : 'Delivery confirmed';
        return [
            'message' => $reason . ' — ₦' . number_format($this->payout) . ' released for "' . $this->listing->title . '".',
            'listing_id' => $this->listing->id,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Payment released')
            ->icon('/favicon.ico')
            ->body('₦' . number_format($this->payout) . ' for "' . $this->listing->title . '"')
            ->data(['url' => route('marketplace.show', $this->listing->id)]);
    }
}

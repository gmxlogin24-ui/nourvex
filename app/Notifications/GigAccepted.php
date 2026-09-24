<?php

namespace App\Notifications;

use App\Models\Gig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GigAccepted extends Notification
{
    use Queueable;

    public function __construct(public Gig $gig)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'gig_id' => $this->gig->id,
            'message' => 'Your gig "' . $this->gig->title . '" was accepted.',
        ];
    }
}

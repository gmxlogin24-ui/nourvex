<?php

namespace App\Console\Commands;

use App\Models\Gig;
use App\Notifications\GigAwaitingConfirmation;
use Illuminate\Console\Command;

class SendGigPaymentReminders extends Command
{
    protected $signature = 'gigs:remind-pending-payments';
    protected $description = 'Reminds posters about delivered gigs they have not confirmed after 3 days';

    public function handle(): void
    {
        $gigs = Gig::where('status', 'delivered')
            ->where('delivered_at', '<=', now()->subDays(3))
            ->whereNull('payment_reminder_sent_at')
            ->with('poster')
            ->get();

        foreach ($gigs as $gig) {
            if ($gig->poster) {
                $gig->poster->notify(new GigAwaitingConfirmation($gig));
            }
            $gig->update(['payment_reminder_sent_at' => now()]);
        }

        $this->info($gigs->count() . ' reminder(s) marked.');
    }
}

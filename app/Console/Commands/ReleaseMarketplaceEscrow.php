<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseMarketplaceEscrow extends Command
{
    protected $signature = 'marketplace:release-escrow';
    protected $description = 'Auto-releases escrow to sellers for single-item listings sold more than 6 days ago with no open dispute';

    public function handle(): void
    {
        $listings = Listing::where('status', 'sold')
            ->whereNotNull('sold_at')
            ->where('sold_at', '<=', now()->subDays(6))
            ->whereDoesntHave('dispute', fn ($q) => $q->where('status', 'open'))
            ->get();

        $released = 0;

        foreach ($listings as $listing) {
            DB::transaction(function () use ($listing, &$released) {
                $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->first();
                if ($lockedListing->status !== 'sold') {
                    return;
                }

                $seller = $lockedListing->user_id ? User::where('id', $lockedListing->user_id)->lockForUpdate()->first() : null;

                if ($seller) {
                    $rate = match ($seller->tier) {
                        'tier2', 'tier3' => 0,
                        'tier1' => 0.03,
                        default => 0.05,
                    };
                    $commission = round($lockedListing->amount * $rate, 2);
                    $payout = $lockedListing->amount - $commission;

                    $seller->increment('wallet_balance', $payout);
                    Transaction::create([
                        'user_id' => $seller->id,
                        'type' => 'marketplace_sale',
                        'amount' => $payout,
                        'description' => 'Sold (auto-released after 6 days): ' . $lockedListing->title . ($commission > 0 ? ' (after ₦' . number_format($commission) . ' platform fee)' : ''),
                    ]);
                    \App\Models\PlatformRevenue::record('marketplace', $commission, 'Commission (auto-released): ' . $lockedListing->title, 'listing', $lockedListing->id);
                    // Was silent before - confirmDelivery() (the manual path)
                    // already notifies the seller when they're paid; the
                    // automatic path should tell them too, not leave them to
                    // notice a wallet change on their own.
                    $seller->notify(new \App\Notifications\MarketplaceSaleCompleted($lockedListing, $payout, auto: true));
                }

                $lockedListing->update(['status' => 'completed']);
                $released++;
            });
        }

        $this->info($released . ' listing(s) auto-released.');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\OrderCheckout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OrderRefund extends Command
{
    protected $signature = 'orders:refund {id? : Order id to refund} {--list : List card orders waiting for a refund}';
    protected $description = 'List or refund card orders paid for an item that sold out first.';

    public function handle(): int
    {
        if ($this->option('list') || !$this->argument('id')) {
            $rows = OrderCheckout::where('status', 'paid_unfulfilled')->orderBy('id')->get();
            if ($rows->isEmpty()) {
                $this->info('No card orders are waiting for a refund.');
                return 0;
            }
            $this->table(['id', 'user_id', 'amount', 'tx_ref', 'paid_at'],
                $rows->map(fn ($r) => [$r->id, $r->user_id, $r->amount, $r->tx_ref, (string) $r->paid_at])->all());
            return 0;
        }

        $row = OrderCheckout::find($this->argument('id'));
        if (!$row || $row->status !== 'paid_unfulfilled') {
            $this->error('That order is not waiting for a refund.');
            return 1;
        }
        $secret = config('services.paystack.secret');
        if (empty($secret)) {
            $this->error('Paystack secret key is not set in .env.');
            return 1;
        }

        // Full refund to the card the buyer paid with.
        $res = Http::withToken($secret)->timeout(30)
            ->post('https://api.paystack.co/refund', ['transaction' => $row->tx_ref])
            ->json();

        if (($res['status'] ?? false) !== true) {
            $this->error('Paystack did not accept the refund: ' . ($res['message'] ?? 'unknown reason'));
            return 1;
        }

        $row->update(['status' => 'refunded']);
        $this->info('Refund sent for order ' . $row->id . ' (₦' . number_format($row->amount) . '). Paystack says: ' . ($res['data']['status'] ?? 'pending'));
        return 0;
    }
}

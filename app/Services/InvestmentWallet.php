<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Investment money moves through the normal naira wallet and is written to the wallet history.
// Same method names as QCoinService, so the investment code kept its shape.
class InvestmentWallet
{
    public function spend(User $user, int $amount, string $type, ?string $description = null, ?string $referenceId = null): void
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        DB::transaction(function () use ($user, $amount, $type, $description) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($locked->wallet_restricted) {
                throw new RuntimeException('Your wallet is restricted. Clear your balance first.');
            }
            if ((float) $locked->wallet_balance < $amount) {
                throw new RuntimeException('Not enough money in your wallet. Add money and try again.');
            }
            $locked->decrement('wallet_balance', $amount);
            Transaction::create([
                'user_id' => $locked->id,
                'type' => $type,
                'amount' => -$amount,
                'description' => $description,
            ]);
            $user->wallet_balance = $locked->wallet_balance;
            $user->syncOriginalAttribute('wallet_balance');
        });
    }

    public function earn(User $user, int $amount, string $type, ?string $description = null, ?string $referenceId = null): void
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        DB::transaction(function () use ($user, $amount, $type, $description) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $locked->increment('wallet_balance', $amount);
            Transaction::create([
                'user_id' => $locked->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
            ]);
            $user->wallet_balance = $locked->wallet_balance;
            $user->syncOriginalAttribute('wallet_balance');
        });
    }
}

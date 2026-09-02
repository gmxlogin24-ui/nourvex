<?php

namespace App\Services;

use App\Models\QCoinTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QCoinService
{
    public const DAILY_CLAIM_AMOUNT = 50;

    /**
     * Credit Q Coins to a user. Atomic: row-locks the user, updates balance,
     * writes the ledger entry, all inside one DB transaction. This is the
     * ONLY method in the codebase that should increase q_coin_balance.
     */
    public function earn(User $user, int $amount, string $type, ?string $description = null, ?string $referenceId = null): QCoinTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Earn amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceId) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $locked->q_coin_balance += $amount;
            $locked->save();

            $txn = QCoinTransaction::create([
                'user_id' => $locked->id,
                'amount' => $amount,
                'balance_after' => $locked->q_coin_balance,
                'type' => $type,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);

            // Keep the caller's in-memory model in sync.
            $user->q_coin_balance = $locked->q_coin_balance;

            return $txn;
        });
    }

    /**
     * Debit Q Coins from a user (spend/redemption). Throws if insufficient balance.
     * ONLY method that should decrease q_coin_balance.
     */
    public function spend(User $user, int $amount, string $type, ?string $description = null, ?string $referenceId = null): QCoinTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Spend amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceId) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->q_coin_balance < $amount) {
                throw new RuntimeException('Insufficient Q Coin balance.');
            }

            $locked->q_coin_balance -= $amount;
            $locked->save();

            $txn = QCoinTransaction::create([
                'user_id' => $locked->id,
                'amount' => -$amount,
                'balance_after' => $locked->q_coin_balance,
                'type' => $type,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);

            $user->q_coin_balance = $locked->q_coin_balance;

            return $txn;
        });
    }

    /**
     * Daily claim, with the "already claimed today" check done INSIDE the
     * lock so two simultaneous requests can't both succeed.
     */
    // Streak bonus: +5 Q per consecutive day, capped at +100 (day 20+).
    protected function streakBonus(int $streakDays): int
    {
        return min($streakDays * 5, 100);
    }

    public function claimDaily(User $user): QCoinTransaction
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        return DB::transaction(function () use ($user, $today, $yesterday) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->q_coin_last_claim_date === $today) {
                throw new RuntimeException('Already claimed today.');
            }

            $newStreak = ($locked->q_coin_last_claim_date === $yesterday)
                ? $locked->q_coin_streak_days + 1
                : 1;

            $bonus = $this->streakBonus($newStreak);
            $total = self::DAILY_CLAIM_AMOUNT + $bonus;

            $locked->q_coin_balance += $total;
            $locked->q_coin_last_claim_date = $today;
            $locked->q_coin_streak_days = $newStreak;
            $locked->save();

            $txn = QCoinTransaction::create([
                'user_id' => $locked->id,
                'amount' => $total,
                'balance_after' => $locked->q_coin_balance,
                'type' => 'daily_claim',
                'description' => $bonus > 0
                    ? "Daily claim + streak bonus (day {$newStreak})"
                    : 'Daily Q Coin claim',
            ]);

            $user->q_coin_balance = $locked->q_coin_balance;
            $user->q_coin_last_claim_date = $today;
            $user->q_coin_streak_days = $newStreak;

            return $txn;
        });
    }
}

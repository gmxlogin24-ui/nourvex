<?php

namespace App\Services;

use App\Models\QCoinTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QCoinService
{
    // Kept for older code that may still reference it. Daily claims now come from
    // config/plans.php (qcoin_daily) plus the personal streak bonus (config/qcoin.php).
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

    // Base daily claim for the user's plan (admins get the top plan's amount).
    public function baseFor(User $user): int
    {
        if ($user->isAdmin()) {
            return (int) config('plans.tier3.qcoin_daily', 0);
        }
        return (int) $user->planRule('qcoin_daily', 0);
    }

    // Personal streak bonus: grows by step_bonus every step_days consecutive days, capped.
    public function streakBonusFor(int $streakDays): int
    {
        $stepDays = max(1, (int) config('qcoin.streak_step_days', 7));
        $stepBonus = (int) config('qcoin.streak_step_bonus', 5);
        $max = (int) config('qcoin.streak_max_bonus', 25);

        return min(intdiv(max($streakDays, 0), $stepDays) * $stepBonus, $max);
    }

    /**
     * Daily claim, with the "already claimed today" check done INSIDE the
     * lock so two simultaneous requests can't both succeed.
     */
    public function claimDaily(User $user): QCoinTransaction
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        return DB::transaction(function () use ($user, $today, $yesterday) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->q_coin_last_claim_date === $today) {
                throw new RuntimeException('Already claimed today.');
            }

            $base = $this->baseFor($locked);
            if ($base <= 0) {
                throw new RuntimeException('Your plan does not include daily Q Coins.');
            }

            $newStreak = ($locked->q_coin_last_claim_date === $yesterday)
                ? $locked->q_coin_streak_days + 1
                : 1;

            $bonus = $this->streakBonusFor($newStreak);
            $total = $base + $bonus;

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

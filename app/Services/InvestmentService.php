<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentPayout;
use App\Models\InvestmentProject;
use App\Models\InvestmentVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvestmentService
{
    // Lock order everywhere: project row first, then user rows (QCoinService locks the user).
    public function __construct(protected QCoinService $qcoin) {}

    public static function naira(int $q): string
    {
        return '₦' . number_format($q / config('investment.qcoin_per_naira'), 2);
    }

    public function isVerified(User $user): bool
    {
        return InvestmentVerification::where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    public function capFor(User $user): int
    {
        return $this->isVerified($user)
            ? (int) config('investment.cap_verified_q')
            : (int) config('investment.cap_unverified_q');
    }

    public function submitVerification(User $user, string $details): InvestmentVerification
    {
        if ($this->isVerified($user)) {
            throw new RuntimeException('You are already verified.');
        }

        return InvestmentVerification::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'pending', 'details' => $details, 'reviewed_by' => null, 'reviewed_at' => null]
        );
    }

    public function reviewVerification(InvestmentVerification $verification, User $admin, bool $approve): void
    {
        DB::transaction(function () use ($verification, $admin, $approve) {
            $locked = InvestmentVerification::whereKey($verification->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new RuntimeException('This request has already been reviewed.');
            }

            $locked->update([
                'status' => $approve ? 'approved' : 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function createProject(User $owner, array $data): InvestmentProject
    {
        if (! $this->isVerified($owner)) {
            throw new RuntimeException('Only verified members can list a project.');
        }

        $active = InvestmentProject::where('owner_id', $owner->id)
            ->whereIn('status', ['pending_review', 'open', 'funded'])
            ->count();
        if ($active >= (int) config('investment.max_active_projects_per_owner')) {
            throw new RuntimeException('You already have the maximum number of active projects.');
        }

        $goal = (int) $data['goal_q'];
        if ($goal < (int) config('investment.min_goal_q') || $goal > (int) config('investment.max_goal_q')) {
            throw new RuntimeException('Goal is outside the allowed range.');
        }

        $days = (int) $data['duration_days'];
        if ($days < (int) config('investment.min_duration_days') || $days > (int) config('investment.max_duration_days')) {
            throw new RuntimeException('Duration is outside the allowed range.');
        }

        $minInvest = (int) config('investment.min_invest_q');
        $shareCap = intdiv($goal * (int) config('investment.max_share_of_goal_percent'), 100);
        $perInvestor = min((int) $data['max_invest_q'], $shareCap);
        if ($perInvestor < $minInvest) {
            throw new RuntimeException('Per-investor limit is too low for this goal.');
        }

        return InvestmentProject::create([
            'owner_id' => $owner->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'],
            'goal_q' => $goal,
            'min_invest_q' => $minInvest,
            'max_invest_q' => $perInvestor,
            'raised_q' => 0,
            'released_q' => 0,
            'returns_reported_q' => 0,
            'returns_distributed_q' => 0,
            'status' => 'pending_review',
            'closes_at' => now()->addDays($days),
        ]);
    }

    public function approveProject(InvestmentProject $project): void
    {
        DB::transaction(function () use ($project) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== 'pending_review') {
                throw new RuntimeException('Only projects waiting for review can be approved.');
            }
            if ($p->closes_at === null || $p->closes_at->isPast()) {
                throw new RuntimeException('The deadline has passed. The owner must submit again.');
            }

            $p->status = 'open';
            $p->approved_at = now();
            $p->save();
        });
    }

    public function invest(User $user, InvestmentProject $project, int $amount, string $key): Investment
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        return DB::transaction(function () use ($user, $project, $amount, $key) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            // Same request sent twice (double tap, retry): return the original, never spend twice.
            $existing = Investment::where('idempotency_key', $key)->first();
            if ($existing) {
                if ((int) $existing->user_id !== (int) $user->id || (int) $existing->investment_project_id !== (int) $p->id) {
                    throw new RuntimeException('This request key was already used.');
                }

                return $existing;
            }

            if (! $p->isOpen()) {
                throw new RuntimeException('This project is not open for investment.');
            }
            if ((int) $p->owner_id === (int) $user->id) {
                throw new RuntimeException('You cannot invest in your own project.');
            }
            if ($amount < $p->min_invest_q) {
                throw new RuntimeException('Minimum investment is ' . number_format($p->min_invest_q) . ' Q.');
            }
            if ($amount > $p->remainingGoal()) {
                throw new RuntimeException('Only ' . number_format($p->remainingGoal()) . ' Q is left to raise.');
            }

            $already = (int) Investment::where('user_id', $user->id)
                ->where('investment_project_id', $p->id)
                ->where('status', 'active')
                ->sum('amount_q');
            $limit = min($this->capFor($user), $p->max_invest_q);
            if ($already + $amount > $limit) {
                $room = max(0, $limit - $already);
                throw new RuntimeException('You can still invest up to ' . number_format($room) . ' Q in this project.');
            }

            $inv = Investment::create([
                'user_id' => $user->id,
                'investment_project_id' => $p->id,
                'amount_q' => $amount,
                'status' => 'active',
                'idempotency_key' => $key,
            ]);

            // Throws if the user cannot cover it. The outer transaction then rolls everything back.
            $this->qcoin->spend($user, $amount, 'investment', 'Invested in ' . $p->title, (string) $inv->id);

            $p->raised_q += $amount;
            if ($p->raised_q >= $p->goal_q) {
                $p->status = 'funded';
            }
            $p->save();

            return $inv;
        });
    }

    // Moves raised Q from escrow to the project owner. Escrow only, so Q is conserved.
    public function release(InvestmentProject $project, int $amount): void
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        DB::transaction(function () use ($project, $amount) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== 'funded') {
                throw new RuntimeException('Funds can only be released from a funded project.');
            }
            if ($amount > $p->availableToRelease()) {
                throw new RuntimeException('Amount is more than the escrow balance (' . number_format($p->availableToRelease()) . ' Q).');
            }

            $owner = User::whereKey($p->owner_id)->lockForUpdate()->firstOrFail();

            $p->released_q += $amount;
            $p->save();

            $this->qcoin->earn($owner, $amount, 'investment_release', 'Released funds for ' . $p->title, 'proj-' . $p->id . '-rel-' . $p->released_q);
        });
    }

    // Admin records income the platform has actually received. Nothing is paid until distributeReturns().
    public function reportReturns(InvestmentProject $project, int $amount): void
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        DB::transaction(function () use ($project, $amount) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== 'funded') {
                throw new RuntimeException('Returns can only be reported on a funded project.');
            }

            $p->returns_reported_q += $amount;
            $p->save();
        });
    }

    // Pays reported returns to investors pro rata, minus the platform fee.
    public function distributeReturns(InvestmentProject $project, int $gross, int $adminId): array
    {
        if ($gross <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        return DB::transaction(function () use ($project, $gross, $adminId) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== 'funded') {
                throw new RuntimeException('Returns can only be paid on a funded project.');
            }
            if ($gross > $p->availableReturns()) {
                throw new RuntimeException('Amount is more than the reported returns not yet paid (' . number_format($p->availableReturns()) . ' Q).');
            }

            $investments = Investment::where('investment_project_id', $p->id)
                ->where('status', 'active')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $base = (int) $investments->sum('amount_q');
            if ($base <= 0) {
                throw new RuntimeException('No active investments to pay.');
            }

            $fee = intdiv($gross * (int) config('investment.platform_fee_percent'), 100);
            $net = $gross - $fee;
            $paid = 0;

            foreach ($investments as $inv) {
                $share = intdiv($net * $inv->amount_q, $base);
                if ($share <= 0) {
                    continue;
                }

                $this->payOut(
                    $inv,
                    $p,
                    $share,
                    'return',
                    'ret-' . $p->id . '-' . $p->returns_distributed_q . '-' . $inv->id,
                    'investment_return',
                    'Return: ' . $p->title . ' (admin ' . $adminId . ')'
                );
                $paid += $share;
            }

            $p->returns_distributed_q += $gross;
            $p->save();

            return ['gross' => $gross, 'paid' => $paid, 'platform' => $gross - $paid];
        });
    }

    // Cancels a project and refunds every active investor. Blocked once any funds were released.
    public function cancel(InvestmentProject $project): int
    {
        return DB::transaction(function () use ($project) {
            $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if (! in_array($p->status, ['open', 'funded'], true)) {
                throw new RuntimeException('Only open or funded projects can be cancelled.');
            }
            if ($p->released_q > 0) {
                throw new RuntimeException('Cannot cancel after funds were released to the owner.');
            }

            $investments = Investment::where('investment_project_id', $p->id)
                ->where('status', 'active')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($investments as $inv) {
                $inv->status = 'refunded';
                $inv->save();

                $this->payOut(
                    $inv,
                    $p,
                    (int) $inv->amount_q,
                    'refund',
                    'refund-' . $p->id . '-' . $inv->id,
                    'investment_refund',
                    'Refund: ' . $p->title
                );
            }

            $p->status = 'cancelled';
            $p->save();

            return $investments->count();
        });
    }

    private function payOut(Investment $inv, InvestmentProject $p, int $amount, string $kind, string $reference, string $type, string $description): void
    {
        $payout = InvestmentPayout::create([
            'investment_id' => $inv->id,
            'user_id' => $inv->user_id,
            'investment_project_id' => $p->id,
            'kind' => $kind,
            'amount_q' => $amount,
            'reference' => $reference,
        ]);

        $this->qcoin->earn($inv->user, $amount, $type, $description, (string) $payout->id);
    }
}

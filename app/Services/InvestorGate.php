<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentDiligence;
use App\Models\InvestmentMilestone;
use App\Models\InvestmentPayout;
use App\Models\InvestmentProject;
use App\Models\InvestorProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// One place for every rule about WHO may invest or list, and WHEN a project may open.
class InvestorGate
{
    public const CHECKS = [
        'owner_kyc' => 'Owner identity checked',
        'cac' => 'Company registration (CAC) checked',
        'directors' => 'Directors and officers checked',
        'site_visit' => 'Site visited in person',
        'permits' => 'Permits and licences checked',
        'feasibility' => 'Feasibility study reviewed',
        'use_of_funds' => 'Use of funds reviewed',
        'lawyer' => 'Lawyer cleared the offer',
    ];

    // Lock order everywhere: project row first, then the investor profile, then user rows (QCoinService).
    public function __construct(protected InvestmentService $svc, protected InvestmentWallet $qcoin) {}

    public function kyc(User $user): ?object
    {
        return DB::table('kyc_profiles')->where('user_id', $user->id)->first();
    }

    public function kycComplete(?object $k): bool
    {
        if (! $k) {
            return false;
        }
        foreach (['phone', 'id_type', 'id_front_path', 'selfie_path', 'address_line', 'city', 'state', 'country'] as $f) {
            if (trim((string) ($k->$f ?? '')) === '') {
                return false;
            }
        }
        return true;
    }

    public function countryAllowed(?object $k): bool
    {
        if (! $k || trim((string) ($k->country ?? '')) === '') {
            return false;
        }
        foreach ((array) config('investment.allowed_countries', ['Nigeria']) as $c) {
            if (strcasecmp(trim((string) $k->country), trim((string) $c)) === 0) {
                return true;
            }
        }
        return false;
    }

    public function checklist(User $user): array
    {
        $k = $this->kyc($user);
        $profile = InvestorProfile::where('user_id', $user->id)->first();
        $approved = $k && $k->status === 'approved' && $this->kycComplete($k);

        return [
            ['label' => 'Identity check approved', 'ok' => (bool) $approved, 'url' => route('kyc.show')],
            ['label' => 'Location verified', 'ok' => (bool) ($approved && $this->countryAllowed($k)), 'url' => route('kyc.show', ['step' => 'address'])],
            ['label' => 'Yearly income declared', 'ok' => (bool) ($profile && (int) $profile->annual_income_naira > 0), 'url' => route('investment.profile')],
            ['label' => 'Transaction PIN set', 'ok' => (bool) $user->hasTransactionPin(), 'url' => route('wallet.pin.edit')],
        ];
    }

    public function problems(User $user): array
    {
        $out = [];
        if ($user->is_banned) {
            $out[] = 'Your account is suspended.';
        }
        if ($user->wallet_restricted) {
            $out[] = 'Your wallet is restricted.';
        }
        foreach ($this->checklist($user) as $c) {
            if (! $c['ok']) {
                $out[] = 'Still needed: ' . strtolower($c['label']) . '.';
            }
        }
        return $out;
    }

    // Everything that can be checked before any database transaction opens.
    protected function preflight(User $user, array $ack, string $pin): void
    {
        $problems = $this->problems($user);
        if ($problems) {
            throw new RuntimeException(implode(' ', $problems));
        }
        if (empty($ack['accepted'])) {
            throw new RuntimeException('Please read and accept the risk warning.');
        }
        $key = 'inv-pin-fail:' . $user->id;
        if ((int) Cache::get($key, 0) >= 5) {
            throw new RuntimeException('Too many wrong PINs. Try again in 15 minutes.');
        }
        if (! $user->verifyTransactionPin($pin)) {
            Cache::add($key, 0, now()->addMinutes(15));
            Cache::increment($key);
            throw new RuntimeException('Wrong PIN.');
        }
        Cache::forget($key);
    }

    protected function assertWithinLimit(User $user, int $amount): void
    {
        $pct = (float) config('investment.income_limit_percent', 10);
        if ($pct <= 0) {
            return;
        }
        $profile = InvestorProfile::where('user_id', $user->id)->first();
        $income = $profile ? (int) $profile->annual_income_naira : 0;
        if ($income <= 0) {
            throw new RuntimeException('Declare your yearly income first.');
        }
        $rate = max(1, (int) config('investment.qcoin_per_naira'));
        $limit = $income * $pct / 100;
        $usedQ = (int) Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereYear('created_at', now()->year)
            ->sum('amount_q');
        $used = $usedQ / $rate;
        $new = $amount / $rate;
        if ($used + $new > $limit + 0.001) {
            $left = max(0, $limit - $used);
            throw new RuntimeException(
                'This is over your yearly limit of ₦' . number_format($limit) . ' (' . rtrim(rtrim(number_format($pct, 2), '0'), '.') . '% of your declared income). You can still invest ₦' . number_format($left) . ' this year.'
            );
        }
    }

    public function invest(User $user, InvestmentProject $project, int $amount, string $key, array $ack, string $pin): Investment
    {
        $this->preflight($user, $ack, $pin);

        return DB::transaction(function () use ($user, $project, $amount, $key, $ack) {
            InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();
            InvestorProfile::where('user_id', $user->id)->lockForUpdate()->first();

            if (! Investment::where('idempotency_key', $key)->exists()) {
                $this->assertWithinLimit($user, $amount);
            }

            $inv = $this->svc->invest($user, $project, $amount, $key);

            if ($inv->risk_ack_at === null) {
                $inv->risk_ack_version = (string) config('investment.risk_ack_version');
                $inv->risk_ack_at = now();
                $inv->risk_ack_ip = substr((string) ($ack['ip'] ?? ''), 0, 45);
                $inv->cooling_off_until = now()->addHours((int) config('investment.cooling_off_hours', 48));
                $inv->save();
            }

            return $inv;
        });
    }

    public function cancelInvestment(User $user, Investment $investment): void
    {
        DB::transaction(function () use ($user, $investment) {
            $p = InvestmentProject::whereKey($investment->investment_project_id)->lockForUpdate()->firstOrFail();
            $i = Investment::whereKey($investment->id)->lockForUpdate()->firstOrFail();

            if ((int) $i->user_id !== (int) $user->id) {
                throw new RuntimeException('This is not your investment.');
            }
            if ($i->status !== 'active') {
                throw new RuntimeException('This investment is already closed.');
            }
            if ($p->status !== 'open') {
                throw new RuntimeException('The project is funded or closed, so this can no longer be cancelled.');
            }
            if (! $i->cooling_off_until || now()->gt(Carbon::parse($i->cooling_off_until))) {
                throw new RuntimeException('The cancel window has ended.');
            }

            $i->status = 'refunded';
            $i->cancelled_at = now();
            $i->save();

            $p->raised_q = max(0, (int) $p->raised_q - (int) $i->amount_q);
            $p->save();

            $payout = InvestmentPayout::create([
                'investment_id' => $i->id,
                'user_id' => $i->user_id,
                'investment_project_id' => $p->id,
                'kind' => 'refund',
                'amount_q' => (int) $i->amount_q,
                'reference' => 'cancel-' . $i->id,
            ]);
            $this->qcoin->earn($user, (int) $i->amount_q, 'investment_refund', 'Cancelled: ' . $p->title, (string) $payout->id);
        });
    }

    public function assertCanList(User $owner): void
    {
        $k = $this->kyc($owner);
        if (! $k || $k->status !== 'approved' || ! $this->kycComplete($k) || ! $this->countryAllowed($k)) {
            throw new RuntimeException('Pass the full identity check before you list a project.');
        }
    }

    public function saveDiligence(InvestmentProject $p, Request $r): void
    {
        InvestmentDiligence::updateOrCreate(
            ['investment_project_id' => $p->id],
            [
                'location_state' => (string) $r->input('location_state'),
                'location_address' => (string) $r->input('location_address'),
                'cac_number' => (string) $r->input('cac_number'),
                'use_of_funds' => (string) $r->input('use_of_funds'),
                'risk_disclosure' => (string) $r->input('risk_disclosure'),
            ]
        );
    }

    public function assertDiligenceComplete(InvestmentProject $p): void
    {
        $d = InvestmentDiligence::where('investment_project_id', $p->id)->first();
        if (! $d) {
            throw new RuntimeException('Due diligence has not been started. Open the project due diligence page first.');
        }
        $miss = [];
        $checks = (array) $d->checks;
        foreach (self::CHECKS as $k => $label) {
            if (empty($checks[$k])) {
                $miss[] = $label;
            }
        }
        foreach (['location_state' => 'Project state', 'cac_number' => 'CAC number', 'use_of_funds' => 'Use of funds', 'risk_disclosure' => 'Risk disclosure'] as $f => $l) {
            if (trim((string) $d->$f) === '') {
                $miss[] = $l;
            }
        }
        if (! in_array($d->risk_rating, ['low', 'medium', 'high'], true)) {
            $miss[] = 'Risk rating';
        }
        $sum = (int) InvestmentMilestone::where('investment_project_id', $p->id)->sum('amount_q');
        if ($sum !== (int) $p->goal_q) {
            $miss[] = 'Milestones that add up to the goal (₦' . number_format($sum) . ' of ₦' . number_format($p->goal_q) . ' set)';
        }
        if ($miss) {
            throw new RuntimeException('Cannot open yet. Missing: ' . implode('; ', $miss) . '.');
        }
    }

    public function releaseMilestone(InvestmentMilestone $milestone, string $proof, int $adminId): void
    {
        DB::transaction(function () use ($milestone, $proof, $adminId) {
            $p = InvestmentProject::whereKey($milestone->investment_project_id)->lockForUpdate()->firstOrFail();
            $m = InvestmentMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();

            if ($m->status !== 'pending') {
                throw new RuntimeException('This milestone is already released.');
            }
            $earlier = InvestmentMilestone::where('investment_project_id', $p->id)
                ->where('position', '<', $m->position)
                ->where('status', 'pending')
                ->exists();
            if ($earlier) {
                throw new RuntimeException('Release the earlier milestones first.');
            }

            $this->svc->release($p, (int) $m->amount_q);

            $m->status = 'released';
            $m->proof_note = $proof;
            $m->released_at = now();
            $m->save();

            $this->audit($adminId, 'milestone_release', 'milestone', $m->id, 'project ' . $p->id . ', ' . $m->amount_q . ' Q');
        });
    }

    public function audit(?int $adminId, string $action, ?string $type = null, ?int $id = null, ?string $details = null): void
    {
        DB::table('investment_audit')->insert([
            'admin_id' => $adminId,
            'action' => $action,
            'subject_type' => $type,
            'subject_id' => $id,
            'details' => $details,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}

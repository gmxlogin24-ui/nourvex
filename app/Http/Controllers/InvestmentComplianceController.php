<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentDiligence;
use App\Models\InvestmentMilestone;
use App\Models\InvestmentProject;
use App\Models\InvestmentUpdate;
use App\Models\InvestorProfile;
use App\Models\KycProfile;
use App\Services\InvestorGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class InvestmentComplianceController extends Controller
{
    // ---------- investor side ----------

    public function profile()
    {
        $user = Auth::user();
        $profile = InvestorProfile::where('user_id', $user->id)->first();
        $rate = max(1, (int) config('investment.qcoin_per_naira'));
        $pct = (float) config('investment.income_limit_percent', 10);
        $income = $profile ? (int) $profile->annual_income_naira : 0;
        $limit = $pct > 0 ? $income * $pct / 100 : null;
        $used = ((int) Investment::where('user_id', $user->id)->where('status', 'active')->whereYear('created_at', now()->year)->sum('amount_q')) / $rate;

        return view('investment.profile', ['profile' => $profile, 'income' => $income, 'pct' => $pct, 'limit' => $limit, 'used' => $used]);
    }

    public function saveProfile(Request $request)
    {
        $data = $request->validate(['annual_income_naira' => 'required|integer|min:1|max:100000000000']);
        $user = Auth::user();
        $existing = InvestorProfile::where('user_id', $user->id)->first();
        if ($existing && (int) $existing->annual_income_naira > 0) {
            return back()->with('error', 'Your declared income is locked. Contact support to change it.');
        }
        InvestorProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['annual_income_naira' => (int) $data['annual_income_naira'], 'declared_at' => now()]
        );

        return back()->with('status', 'Saved.');
    }

    public function cancel(Investment $investment, InvestorGate $gate)
    {
        try {
            $gate->cancelInvestment(Auth::user(), $investment);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Cancelled. Your money is back in your wallet.');
    }

    // ---------- admin: ID checks ----------

    public function kycIndex()
    {
        $rows = DB::table('kyc_profiles')
            ->join('users', 'users.id', '=', 'kyc_profiles.user_id')
            ->where('kyc_profiles.status', 'submitted')
            ->orderBy('kyc_profiles.submitted_at')
            ->select('kyc_profiles.*', 'users.name', 'users.email')
            ->get();

        return view('admin.kyc.index', ['rows' => $rows]);
    }

    public function kycFile(KycProfile $profile, string $kind, InvestorGate $gate)
    {
        $col = ['id_front' => 'id_front_path', 'id_back' => 'id_back_path', 'selfie' => 'selfie_path'][$kind] ?? null;
        abort_unless($col, 404);
        $path = (string) $profile->$col;
        abort_unless($path !== '' && str_starts_with($path, 'kyc/') && ! str_contains($path, '..') && Storage::disk('local')->exists($path), 404);
        $gate->audit(Auth::id(), 'kyc_file_view', 'kyc', $profile->id, $kind);
        $res = Storage::disk('local')->response($path);
        $res->headers->set('Cache-Control', 'private, no-store');

        return $res;
    }

    public function kycDecide(Request $request, KycProfile $profile, InvestorGate $gate)
    {
        $d = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:500',
        ]);
        if ($d['decision'] === 'reject' && trim((string) ($d['note'] ?? '')) === '') {
            return back()->with('error', 'Add a short reason so the person knows what to fix.');
        }
        try {
            DB::transaction(function () use ($profile, $d, $gate) {
                $k = DB::table('kyc_profiles')->where('id', $profile->id)->lockForUpdate()->first();
                if (! $k || $k->status !== 'submitted') {
                    throw new RuntimeException('This request is not waiting for review.');
                }
                if ($d['decision'] === 'approve') {
                    if (! $gate->kycComplete($k)) {
                        throw new RuntimeException('Details are incomplete. Phone, ID, selfie and full address are all needed.');
                    }
                    if (! $gate->countryAllowed($k)) {
                        throw new RuntimeException('This country is not on the allowed list.');
                    }
                }
                DB::table('kyc_profiles')->where('id', $k->id)->update([
                    'status' => $d['decision'] === 'approve' ? 'approved' : 'rejected',
                    'reviewed_at' => now(),
                    'review_note' => $d['note'] ?? null,
                    'reviewed_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
                $gate->audit(Auth::id(), 'kyc_' . $d['decision'], 'kyc', $k->id, 'user ' . $k->user_id);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Done.');
    }

    // ---------- admin: due diligence, milestones, updates ----------

    public function diligence(InvestmentProject $project)
    {
        return view('admin.investment.diligence', [
            'project' => $project,
            'd' => InvestmentDiligence::where('investment_project_id', $project->id)->first(),
            'milestones' => InvestmentMilestone::where('investment_project_id', $project->id)->orderBy('position')->get(),
            'updates' => InvestmentUpdate::where('investment_project_id', $project->id)->latest()->take(20)->get(),
        ]);
    }

    public function diligenceSave(Request $request, InvestmentProject $project, InvestorGate $gate)
    {
        $data = $request->validate([
            'risk_rating' => 'nullable|in:low,medium,high',
            'location_state' => 'nullable|string|max:100',
            'location_address' => 'nullable|string|max:255',
            'cac_number' => 'nullable|string|max:40',
            'use_of_funds' => 'nullable|string|max:2000',
            'risk_disclosure' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);
        $checks = [];
        foreach (array_keys(InvestorGate::CHECKS) as $k) {
            $checks[$k] = $request->boolean('checks.' . $k);
        }
        InvestmentDiligence::updateOrCreate(
            ['investment_project_id' => $project->id],
            $data + ['checks' => $checks, 'updated_by' => Auth::id()]
        );
        $gate->audit(Auth::id(), 'diligence_save', 'project', $project->id, json_encode($checks));

        return back()->with('status', 'Saved.');
    }

    public function milestoneStore(Request $request, InvestmentProject $project, InvestorGate $gate)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'amount_q' => 'required|integer|min:1',
        ]);
        try {
            DB::transaction(function () use ($project, $data, $gate) {
                $p = InvestmentProject::whereKey($project->id)->lockForUpdate()->firstOrFail();
                if (! in_array($p->status, ['pending_review', 'open', 'funded'], true)) {
                    throw new RuntimeException('This project is closed.');
                }
                $sum = (int) InvestmentMilestone::where('investment_project_id', $p->id)->sum('amount_q');
                if ($sum + (int) $data['amount_q'] > (int) $p->goal_q) {
                    throw new RuntimeException('Milestones cannot add up to more than the goal.');
                }
                $pos = (int) InvestmentMilestone::where('investment_project_id', $p->id)->max('position') + 1;
                $m = InvestmentMilestone::create([
                    'investment_project_id' => $p->id,
                    'position' => $pos,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'amount_q' => (int) $data['amount_q'],
                    'status' => 'pending',
                ]);
                $gate->audit(Auth::id(), 'milestone_add', 'milestone', $m->id, 'project ' . $p->id . ', ' . $m->amount_q . ' Q');
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Milestone added.');
    }

    public function milestoneDelete(InvestmentMilestone $milestone, InvestorGate $gate)
    {
        $project = InvestmentProject::findOrFail($milestone->investment_project_id);
        if ($project->status !== 'pending_review' || $milestone->status !== 'pending') {
            return back()->with('error', 'Milestones can only be removed before the project opens.');
        }
        $gate->audit(Auth::id(), 'milestone_delete', 'milestone', $milestone->id, 'project ' . $project->id);
        $milestone->delete();

        return back()->with('status', 'Removed.');
    }

    public function milestoneRelease(Request $request, InvestmentMilestone $milestone, InvestorGate $gate)
    {
        $data = $request->validate(['proof_note' => 'required|string|min:10|max:1000']);
        try {
            $gate->releaseMilestone($milestone, $data['proof_note'], (int) Auth::id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Milestone released to the owner.');
    }

    public function updatePost(Request $request, InvestmentProject $project, InvestorGate $gate)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:3000',
        ]);
        $u = InvestmentUpdate::create([
            'investment_project_id' => $project->id,
            'author_id' => Auth::id(),
            'title' => $data['title'],
            'body' => $data['body'],
        ]);
        $gate->audit(Auth::id(), 'update_post', 'project', $project->id, 'update ' . $u->id);

        return back()->with('status', 'Posted.');
    }
}

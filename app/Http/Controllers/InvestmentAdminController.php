<?php

namespace App\Http\Controllers;

use App\Models\InvestmentProject;
use App\Models\InvestmentVerification;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class InvestmentAdminController extends Controller
{
    public function index()
    {
        return view('admin.investment.index', [
            'pendingVerifications' => InvestmentVerification::with('user')->where('status', 'pending')->oldest()->get(),
            'pendingProjects' => InvestmentProject::with('owner')->where('status', 'pending_review')->oldest()->get(),
            'activeProjects' => InvestmentProject::with('owner')->whereIn('status', ['open', 'funded'])->latest()->get(),
        ]);
    }

    public function reviewVerification(Request $request, InvestmentVerification $verification, InvestmentService $svc)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject']);

        try {
            $svc->reviewVerification($verification, Auth::user(), $data['decision'] === 'approve');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Verification ' . $data['decision'] . 'd.');
    }

    public function approve(InvestmentProject $project, InvestmentService $svc)
    {
        try {
            $svc->approveProject($project);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Project is open for investment.');
    }

    public function release(Request $request, InvestmentProject $project, InvestmentService $svc)
    {
        $data = $request->validate(['amount_q' => 'required|integer|min:1']);

        try {
            throw new RuntimeException('Funds are released by milestone. Open the project due diligence page.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Funds released to the owner.');
    }

    public function reportReturns(Request $request, InvestmentProject $project, InvestmentService $svc)
    {
        $data = $request->validate(['amount_q' => 'required|integer|min:1']);

        try {
            $svc->reportReturns($project, (int) $data['amount_q']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Returns recorded. Distribute them when ready.');
    }

    public function distribute(Request $request, InvestmentProject $project, InvestmentService $svc)
    {
        $data = $request->validate(['amount_q' => 'required|integer|min:1']);

        try {
            $result = $svc->distributeReturns($project, (int) $data['amount_q'], (int) Auth::id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Paid ₦' . number_format($result['paid']) . ' to investors. Platform kept ₦' . number_format($result['platform']) . '.');
    }

    public function cancel(InvestmentProject $project, InvestmentService $svc)
    {
        try {
            $count = $svc->cancel($project);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Project cancelled. ' . $count . ' investment(s) refunded.');
    }
}

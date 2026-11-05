<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentProject;
use App\Models\InvestmentVerification;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class InvestmentController extends Controller
{
    public function index(InvestmentService $svc)
    {
        $user = Auth::user();

        $myInvestments = Investment::with('project')->where('user_id', $user->id)->latest()->get();

        return view('investment.index', [
            'qBalance' => (int) floor((float) $user->wallet_balance),
            'verification' => InvestmentVerification::where('user_id', $user->id)->first(),
            'isVerified' => $svc->isVerified($user),
            'openProjects' => InvestmentProject::where('status', 'open')->where('closes_at', '>', now())->latest()->get(),
            'myInvestments' => $myInvestments,
            'myProjects' => InvestmentProject::where('owner_id', $user->id)->latest()->get(),
            'totalInvested' => (int) $myInvestments->where('status', 'active')->sum('amount_q'),
        ]);
    }

    public function create(InvestmentService $svc)
    {
        if (! $svc->isVerified(Auth::user())) {
            return redirect()->route('investment.index')->with('error', 'Get verified before you list a project.');
        }

        return view('investment.create', ['types' => config('investment.types')]);
    }

    public function store(Request $request, InvestmentService $svc)
    {
        $data = $request->validate([
            'type' => 'required|in:business,land',
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:3000',
            'goal_q' => 'required|integer|min:' . config('investment.min_goal_q') . '|max:' . config('investment.max_goal_q'),
            'max_invest_q' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:' . config('investment.min_duration_days') . '|max:' . config('investment.max_duration_days'),
            'location_state' => 'required|string|max:100',
            'location_address' => 'required|string|max:255',
            'cac_number' => 'required|string|max:40',
            'use_of_funds' => 'required|string|max:2000',
            'risk_disclosure' => 'required|string|max:2000',
        ]);

        try {
            $project = $svc->createProject(Auth::user(), $data);
            app(\App\Services\InvestorGate::class)->saveDiligence($project, $request);
        } catch (RuntimeException $e) {
            return back()->withErrors(['project' => $e->getMessage()])->withInput();
        }

        return redirect()->route('investment.show', $project)->with('status', 'Submitted. An admin will review it.');
    }

    public function show(InvestmentProject $project, InvestmentService $svc)
    {
        $user = Auth::user();

        abort_if(
            $project->status === 'pending_review' && (int) $project->owner_id !== (int) $user->id && ! $user->isAdmin(),
            404
        );

        $mine = Investment::where('user_id', $user->id)
            ->where('investment_project_id', $project->id)
            ->latest()
            ->get();

        $myActive = (int) $mine->where('status', 'active')->sum('amount_q');
        $isOwner = (int) $project->owner_id === (int) $user->id;
        $limit = min($svc->capFor($user), $project->max_invest_q);
        $maxAllowed = max(0, min($limit - $myActive, $project->remainingGoal()));

        return view('investment.show', [
            'project' => $project,
            'mine' => $mine,
            'isOwner' => $isOwner,
            'canInvest' => $project->isOpen() && ! $isOwner && $maxAllowed > 0,
            'qBalance' => (int) floor((float) $user->wallet_balance),
            'myActive' => $myActive,
            'maxAllowed' => $maxAllowed,
            'idemKey' => (string) Str::uuid(),
        ]);
    }

    public function invest(Request $request, InvestmentProject $project, InvestmentService $svc)
    {
        $data = $request->validate([
            'amount_q' => 'required|integer|min:1',
            'idempotency_key' => 'required|string|max:64',
            'risk_ack' => 'accepted',
            'pin' => 'required|string|max:12',
        ]);

        try {
            app(\App\Services\InvestorGate::class)->invest(Auth::user(), $project, (int) $data['amount_q'], $data['idempotency_key'], ['accepted' => true, 'ip' => $request->ip()], (string) $data['pin']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['amount_q' => $e->getMessage()])->withInput();
        }

        return redirect()->route('investment.show', $project)->with('status', 'Investment recorded.');
    }

    public function submitVerification(Request $request, InvestmentService $svc)
    {
        $data = $request->validate([
            'details' => 'required|string|min:20|max:2000',
        ]);

        try {
            $svc->submitVerification(Auth::user(), $data['details']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Verification request sent. An admin will review it.');
    }
}

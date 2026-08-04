<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class PlanGateController extends Controller
{
    public function choose(Request $request)
    {
        return view('auth.plan-gate', [
            'plans' => Subscription::PLANS,
            'intendedUrl' => url()->previous(),
        ]);
    }
}

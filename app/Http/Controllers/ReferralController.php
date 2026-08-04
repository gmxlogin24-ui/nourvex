<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $referrals = User::where('referred_by', $user->id)->latest()->get();

        return view('referrals.index', [
            'code' => $user->referral_code,
            'referrals' => $referrals,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    public function complete()
    {
        Auth::user()->update(['has_seen_tour' => true]);
        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class OverviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $id = $user->id;

        if (!$user->has_seen_tour) {
            return redirect()->route('welcome.show');
        }

        $from = now()->subDays(6)->startOfDay();
        $rows = Transaction::where('user_id', $id)->where('created_at', '>=', $from)->get(['amount', 'created_at']);

        $flow = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $flow[] = round((float) $rows->filter(fn ($r) => $r->created_at->toDateString() === $key)->sum('amount'), 2);
            $labels[] = substr($day->format('D'), 0, 1);
        }

        $parts = explode(' ', trim((string) $user->name));

        return view('overview', [
            'firstName' => $parts[0] !== '' ? $parts[0] : 'there',
            'balance' => (float) $user->wallet_balance,
            'weekIn' => (float) $rows->where('amount', '>', 0)->sum('amount'),
            'weekOut' => abs((float) $rows->where('amount', '<', 0)->sum('amount')),
            'flow' => $flow,
            'labels' => $labels,
            'gigCount' => Gig::where('status', 'open')->count(),
            'listingCount' => Listing::where('status', 'available')->count(),
        ]);
    }
}

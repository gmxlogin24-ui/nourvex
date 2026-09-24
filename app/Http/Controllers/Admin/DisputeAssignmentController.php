<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flag;
use App\Models\GigDispute;
use App\Models\ListingDispute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeAssignmentController extends Controller
{
    private const MODELS = [
        'gig' => GigDispute::class,
        'listing' => ListingDispute::class,
        'flag' => Flag::class,
    ];

    public function assign(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'type' => 'required|in:gig,listing,flag',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'agent_id' => 'required|exists:users,id',
        ]);

        $agent = User::findOrFail($validated['agent_id']);
        abort_unless($agent->isCustomerCare(), 422, 'Selected user is not a customer care agent.');

        $modelClass = self::MODELS[$validated['type']];

        $modelClass::whereIn('id', $validated['ids'])->update([
            'assigned_to' => $agent->id,
            'assigned_at' => now(),
        ]);

        $count = count($validated['ids']);
        return back()->with('status', $count . ' item(s) assigned to ' . $agent->name . '.');
    }
}

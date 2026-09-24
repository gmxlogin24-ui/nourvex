<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCarePermissionController extends Controller
{
    private const VALID_KEYS = ['orders', 'gig_disputes', 'listing_disputes', 'user_lookup', 'flags'];

    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($user->isCustomerCare(), 422, 'This user is not a customer care agent.');

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', self::VALID_KEYS),
        ]);

        $user->update(['customer_care_permissions' => $validated['permissions'] ?? []]);

        return back()->with('status', 'Permissions updated for ' . $user->name . '.');
    }
}

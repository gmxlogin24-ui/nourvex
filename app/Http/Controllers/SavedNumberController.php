<?php

namespace App\Http\Controllers;

use App\Models\SavedNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedNumberController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:phone,smart_card',
            'provider' => 'nullable|string',
            'number' => 'required|string|max:20',
            'label' => 'nullable|string|max:50',
        ]);

        SavedNumber::updateOrCreate(
            ['user_id' => Auth::id(), 'type' => $validated['type'], 'number' => $validated['number']],
            ['provider' => $validated['provider'] ?? null, 'label' => $validated['label'] ?? null]
        );

        return back()->with('status', 'Number saved.');
    }

    public function destroy(SavedNumber $savedNumber)
    {
        abort_if($savedNumber->user_id !== Auth::id(), 403);
        $savedNumber->delete();
        return back()->with('status', 'Number removed.');
    }
}

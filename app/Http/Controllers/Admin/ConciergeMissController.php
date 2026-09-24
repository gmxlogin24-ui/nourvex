<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConciergeMiss;

class ConciergeMissController extends Controller
{
    public function index()
    {
        $misses = ConciergeMiss::where('resolved', false)
            ->orderByDesc('occurrences')
            ->orderByDesc('updated_at')
            ->paginate(30);

        return view('admin.concierge-misses', ['misses' => $misses]);
    }

    public function resolve(ConciergeMiss $miss)
    {
        $miss->update(['resolved' => true]);
        return back()->with('status', 'Marked as handled.');
    }
}

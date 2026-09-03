<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeatureGateController extends Controller
{
    public function index()
    {
        $gates = FeatureGate::orderBy('required_tier')->orderBy('feature_key')->get();
        return view('admin.feature-gates.index', ['gates' => $gates]);
    }

    public function toggle(FeatureGate $featureGate)
    {
        $featureGate->update(['enabled' => !$featureGate->enabled]);
        Cache::forget("feature_gate:{$featureGate->feature_key}");

        return back()->with('status', $featureGate->label . ' is now ' . ($featureGate->enabled ? 'enabled' : 'disabled') . '.');
    }

    public function update(Request $request, FeatureGate $featureGate)
    {
        $validated = $request->validate([
            'required_tier' => 'required|in:free,tier1,tier2,tier3',
        ]);

        $featureGate->update($validated);
        Cache::forget("feature_gate:{$featureGate->feature_key}");

        return back()->with('status', $featureGate->label . ' updated.');
    }
}

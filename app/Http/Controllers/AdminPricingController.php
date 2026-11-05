<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Subscription;
use App\Support\Pricing;
use Illuminate\Http\Request;

class AdminPricingController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    }

    public function index()
    {
        $this->guard();
        return view('admin.finance.pricing', [
            'ads' => Pricing::adPrices(),
            'wfee' => Pricing::withdrawalFeePercent(),
            'tiers' => Subscription::PLANS,
        ]);
    }

    public function save(Request $r)
    {
        $this->guard();
        $v = $r->validate([
            'ad_3' => 'required|integer|min:0|max:100000000',
            'ad_7' => 'required|integer|min:0|max:100000000',
            'ad_14' => 'required|integer|min:0|max:100000000',
            'withdrawal_fee_percent' => 'required|numeric|min:0|max:10',
        ]);
        AppSetting::set('pricing.ad_prices', json_encode([3 => (int) $v['ad_3'], 7 => (int) $v['ad_7'], 14 => (int) $v['ad_14']]));
        AppSetting::set('pricing.withdrawal_fee_percent', $v['withdrawal_fee_percent']);
        return back()->with('status', 'Prices saved. They apply to new purchases straight away.');
    }
}

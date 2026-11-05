<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class InvestmentExtrasController extends Controller
{
    public function audit()
    {
        $rows = DB::table('investment_audit')
            ->leftJoin('users', 'users.id', '=', 'investment_audit.admin_id')
            ->orderByDesc('investment_audit.id')
            ->limit(300)
            ->select('investment_audit.*', 'users.name as admin_name')
            ->get();

        return view('admin.investment.audit', ['rows' => $rows]);
    }
}

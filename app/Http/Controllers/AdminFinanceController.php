<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Withdrawal;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminFinanceController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    }

    public function profit(Request $r, FinanceService $fin)
    {
        $this->guard();
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $r->query('month')) ? $r->query('month') : now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $expenses = Schema::hasTable('expenses')
            ? DB::table('expenses')->whereBetween('incurred_on', [$start->toDateString(), $end->toDateString()])->orderByDesc('incurred_on')->get()
            : collect();

        $values = [];
        foreach (FinanceService::PAYSTACK_FIELDS as $key => $f) {
            $values[$key] = $fin->setting($key);
        }

        return view('admin.finance.profit', [
            'month' => $month,
            's' => $fin->summary($start, $end),
            'types' => $fin->transactionTypes($start, $end),
            'expenses' => $expenses,
            'hasExpenses' => Schema::hasTable('expenses'),
            'categories' => FinanceService::EXPENSE_CATEGORIES,
            'fields' => FinanceService::PAYSTACK_FIELDS,
            'values' => $values,
            'poolPercent' => $fin->percent(),
            'dvaTypes' => implode(',', $fin->dvaTypes()),
            'dvaDescription' => $fin->dvaDescription(),
        ]);
    }

    public function saveSettings(Request $r)
    {
        $this->guard();
        $rules = ['pool_percent' => 'required|numeric|min:0|max:100', 'dva_types' => 'nullable|string|max:500', 'dva_description' => 'nullable|string|max:255'];
        foreach (FinanceService::PAYSTACK_FIELDS as $key => $f) {
            $rules['f_' . str_replace('.', '_', $key)] = 'required|numeric|min:0|max:10000000';
        }
        $v = $r->validate($rules);
        AppSetting::set('profit.pool_percent', $v['pool_percent']);
        foreach (FinanceService::PAYSTACK_FIELDS as $key => $f) {
            AppSetting::set($key, $v['f_' . str_replace('.', '_', $key)]);
        }
        AppSetting::set('paystack.dva_types', trim((string) ($v['dva_types'] ?? '')));
        AppSetting::set('paystack.dva_description', trim((string) ($v['dva_description'] ?? '')));
        return back()->with('status', 'Finance settings saved.');
    }

    public function storeExpense(Request $r)
    {
        $this->guard();
        abort_unless(Schema::hasTable('expenses'), 404);
        $v = $r->validate([
            'category' => 'required|in:' . implode(',', array_keys(FinanceService::EXPENSE_CATEGORIES)),
            'amount' => 'required|numeric|between:-10000000,100000000',
            'incurred_on' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);
        DB::table('expenses')->insert($v + ['created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('status', 'Cost recorded.');
    }

    public function deleteExpense(int $id)
    {
        $this->guard();
        abort_unless(Schema::hasTable('expenses'), 404);
        DB::table('expenses')->where('id', $id)->delete();
        return back()->with('status', 'Cost removed.');
    }

    public function withdrawals(Request $r)
    {
        $this->guard();
        $q = Withdrawal::with('user')->latest();
        if ($r->filled('status')) {
            $q->where('status', $r->query('status'));
        }
        if ($r->filled('search')) {
            $s = $r->query('search');
            $q->where(fn ($w) => $w->where('tx_ref', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
        }
        return view('admin.finance.withdrawals', [
            'withdrawals' => $q->paginate(30)->withQueryString(),
            'totals' => DB::table('withdrawals')->selectRaw('status, COUNT(*) as c, SUM(amount) as a, SUM(fee) as f')->groupBy('status')->get(),
        ]);
    }
}

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSecurityController extends Controller
{
    public function confirm() { return view('admin.confirm'); }

    public function confirmPost(Request $r)
    {
        $r->validate(['password' => 'required|string']);
        if (!Hash::check($r->input('password'), Auth::user()->password)) {
            return back()->withErrors(['password' => 'Wrong password']);
        }
        $r->session()->put('adm_reauth', time());
        $to = $r->session()->pull('adm_back', '/admin');
        return redirect($to ?: '/admin');
    }

    public function audit()
    {
        $rows = DB::table('admin_audit_logs as a')->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.*', 'u.name', 'u.username')->orderByDesc('a.id')->limit(300)->get();
        return view('admin.audit', ['rows' => $rows]);
    }
}

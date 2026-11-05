<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminGuard
{
    const IDLE = 900;
    const REAUTH = 600;
    const RISKY = [
        'admin/users/*/adjust-balance', 'admin/users/*/toggle-admin', 'admin/users/*/toggle-ban',
        'admin/users/*/toggle-customer-care', 'admin/users/*/cc-permissions',
        'admin/disputes/*/pay', 'admin/disputes/*/partial',
        'admin/marketplace-disputes/*/pay', 'admin/marketplace-disputes/*/refund',
        'admin/investment/projects/*/release', 'admin/investment/projects/*/distribute', 'admin/investment/projects/*/returns',
        'admin/investment/verifications/*', 'admin/investment/projects/*/cancel', 'admin/investment/milestones/*/release', 'admin/kyc/*/decision', 'admin/investment/projects/*/approve', 'admin/grant-access',
    ];

    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u || !$u->is_admin || !$request->is('admin', 'admin/*')) return $next($request);

        $now = time();
        $last = (int) $request->session()->get('adm_last', 0);
        if ($last && $now - $last > self::IDLE) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login');
        }
        $request->session()->put('adm_last', $now);

        $write = !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
        if ($write && !$request->is('admin/confirm')) {
            $risky = false;
            foreach (self::RISKY as $p) { if ($request->is($p)) { $risky = true; break; } }
            if ($risky && $now - (int) $request->session()->get('adm_reauth', 0) > self::REAUTH) {
                $request->session()->put('adm_back', url()->previous());
                return redirect('/admin/confirm')->with('warn', 'Confirm your password, then repeat the action.');
            }
        }

        $res = $next($request);

        if ($write) {
            try {
                $p = $request->except(['_token', '_method', 'password', 'current_password', 'password_confirmation']);
                DB::table('admin_audit_logs')->insert([
                    'user_id' => $u->id, 'method' => $request->method(), 'path' => substr($request->path(), 0, 250),
                    'ip' => $request->ip(), 'agent' => substr((string) $request->userAgent(), 0, 250),
                    'payload' => substr(json_encode($p), 0, 1500), 'status' => method_exists($res, 'getStatusCode') ? $res->getStatusCode() : 0,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}
        }
        return $res;
    }
}

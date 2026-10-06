<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function sendMoneyForm()
    {
        return view('wallet.send', [
            'hasPin' => Auth::user()->hasTransactionPin(),
        ]);
    }

    // AJAX: show the sender the recipient's real name before they commit to
    // sending. Display-only - sendMoney() below re-looks-up the recipient
    // itself before crediting anything, so this can't be tampered with
    // client-side, matching BankAccountController::resolve()'s pattern.
    public function resolveRecipient(Request $request)
    {
        $validated = $request->validate([
            'recipient_identifier' => 'required|string',
        ]);

        $sender = Auth::user();
        $recipient = User::where('email', $validated['recipient_identifier'])
            ->orWhere('id', $validated['recipient_identifier'])
            ->first();

        if (!$recipient) {
            return response()->json(['error' => 'No user found with that email or ID.'], 422);
        }
        if ($recipient->id === $sender->id) {
            return response()->json(['error' => 'You cannot send money to yourself.'], 422);
        }

        return response()->json(['name' => $recipient->name]);
    }

    // Locked transaction, mirrors the pattern used everywhere else in this app
    // (GigController deposit, NewsController::storeAd, ServicesController::processPurchase)
    // to prevent race conditions on concurrent transfer requests.
    public function sendMoney(Request $request)
    {
        $sender = Auth::user();
        $validated = $request->validate([
            'recipient_identifier' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
            'pin' => 'required|string',
        ]);

        if (!$sender->hasTransactionPin()) {
            return back()->withErrors(['pin' => 'Set a transaction PIN before sending money.'])->withInput();
        }
        if (!$sender->verifyTransactionPin($validated['pin'])) {
            return back()->withErrors(['pin' => 'Incorrect transaction PIN.'])->withInput();
        }

        $recipient = User::where('email', $validated['recipient_identifier'])
            ->orWhere('id', $validated['recipient_identifier'])
            ->first();

        if (!$recipient) {
            return back()->withErrors(['recipient_identifier' => 'No user found with that email or ID.'])->withInput();
        }

        if ($recipient->id === $sender->id) {
            return back()->withErrors(['recipient_identifier' => 'You cannot send money to yourself.'])->withInput();
        }

        $result = DB::transaction(function () use ($sender, $recipient, $validated) {
            $lockedSender = User::where('id', $sender->id)->lockForUpdate()->first();

            if ($lockedSender->wallet_restricted) {
                return ['status' => 'restricted'];
            }
            if ($lockedSender->wallet_balance < $validated['amount']) {
                return ['status' => 'insufficient'];
            }

            $lockedRecipient = User::where('id', $recipient->id)->lockForUpdate()->first();

            if ($lockedRecipient->wouldExceedNoBvnCap($validated['amount'])) {
                return ['status' => 'recipient_capped'];
            }

            $lockedSender->decrement('wallet_balance', $validated['amount']);
            $lockedRecipient->increment('wallet_balance', $validated['amount']);

            $note = $validated['note'] ?? '';

            // Internal wallet-to-wallet transfer - no bank/Paystack is involved,
            // so NOURVEX generates its own reference, shared by both legs of
            // the transfer (same convention banks use between debit/credit legs).
            $reference = 'NRX' . now()->format('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

            $senderTxn = Transaction::create([
                'user_id' => $lockedSender->id,
                'related_user_id' => $lockedRecipient->id,
                'type' => 'transfer_sent',
                'amount' => -$validated['amount'],
                'description' => 'Sent to ' . $lockedRecipient->name . ($note ? ': ' . $note : ''),
                'reference' => $reference,
                'status' => 'successful',
            ]);
            Transaction::create([
                'user_id' => $lockedRecipient->id,
                'related_user_id' => $lockedSender->id,
                'type' => 'transfer_received',
                'amount' => $validated['amount'],
                'description' => 'Received from ' . $lockedSender->name . ($note ? ': ' . $note : ''),
                'reference' => $reference,
                'status' => 'successful',
            ]);
            $lockedRecipient->notify(new \App\Notifications\MoneyReceived($lockedSender, $validated['amount'], $note ?: null));

            return ['status' => 'ok', 'txn_id' => $senderTxn->id];
        });

        return match ($result['status']) {
            'restricted' => back()->withErrors(['amount' => 'Your wallet is restricted due to an unresolved payment issue.'])->withInput(),
            'insufficient' => back()->withErrors(['amount' => 'Insufficient wallet balance.'])->withInput(),
            'recipient_capped' => back()->withErrors(['recipient_identifier' => 'This recipient has an unverified wallet and cannot receive that much right now.'])->withInput(),
            default => redirect()->route('wallet.transaction.show', $result['txn_id']),
        };
    }

    public function showTransaction($id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        return view('wallet.receipt', ['tx' => $transaction]);
    }

    public function index()
    {
        $user = Auth::user();

        return view('wallet.index', [
            'balance' => $user->wallet_balance,
            'transactions' => Transaction::where('user_id', $user->id)->latest()->get(),
            'hasPin' => $user->hasTransactionPin(),
            'hasBankAccount' => (bool) $user->payout_account_number,
            'isVerified' => (bool) $user->virtual_account_number,
            'walletCapRemaining' => $user->virtual_account_number ? null : max(0, \App\Models\User::NO_BVN_WALLET_CAP - $user->wallet_balance),
        ]);
    }

    public function moneyTracker()
    {
        $user = Auth::user();

        abort_unless(in_array($user->tier, ['tier2', 'tier3'], true), 403, 'Money Tracker is available on the Gold plan and above. Upgrade to unlock detailed cash flow tracking.');

        $totalIn = Transaction::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount');
        $totalOut = abs(Transaction::where('user_id', $user->id)->where('amount', '<', 0)->sum('amount'));

        $categoryTotals = Transaction::where('user_id', $user->id)
            ->selectRaw('type, SUM(ABS(amount)) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $driver = \DB::connection()->getDriverName();
        $ymExpr = match ($driver) {
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')", // mysql/mariadb
        };

        $monthly = Transaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("$ymExpr as ym, SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as inflow, SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as outflow")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return view('money-tracker.index', [
            'balance' => $user->wallet_balance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'categoryTotals' => $categoryTotals,
            'monthly' => $monthly,
            'transactions' => Transaction::where('user_id', $user->id)->latest()->take(30)->get(),
        ]);
    }
}

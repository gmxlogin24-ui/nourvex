<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTopup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WalletTopupFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeVerify(string $txRef, float $amount = 1000, string $status = 'successful'): void
    {
        Http::fake([
            'api.flutterwave.com/v3/transactions/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => $status,
                    'tx_ref' => $txRef,
                    'amount' => $amount,
                    'currency' => 'NGN',
                ],
            ]),
        ]);
    }

    protected function pendingTopup(User $user, float $amount = 1000): WalletTopup
    {
        return WalletTopup::create([
            'user_id' => $user->id,
            'tx_ref' => 'QELVO-' . $user->id . '-' . time() . random_int(1, 9999),
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /** The normal happy path: browser redirects back, verify() confirms with Flutterwave, wallet is credited. */
    public function test_callback_credits_wallet_when_verification_succeeds(): void
    {
        Notification::fake();
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);
        $this->fakeVerify($topup->tx_ref, 1000);

        $response = $this->actingAs($user)->get(
            route('flutterwave.callback') . '?tx_ref=' . $topup->tx_ref . '&status=successful&transaction_id=999'
        );

        $response->assertRedirect(route('wallet.index'));
        $this->assertEquals(1000, $user->fresh()->wallet_balance);
        $this->assertEquals('successful', $topup->fresh()->status);
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'type' => 'wallet_topup', 'amount' => 1000]);
        Notification::assertSentTo($user, \App\Notifications\WalletTopupCredited::class);
    }

    /** Flutterwave's own verify endpoint disagrees with the redirect status - must NOT credit. This is the case that stops a spoofed/forged redirect from crediting a wallet. */
    public function test_callback_does_not_credit_when_server_side_verification_fails(): void
    {
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);
        // Redirect claims success, but Flutterwave's own verify says otherwise
        $this->fakeVerify($topup->tx_ref, 1000, 'failed');

        $this->actingAs($user)->get(
            route('flutterwave.callback') . '?tx_ref=' . $topup->tx_ref . '&status=successful&transaction_id=999'
        );

        $this->assertEquals(0, $user->fresh()->wallet_balance);
        $this->assertEquals('failed', $topup->fresh()->status);
    }

    /** Verified amount is less than what was requested (partial/short payment) - must NOT credit. */
    public function test_callback_does_not_credit_when_amount_paid_is_short(): void
    {
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);
        $this->fakeVerify($topup->tx_ref, 500, 'successful'); // paid less than requested

        $this->actingAs($user)->get(
            route('flutterwave.callback') . '?tx_ref=' . $topup->tx_ref . '&status=successful&transaction_id=999'
        );

        $this->assertEquals(0, $user->fresh()->wallet_balance);
    }

    /** This is YOUR specific fear: browser never returns (closed tab, dropped connection) - callback() never runs at all. The webhook, fired independently by Flutterwave's own servers, must still credit the wallet on its own. */
    public function test_webhook_credits_wallet_when_browser_callback_never_fires(): void
    {
        Notification::fake();
        config(['services.flutterwave.webhook_secret' => 'test-secret']);
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);
        // Note: no call to callback() at all - simulating the browser never coming back

        $response = $this->withHeaders(['verif-hash' => 'test-secret'])
            ->postJson('/webhooks/flutterwave', [
                'event' => 'charge.completed',
                'data' => [
                    'id' => 55555,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 1000,
                    'tx_ref' => $topup->tx_ref,
                    'customer' => ['email' => $user->email],
                ],
            ]);

        $response->assertOk();
        $this->assertEquals(1000, $user->fresh()->wallet_balance);
        $this->assertEquals('successful', $topup->fresh()->status);
        Notification::assertSentTo($user, \App\Notifications\WalletTopupCredited::class);
    }

    /** Flutterwave retries webhook delivery (their standard behavior if your server is slow to respond) - the SECOND delivery of the same event must NOT double-credit. */
    public function test_duplicate_webhook_delivery_does_not_double_credit(): void
    {
        config(['services.flutterwave.webhook_secret' => 'test-secret']);
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);

        $payload = [
            'event' => 'charge.completed',
            'data' => [
                'id' => 77777,
                'status' => 'successful',
                'currency' => 'NGN',
                'amount' => 1000,
                'tx_ref' => $topup->tx_ref,
                'customer' => ['email' => $user->email],
            ],
        ];

        $this->withHeaders(['verif-hash' => 'test-secret'])->postJson('/webhooks/flutterwave', $payload);
        $this->withHeaders(['verif-hash' => 'test-secret'])->postJson('/webhooks/flutterwave', $payload); // exact same delivery again

        $this->assertEquals(1000, $user->fresh()->wallet_balance); // NOT 2000
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->where('type', 'wallet_topup')->count());
    }

    /** Both paths fire for the same payment (browser DOES return, AND webhook also fires) - only one may credit, matching the "whichever lands first wins" design. */
    public function test_callback_and_webhook_both_firing_only_credits_once(): void
    {
        Notification::fake();
        config(['services.flutterwave.webhook_secret' => 'test-secret']);
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);
        $this->fakeVerify($topup->tx_ref, 1000);

        // Browser returns first
        $this->actingAs($user)->get(
            route('flutterwave.callback') . '?tx_ref=' . $topup->tx_ref . '&status=successful&transaction_id=999'
        );

        // Webhook also fires for the same payment shortly after
        $this->withHeaders(['verif-hash' => 'test-secret'])->postJson('/webhooks/flutterwave', [
            'event' => 'charge.completed',
            'data' => [
                'id' => 88888,
                'status' => 'successful',
                'currency' => 'NGN',
                'amount' => 1000,
                'tx_ref' => $topup->tx_ref,
                'customer' => ['email' => $user->email],
            ],
        ]);

        $this->assertEquals(1000, $user->fresh()->wallet_balance); // NOT 2000
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->where('type', 'wallet_topup')->count());
    }

    /** A webhook request with a missing/wrong signature must be rejected outright - proves a stranger can't POST a fake "payment successful" straight at your webhook URL to credit themselves for free. */
    public function test_webhook_rejects_request_with_wrong_signature(): void
    {
        config(['services.flutterwave.webhook_secret' => 'test-secret']);
        $user = User::factory()->create(['wallet_balance' => 0]);
        $topup = $this->pendingTopup($user, 1000);

        $response = $this->withHeaders(['verif-hash' => 'wrong-guess'])
            ->postJson('/webhooks/flutterwave', [
                'event' => 'charge.completed',
                'data' => [
                    'id' => 99999, 'status' => 'successful', 'currency' => 'NGN',
                    'amount' => 1000, 'tx_ref' => $topup->tx_ref,
                    'customer' => ['email' => $user->email],
                ],
            ]);

        $response->assertStatus(401);
        $this->assertEquals(0, $user->fresh()->wallet_balance);
    }
}

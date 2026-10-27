<?php

namespace Tests\Feature;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NaurvexFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeFlutterwaveSuccess(string $txRef): void
    {
        Http::fake([
            'api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => ['link' => 'https://checkout.flutterwave.com/fake'],
            ]),
            'api.flutterwave.com/v3/transactions/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'tx_ref' => $txRef,
                    'amount' => 1000,
                    'currency' => 'NGN',
                ],
            ]),
        ]);
    }

    /** New account is blocked from the dashboard until the ₦1,000 fee is paid, then let through after. */
    public function test_registration_gates_behind_signup_fee(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password123',
            'gender' => 'other',
            'spoken_language' => 'English',
        ]);

        $response->assertRedirect(route('signup-fee.activate'));
        $user = User::where('email', 'student@test.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->signup_fee_paid);

        // Trying to jump straight to the dashboard should bounce back to the payment page
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('signup-fee.activate'));

        // Simulate paying
        $this->fakeFlutterwaveSuccess("SIGNUPFEE-{$user->id}-fake");
        $this->actingAs($user)->get('/signup-fee'); // kicks off WalletTopup row with the real tx_ref format
        $topup = \App\Models\WalletTopup::where('user_id', $user->id)->latest()->first();
        $this->fakeFlutterwaveSuccess($topup->tx_ref);

        $callback = $this->actingAs($user)->get('/signup-fee/callback?tx_ref=' . $topup->tx_ref . '&status=successful&transaction_id=12345');
        $callback->assertRedirect(route('onboarding.interests'));

        $user->refresh();
        $this->assertTrue($user->signup_fee_paid);
        $this->assertEquals('bronze', $user->badge_tier);

        // Now the dashboard should actually load
        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    /** Guest can claim 2 gigs with just a name; the 3rd is blocked and points at registration. */
    public function test_guest_gets_exactly_two_free_gigs(): void
    {
        $poster = User::factory()->create(['signup_fee_paid' => true, 'wallet_balance' => 100000]);
        $gigs = collect(range(1, 3))->map(fn($i) => Gig::create([
            'title' => "Gig $i", 'category' => 'General', 'amount' => 1000,
            'user_id' => $poster->id, 'status' => 'open',
        ]));

        $client = $this->withUnencryptedCookie('campulsy_guest_token', 'test-guest-token-123');

        $r1 = $client->post("/gigs/{$gigs[0]->id}/accept", ['guest_name' => 'Guest One']);
        $r1->assertRedirect(route('gigs.show', $gigs[0]->id));
        $this->assertEquals('in_progress', $gigs[0]->fresh()->status);

        $r2 = $client->post("/gigs/{$gigs[1]->id}/accept", ['guest_name' => 'Guest One']);
        $this->assertEquals('in_progress', $gigs[1]->fresh()->status);

        $r3 = $client->post("/gigs/{$gigs[2]->id}/accept", ['guest_name' => 'Guest One']);
        $r3->assertRedirect(route('register'));
        $this->assertEquals('open', $gigs[2]->fresh()->status); // NOT claimed - cap held
    }

    /** Completed guest gig holds payout until they register + pay; paying releases it. */
    public function test_guest_gig_payout_is_held_then_released_on_signup(): void
    {
        $poster = User::factory()->create(['signup_fee_paid' => true, 'wallet_balance' => 100000]);
        $gig = Gig::create([
            'title' => 'Test Gig', 'category' => 'General', 'amount' => 5000,
            'user_id' => $poster->id, 'status' => 'in_progress',
            'guest_name' => 'Guest Worker', 'guest_token' => 'guest-payout-token',
        ]);

        $client = $this->withUnencryptedCookie('campulsy_guest_token', 'guest-payout-token');
        $client->post("/gigs/{$gig->id}/deliver", ['delivery_note' => 'Done']);
        $this->assertEquals('delivered', $gig->fresh()->status);

        $this->actingAs($poster)->post(route('gigs.complete', $gig->id));
        $this->assertEquals('awaiting_worker_signup', $gig->fresh()->status);

        // Now the guest registers and pays
        $newUser = User::factory()->create(['signup_fee_paid' => false]);
        $newUser->update(['signup_fee_paid' => true]); // simulate the payment callback's effect directly
        app(\App\Http\Controllers\GigController::class)->settleGuestGigs($newUser, 'guest-payout-token');

        $this->assertEquals('completed', $gig->fresh()->status);
        $this->assertEquals(4750, $newUser->fresh()->wallet_balance); // 5000 minus 5% base-tier commission
    }

    /** Base-tier user can post up to 4 open gigs, blocked on the 5th. */
    public function test_gig_post_cap_by_tier(): void
    {
        $user = User::factory()->create(['signup_fee_paid' => true, 'tier' => 'free', 'wallet_balance' => 100000]);

        for ($i = 1; $i <= 4; $i++) {
            $this->actingAs($user)->post('/gigs', [
                'title' => "Gig $i", 'category' => 'General', 'amount' => 1000,
            ])->assertRedirect();
        }
        $this->assertEquals(4, Gig::where('user_id', $user->id)->count());

        $blocked = $this->actingAs($user)->post('/gigs', [
            'title' => 'Gig 5', 'category' => 'General', 'amount' => 1000,
        ]);
        $blocked->assertRedirect(route('subscriptions.index'));
        $this->assertEquals(4, Gig::where('user_id', $user->id)->count());
    }

    /** Base-tier user can post up to 5 marketplace listings per week, blocked on the 6th. */
    public function test_marketplace_weekly_cap_by_tier(): void
    {
        $user = User::factory()->create(['signup_fee_paid' => true, 'tier' => 'free']);

        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($user)->post('/marketplace', [
                'title' => "Item $i", 'category' => 'Books', 'listing_type' => 'item',
                'condition' => 'New', 'location' => 'Campus', 'amount' => 500,
            ]);
        }
        $this->assertEquals(5, Listing::where('user_id', $user->id)->count());

        $blocked = $this->actingAs($user)->post('/marketplace', [
            'title' => 'Item 6', 'category' => 'Books', 'listing_type' => 'item',
            'condition' => 'New', 'location' => 'Campus', 'amount' => 500,
        ]);
        $blocked->assertRedirect(route('subscriptions.index'));
        $this->assertEquals(5, Listing::where('user_id', $user->id)->count());
    }

    /** Base tier cannot post ads at all; tier1 can and it's live on the feed. */
    public function test_ad_posting_gated_by_tier(): void
    {
        $freeUser = User::factory()->create(['signup_fee_paid' => true, 'tier' => 'free']);
        $this->actingAs($freeUser)->get('/news/ad/create')->assertForbidden();

        $paidUser = User::factory()->create(['signup_fee_paid' => true, 'tier' => 'tier1', 'wallet_balance' => 10000]);
        $response = $this->actingAs($paidUser)->post('/news/ad', [
            'title' => 'Test Ad', 'category' => 'Promo', 'body' => 'Buy my stuff',
            'duration' => 7,
        ]);
        $response->assertRedirect(route('news.index'));
        $this->assertEquals(7000, $paidUser->fresh()->wallet_balance); // 10000 - 3000 for 7 days
        $this->assertDatabaseHas('news', ['title' => 'Test Ad', 'type' => 'ad']);
    }
}

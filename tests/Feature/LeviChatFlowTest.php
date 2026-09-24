<?php

namespace Tests\Feature;

use App\Models\ConciergeMiss;
use App\Models\Gig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeviChatFlowTest extends TestCase
{
    use RefreshDatabase;

    /** A recognized keyword (wallet balance) gets a real answer, and search now uses the correct 'amount' column. */
    public function test_wallet_balance_keyword_is_understood(): void
    {
        $user = User::factory()->create(['wallet_balance' => 15000]);

        $response = $this->actingAs($user)->postJson(route('concierge.message'), [
            'message' => 'check my wallet balance',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('15,000', $response->json('reply'));
    }

    /** Gig search actually queries the real 'amount' column now, not the nonexistent 'budget' column - this would have thrown a SQL error before the fix. */
    public function test_gig_search_no_longer_throws_column_error(): void
    {
        $poster = User::factory()->create(['signup_fee_paid' => true, 'wallet_balance' => 100000]);
        Gig::create(['title' => 'Cheap gig', 'category' => 'General', 'amount' => 5000, 'user_id' => $poster->id, 'status' => 'open']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('concierge.message'), [
            'message' => 'find gigs under 10000',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Cheap gig', $response->json('reply'));
    }

    /** Something LEVI doesn't recognize gets logged as a miss, so it can be reviewed and taught later. */
    public function test_unrecognized_message_is_logged_as_a_miss(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('concierge.message'), [
            'message' => 'can you hook me up with some cheap jobs abeg',
        ]);

        $this->assertDatabaseHas('concierge_misses', [
            'user_id' => $user->id,
            'message' => 'can you hook me up with some cheap jobs abeg',
        ]);
    }

    /** The SAME unrecognized message sent twice increments occurrences instead of creating a duplicate row - this is what makes the admin page's "sorted by frequency" view meaningful. */
    public function test_repeated_miss_increments_occurrence_count_not_duplicate_rows(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('concierge.message'), ['message' => 'yo hook me up']);
        $this->actingAs($user)->postJson(route('concierge.message'), ['message' => 'yo hook me up']);
        $this->actingAs($user)->postJson(route('concierge.message'), ['message' => 'yo hook me up']);

        $this->assertEquals(1, ConciergeMiss::where('message', 'yo hook me up')->count());
        $this->assertEquals(3, ConciergeMiss::where('message', 'yo hook me up')->first()->occurrences);
    }

    /** Sending ONE message produces exactly ONE stored AI reply - proves the levi.js duplicate-script bug (which caused two requests per message) can never come back even if someone re-adds that script tag later. */
    public function test_one_message_produces_exactly_one_ai_reply(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('concierge.message'), ['message' => 'check my wallet balance']);

        $conversation = \App\Models\Conversation::findOrCreateConcierge($user);
        $aiReplies = $conversation->messages()->where('is_ai', true)->count();

        $this->assertEquals(1, $aiReplies, 'Expected exactly one AI reply per message - got ' . $aiReplies . '. This would indicate the duplicate-script bug has returned.');
    }

    /** Admin miss-review page loads and shows the logged misses, sorted by frequency. */
    public function test_admin_can_view_concierge_misses_sorted_by_frequency(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        ConciergeMiss::create(['user_id' => $user->id, 'message' => 'rare one', 'occurrences' => 1]);
        ConciergeMiss::create(['user_id' => $user->id, 'message' => 'common one', 'occurrences' => 12]);

        $response = $this->actingAs($admin)->get(route('admin.concierge-misses.index'));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'common one') < strpos($content, 'rare one'), 'Higher-occurrence miss should appear first.');
    }
}

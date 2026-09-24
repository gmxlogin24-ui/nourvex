<?php

namespace App\Services\Concierge;

use App\Models\ConciergeMiss;
use App\Models\User;

class KeywordConciergeService
{
    public function __construct(protected ConciergeToolRegistry $tools) {}

    /**
     * Each rule: an array of trigger words (ANY match counts), and a closure
     * that builds the tool call + a human reply from the matched text and
     * an optional captured number (e.g. a budget/price the user typed).
     */
    protected function rules(): array
    {
        return [
            [
                'words' => ['balance', 'wallet', 'how much do i have', 'how much money'],
                'handle' => function (User $user) {
                    $res = $this->tools->execute($user, 'get_wallet_balance', []);
                    $bal = $res['result']['balance_naira'] ?? 0;
                    return ['reply' => 'Your wallet balance is ₦' . number_format($bal) . '.', 'client_actions' => []];
                },
            ],
            [
                'words' => ['q coin balance', 'qcoin balance', 'my coins', 'coin balance'],
                'handle' => function (User $user) {
                    $res = $this->tools->execute($user, 'get_qcoin_balance', []);
                    $bal = $res['result']['balance_q'] ?? 0;
                    return ['reply' => 'You have ' . number_format($bal) . ' Q Coins.', 'client_actions' => []];
                },
            ],
            [
                'words' => ['claim', 'daily reward', 'q coin reward', 'claim coin'],
                'handle' => function (User $user) {
                    $res = $this->tools->execute($user, 'claim_daily_reward', []);
                    if ($res['result']['claimed'] ?? false) {
                        return ['reply' => 'Claimed! You got ' . $res['result']['amount'] . ' Q Coins. New balance: ' . $res['result']['new_balance'] . '.', 'client_actions' => []];
                    }
                    return ['reply' => $res['result']['reason'] ?? "You've already claimed today's reward.", 'client_actions' => []];
                },
            ],
            [
                'words' => ['how many gig', 'how many job', 'number of gig', 'number of job', 'count of gig'],
                'handle' => function (User $user) {
                    $count = \App\Models\Gig::where('status', 'open')->count();
                    return ['reply' => "There are currently {$count} open " . ($count === 1 ? 'gig' : 'gigs') . " listed.", 'client_actions' => []];
                },
            ],
            [
                'words' => ['gig', 'gigs', 'job', 'jobs', 'task', 'tasks'],
                'handle' => function (User $user, string $text, ?float $number) {
                    $input = [];
                    if ($number !== null) $input['max_budget'] = $number;
                    $res = $this->tools->execute($user, 'search_gigs', $input);
                    $gigs = $res['result']['gigs'] ?? [];
                    if (empty($gigs)) {
                        return ['reply' => "I couldn't find any gigs matching that right now. Want me to take you to the full gigs page instead?", 'client_actions' => []];
                    }
                    return [
                        'reply' => "Found " . count($gigs) . " open " . (count($gigs) === 1 ? 'gig' : 'gigs') . " — tap one to view it:",
                        'client_actions' => [],
                        'links' => collect($gigs)->map(fn($g) => ['title' => $g['title'], 'subtitle' => '₦' . number_format($g['budget']), 'url' => $g['url']])->values()->all(),
                    ];
                },
            ],
            [
                'words' => ['marketplace', 'buy', 'item', 'listing', 'listings', 'for sale'],
                'handle' => function (User $user, string $text, ?float $number) {
                    $input = [];
                    if ($number !== null) $input['max_price'] = $number;
                    $res = $this->tools->execute($user, 'search_marketplace', $input);
                    $listings = $res['result']['listings'] ?? [];
                    if (empty($listings)) {
                        return ['reply' => "Nothing matching that in the marketplace right now.", 'client_actions' => []];
                    }
                    $lines = collect($listings)->map(fn($l) => "• {$l['title']} — ₦" . number_format($l['price']))->implode("\n");
                    return ['reply' => "Found these:\n" . $lines, 'client_actions' => []];
                },
            ],
            [
                'words' => ['take me to my wallet', 'open wallet', 'go to wallet'],
                'handle' => fn(User $user) => $this->navigateReply($user, 'wallet'),
            ],
            [
                'words' => ['post a gig', 'create a gig', 'new gig'],
                'handle' => fn(User $user) => $this->navigateReply($user, 'gigs_create'),
            ],
            [
                'words' => ['sell something', 'post a listing', 'create a listing', 'new listing'],
                'handle' => fn(User $user) => $this->navigateReply($user, 'marketplace_create'),
            ],
        ];
    }

    protected function navigateReply(User $user, string $page): array
    {
        $res = $this->tools->execute($user, 'navigate', ['page' => $page]);
        return ['reply' => 'On it — taking you there.', 'client_actions' => [$res['client_action']]];
    }

    // Maps informal/alternate phrasings to the canonical word the rules already
    // match on. Expanding the message with these BEFORE matching means we don't
    // need to duplicate every rule's word list with every possible synonym -
    // add a new phrasing here once, and every existing rule benefits.
    protected function synonyms(): array
    {
        return [
            'gig' => ['hustle', 'side hustle', 'work', 'gig work', 'side job', 'side jobs', 'hook me up', 'any work'],
            'job' => ['hustle', 'work', 'employment', 'side job', 'side jobs', 'hook me up'],
            'wallet' => ['money', 'cash', 'funds', 'account balance'],
            'marketplace' => ['market', 'shop', 'store', 'stuff for sale', 'things to buy'],
            'claim' => ['redeem', 'collect', 'get my reward', 'get my coins'],
            'listing' => ['product', 'item for sale', 'sell my stuff'],
        ];
    }

    protected function expandWithSynonyms(string $lower): string
    {
        $expanded = $lower;
        foreach ($this->synonyms() as $canonical => $variants) {
            foreach ($variants as $variant) {
                if (str_contains($lower, $variant)) {
                    $expanded .= ' ' . $canonical;
                }
            }
        }
        return $expanded;
    }

    public function handleUserMessage(User $user, string $text): array
    {
        $lower = strtolower(trim($text));
        $lower = $this->expandWithSynonyms($lower);
        preg_match('/(\d[\d,]*)/', $lower, $m);
        $number = isset($m[1]) ? (float) str_replace(',', '', $m[1]) : null;

        foreach ($this->rules() as $rule) {
            foreach ($rule['words'] as $word) {
                if (str_contains($lower, $word)) {
                    return $rule['handle']($user, $lower, $number);
                }
            }
        }

        $this->logMiss($user, $text);

        return [
            'reply' => "I didn't catch that. I can check your wallet or Q Coin balance, search gigs or marketplace items, help you post a gig or listing, or take you somewhere. Try one of those!",
            'client_actions' => [],
        ];
    }

    protected function logMiss(User $user, string $text): void
    {
        $existing = ConciergeMiss::where('message', $text)->where('resolved', false)->first();
        if ($existing) {
            $existing->increment('occurrences');
            return;
        }
        ConciergeMiss::create(['user_id' => $user->id, 'message' => $text, 'occurrences' => 1]);
    }
}

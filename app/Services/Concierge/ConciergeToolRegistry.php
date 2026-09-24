<?php

namespace App\Services\Concierge;

use App\Models\Gig;
use App\Models\Listing;
use App\Models\User;
use App\Services\QCoinService;
use App\Services\TierService;
use RuntimeException;

class ConciergeToolRegistry
{
    public function __construct(
        protected TierService $tiers,
        protected QCoinService $qcoin,
    ) {}

    public function schemas(): array
    {
        return [
            [
                'name' => 'search_gigs',
                'description' => 'Search open gigs on QLEVO by keyword, category, or max budget. Returns matching gigs with id, title, budget, and url.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keyword to search for'],
                        'max_budget' => ['type' => 'number', 'description' => 'Maximum budget in Naira'],
                    ],
                ],
            ],
            [
                'name' => 'search_marketplace',
                'description' => 'Search marketplace listings by keyword or max price. Returns matching listings with id, title, price, and url.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string'],
                        'max_price' => ['type' => 'number'],
                    ],
                ],
            ],
            [
                'name' => 'get_wallet_balance',
                'description' => "Get the user's current wallet (Naira) balance.",
                'input_schema' => ['type' => 'object', 'properties' => []],
            ],
            [
                'name' => 'get_qcoin_balance',
                'description' => "Get the user's current Q Coin balance.",
                'input_schema' => ['type' => 'object', 'properties' => []],
            ],
            [
                'name' => 'claim_daily_reward',
                'description' => "Claim the user's daily Q Coin reward, if not already claimed today.",
                'input_schema' => ['type' => 'object', 'properties' => []],
            ],
            [
                'name' => 'navigate',
                'description' => 'Send the user to a specific page in QLEVO. Use this when the user wants to go somewhere rather than see inline results.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'page' => [
                            'type' => 'string',
                            'enum' => ['gigs', 'marketplace', 'wallet', 'money_tracker', 'profile', 'gigs_create', 'marketplace_create', 'marketplace_orders'],
                        ],
                    ],
                    'required' => ['page'],
                ],
            ],
        ];
    }

    /**
     * Execute a tool call. Returns an array: the result to send back to
     * Claude, plus an optional 'client_action' the frontend should perform
     * (e.g. navigation) separate from the conversational reply.
     */
    public function execute(User $user, string $toolName, array $input): array
    {
        return match ($toolName) {
            'search_gigs' => $this->searchGigs($input),
            'search_marketplace' => $this->searchMarketplace($input),
            'get_wallet_balance' => ['result' => ['balance_naira' => (float) $user->wallet_balance]],
            'get_qcoin_balance' => ['result' => ['balance_q' => (int) $user->q_coin_balance]],
            'claim_daily_reward' => $this->claimDailyReward($user),
            'navigate' => $this->navigate($input),
            default => ['result' => ['error' => "Unknown tool: {$toolName}"]],
        };
    }

    protected function searchGigs(array $input): array
    {
        $q = Gig::query()->where('status', 'open');

        if (!empty($input['query'])) {
            $q->where('title', 'like', '%' . $input['query'] . '%');
        }

        if (!empty($input['max_budget'])) {
            $q->where('amount', '<=', (float) $input['max_budget']);
        }

        $gigs = $q->orderByDesc('created_at')->limit(5)->get();

        return [
            'result' => [
                'gigs' => $gigs->map(fn (Gig $gig) => [
                    'id' => $gig->id,
                    'title' => $gig->title,
                    'budget' => (float) $gig->amount,
                    'url' => route('gigs.show', $gig),
                ])->values(),
            ],
        ];
    }

    protected function searchMarketplace(array $input): array
    {
        $q = Listing::query()->where('status', 'available');

        if (!empty($input['query'])) {
            $q->where('title', 'like', '%' . $input['query'] . '%');
        }

        if (!empty($input['max_price'])) {
            $q->where('amount', '<=', (float) $input['max_price']);
        }

        $listings = $q->orderByDesc('created_at')->limit(5)->get();

        return [
            'result' => [
                'listings' => $listings->map(fn (Listing $listing) => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'price' => (float) $listing->amount,
                    'url' => route('marketplace.show', $listing),
                ])->values(),
            ],
        ];
    }

    protected function claimDailyReward(User $user): array
    {
        try {
            $reward = $this->qcoin->claimDaily($user);

            return [
                'result' => [
                    'claimed' => true,
                    'amount' => $reward,
                    'new_balance' => (int) $user->fresh()->q_coin_balance,
                ],
            ];
        } catch (RuntimeException $e) {
            return [
                'result' => [
                    'claimed' => false,
                    'reason' => $e->getMessage(),
                ],
            ];
        }
    }

    protected function navigate(array $input): array
    {
        $routes = [
            'gigs' => 'gigs.index',
            'marketplace' => 'marketplace.index',
            'wallet' => 'wallet.index',
            'money_tracker' => 'money-tracker.index',
            'profile' => 'profile.show',
            'gigs_create' => 'gigs.create',
            'marketplace_create' => 'marketplace.create',
            'marketplace_orders' => 'marketplace.orders',
        ];

        $page = $input['page'] ?? null;
        $routeName = $routes[$page] ?? null;

        if (!$routeName) {
            return ['result' => ['error' => "Unknown page: {$page}"]];
        }

        return [
            'result' => ['navigated' => true, 'page' => $page],
            'client_action' => ['type' => 'navigate', 'url' => route($routeName)],
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VtuService
{
    protected string $baseUrl;
    protected string $token;

    // Allowed network strings this app accepts from users. The numeric
    // network code Oseultimate actually expects is NOT hardcoded here -
    // it's looked up live from their own /api/user/ response, since
    // that's the only place it's documented.
    public const NETWORKS = ['mtn', 'glo', '9mobile', 'airtel'];

    // Cable providers this app accepts; keys match Oseultimate's Cableplan
    // catalog structure (GOTVPLAN, DSTVPLAN, STARTIMEPLAN — no underscore,
    // unlike the Dataplans keys) and its separate 'cablename' id/name list
    // used for the cablesub POST body.
    public const CABLE_PROVIDERS = ['gotv', 'dstv', 'startime'];

    public function __construct()
    {
        $this->baseUrl = rtrim(env('VTU_BASE_URL', 'https://oseultimate.com/api'), '/');
        $this->token = env('VTU_TOKEN', '');
    }

    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Token ' . $this->token,
            'Content-Type' => 'application/json',
        ])->timeout(30);
    }

    // Oseultimate bundles the whole data-plan catalog (and the network
    // codes needed to buy anything) inside the account-detail response -
    // there's no separate plans endpoint. That same response also
    // contains the account password hash and bank details, so we
    // deliberately extract and cache ONLY the Dataplans portion, never
    // the full raw response.
    protected function accountData(): array
    {
        return Cache::remember('vtu_account_data', now()->addMinutes(15), function () {
            $response = $this->client()->get("{$this->baseUrl}/user/");
            if (!$response->successful()) {
                return ['dataplans' => [], 'cableplans' => []];
            }

            $data = $response->json();
            return [
                'dataplans' => $data['Dataplans'] ?? [],
                'cableplans' => $data['Cableplan'] ?? [],
            ];
        });
    }

    protected function networkCode(string $network): ?int
    {
        $group = $this->accountData()['dataplans'][strtoupper($network) . '_PLAN']['ALL'] ?? [];
        return isset($group[0]['network']) ? (int) $group[0]['network'] : null;
    }

    public function getDataPlans(string $network): array
    {
        if (!in_array($network, self::NETWORKS, true)) return [];

        $group = $this->accountData()['dataplans'][strtoupper($network) . '_PLAN']['ALL'] ?? [];

        return collect($group)->map(fn($plan) => [
            'id' => $plan['dataplan_id'] ?? $plan['id'] ?? null,
            'plan_type' => $plan['plan_type'] ?? '',
            'size' => $plan['plan'] ?? '',
            'validity' => $plan['month_validate'] ?? '',
            'amount' => (float) ($plan['plan_amount'] ?? 0),
        ])->filter(fn($p) => $p['id'] !== null)->values()->all();
    }

    // Buckets a raw validity string like "7 DAYS { Tiktok}" or "30 days"
    // into a clean, sortable group: ['label' => '7 Days', 'days' => 7].
    // Anything without a recognisable day/month/year count falls into
    // an 'Other' bucket, sorted last.
    public static function normalizeValidity(string $raw): array
    {
        if (preg_match('/(\d+)\s*(day|days|month|months|yr|yrs|year|years)/i', $raw, $m)) {
            $n = (int) $m[1];
            $unit = strtolower($m[2]);

            if (str_starts_with($unit, 'day')) {
                return ['label' => $n . ' Day' . ($n > 1 ? 's' : ''), 'days' => $n];
            }
            if (str_starts_with($unit, 'month')) {
                return ['label' => $n . ' Month' . ($n > 1 ? 's' : ''), 'days' => $n * 30];
            }
            return ['label' => $n . ' Year' . ($n > 1 ? 's' : ''), 'days' => $n * 365];
        }

        return ['label' => 'Other', 'days' => 999999];
    }

    public function buyAirtime(string $network, string $phone, float $amount, string $reference): array
    {
        $code = $this->networkCode($network);
        if (!$code) return ['success' => false, 'message' => 'Unknown or unavailable network.'];

        $response = $this->client()->post("{$this->baseUrl}/topup/", [
            'network' => $code,
            'amount' => (string) $amount,
            'mobile_number' => $phone,
            'Ported_number' => true,
            'airtime_type' => 'VTU',
            'request_id' => $reference,
        ]);

        return $this->parseResponse($response);
    }

    public function buyData(string $network, string $phone, string $planId, string $reference): array
    {
        $code = $this->networkCode($network);
        if (!$code) return ['success' => false, 'message' => 'Unknown or unavailable network.'];

        $response = $this->client()->post("{$this->baseUrl}/data/", [
            'network' => $code,
            'mobile_number' => $phone,
            'plan' => $planId,
            'Ported_number' => true,
            'request_id' => $reference,
        ]);

        return $this->parseResponse($response);
    }

    // Cable provider ids (cablename in the API) are looked up live from the
    // account response's 'cablename' list, same reasoning as networkCode() -
    // never hardcoded, since that's the only place Oseultimate documents them.
    protected function cableProviderId(string $provider): ?int
    {
        $list = $this->accountData()['cableplans']['cablename'] ?? [];
        $match = collect($list)->first(fn($c) => strtoupper($c['name'] ?? '') === strtoupper($provider));
        return isset($match['id']) ? (int) $match['id'] : null;
    }

    public function getCablePlans(string $provider): array
    {
        if (!in_array($provider, self::CABLE_PROVIDERS, true)) return [];
        $key = strtoupper($provider) . 'PLAN';
        $group = $this->accountData()['cableplans'][$key] ?? [];

        return collect($group)->map(fn($plan) => [
            'id' => $plan['cableplan_id'] ?? $plan['id'] ?? null,
            'label' => $plan['package'] ?? '',
            'amount' => (float) ($plan['plan_amount'] ?? 0),
        ])->filter(fn($p) => $p['id'] !== null)->values()->all();
    }

    public function buyCable(string $provider, string $smartCardNumber, string $planId, string $reference): array
    {
        $providerId = $this->cableProviderId($provider);
        if (!$providerId) return ['success' => false, 'message' => 'Unknown or unavailable cable provider.'];

        // Only the 3 documented fields are sent - request_id is NOT confirmed
        // for this endpoint (unlike topup/data), so it's deliberately omitted
        // rather than guessed.
        $response = $this->client()->post("{$this->baseUrl}/cablesub/", [
            'cablename' => $providerId,
            'cableplan' => $planId,
            'smart_card_number' => $smartCardNumber,
        ]);

        return $this->parseResponse($response);
    }

    protected function parseResponse($response): array
    {
        if (!$response->successful()) {
            return ['success' => false, 'message' => 'Provider error (HTTP ' . $response->status() . ').', 'raw' => $response->json()];
        }

        $data = $response->json();
        $status = strtolower($data['Status'] ?? $data['status'] ?? '');
        $success = in_array($status, ['successful', 'success', 'delivered', 'completed'], true);

        return [
            'success' => $success,
            'message' => $data['api_response'] ?? $data['message'] ?? ($success ? 'Successful' : 'Transaction failed'),
            'raw' => $data,
        ];
    }
}

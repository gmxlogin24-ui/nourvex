<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VtuNgService
{
    protected string $baseUrl;

    public const ELECTRICITY_PROVIDERS = [
        'ikeja-electric', 'eko-electric', 'kano-electric', 'portharcourt-electric',
        'jos-electric', 'ibadan-electric', 'kaduna-electric', 'abuja-electric',
        'enugu-electric', 'benin-electric', 'aba-electric', 'yola-electric',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.vtung.base_url', 'https://vtu.ng/wp-json'), '/');
    }

    // Maps this app's network slug to whatever service_id vtu.ng expects.
    // Defaults to the same slug - adjust config/services.php's vtung.network_map
    // (or the VTUNG_NETWORK_* env vars) if a real purchase shows vtu.ng wants
    // something different.
    public static function mapNetwork(string $network): string
    {
        return config("services.vtung.network_map.{$network}", $network);
    }

    // JWT tokens are valid 7 days per VTU.ng's docs; cached for 6 to refresh
    // a day early rather than risk using one right at expiry.
    protected function login(): ?string
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/jwt-auth/v1/token", [
                'username' => config('services.vtung.username'),
                'password' => config('services.vtung.password'),
            ]);
        } catch (\Throwable $e) {
            Log::error('vtu.ng login failed: ' . $e->getMessage());
            return null;
        }

        $token = $response->json()['token'] ?? null;
        if ($token) {
            Cache::put('vtung_jwt_token', $token, now()->addDays(6));
        }
        return $token;
    }

    protected function token(): ?string
    {
        return Cache::get('vtung_jwt_token') ?: $this->login();
    }

    // Transparently re-logs-in once if the cached token turns out expired/invalid,
    // rather than failing outright on a stale cache entry. Any connection
    // exception is turned into a clean failed-response shape rather than
    // being thrown, so callers never have to try/catch this.
    protected function authedRequest(\Closure $makeRequest)
    {
        try {
            $response = $makeRequest(Http::timeout(30)->withToken($this->token() ?? ''));
            $code = $response->json()['code'] ?? null;

            if (in_array($code, ['jwt_auth_failed', 'jwt_auth_invalid_token', 'rest_forbidden'], true)) {
                Cache::forget('vtung_jwt_token');
                $freshToken = $this->login();
                $response = $makeRequest(Http::timeout(30)->withToken($freshToken ?? ''));
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('vtu.ng request failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function parseResponse($response): array
    {
        if ($response === null) {
            return ['success' => false, 'message' => 'Provider connection error.', 'data' => [], 'raw' => []];
        }

        $data = $response->json() ?? [];
        $success = ($data['code'] ?? null) === 'success';

        return [
            'success' => $success,
            'message' => $data['message'] ?? ($success ? 'Successful' : 'Request failed'),
            'data' => $data['data'] ?? [],
            'raw' => $data,
        ];
    }

    public function buyAirtime(string $network, string $phone, float $amount, string $reference): array
    {
        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/airtime", [
            'request_id' => $reference,
            'phone' => $phone,
            'service_id' => self::mapNetwork($network),
            'amount' => (int) $amount,
        ]));
        return $this->parseResponse($response);
    }

    public function getDataPlans(string $network): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/api/v2/variations/data", ['service_id' => self::mapNetwork($network)]);
        } catch (\Throwable $e) {
            Log::error('vtu.ng data plans fetch failed: ' . $e->getMessage());
            return [];
        }

        $data = $response->json();
        if (($data['code'] ?? null) !== 'success') return [];

        return collect($data['data'] ?? [])
            ->filter(fn($p) => ($p['availability'] ?? '') === 'Available')
            ->map(fn($p) => [
                'id' => (string) ($p['variation_id'] ?? ''),
                'label' => $p['data_plan'] ?? '',
                'amount' => (float) ($p['price'] ?? 0),
            ])
            ->filter(fn($p) => $p['id'] !== '')
            ->values()->all();
    }

    public function buyData(string $network, string $phone, string $variationId, string $reference): array
    {
        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/data", [
            'request_id' => $reference,
            'phone' => $phone,
            'service_id' => self::mapNetwork($network),
            'variation_id' => $variationId,
        ]));
        return $this->parseResponse($response);
    }

    public function getCablePlans(string $provider): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/api/v2/variations/tv", ['service_id' => $provider]);
        } catch (\Throwable $e) {
            Log::error('vtu.ng cable plans fetch failed: ' . $e->getMessage());
            return [];
        }

        $data = $response->json();
        if (($data['code'] ?? null) !== 'success') return [];

        return collect($data['data'] ?? [])
            ->filter(fn($p) => ($p['availability'] ?? '') === 'Available')
            ->map(fn($p) => [
                'id' => (string) ($p['variation_id'] ?? ''),
                'label' => $p['package_bouquet'] ?? '',
                'amount' => (float) ($p['price'] ?? 0),
            ])
            ->filter(fn($p) => $p['id'] !== '')
            ->values()->all();
    }

    public function buyCable(string $provider, string $customerId, string $variationId, string $reference, ?string $subscriptionType = null, ?float $amount = null): array
    {
        $payload = [
            'request_id' => $reference,
            'customer_id' => $customerId,
            'service_id' => $provider,
            'variation_id' => $variationId,
        ];
        if ($subscriptionType) $payload['subscription_type'] = $subscriptionType;
        if ($amount !== null) $payload['amount'] = (int) $amount;

        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/tv", $payload));
        return $this->parseResponse($response);
    }

    // Required before purchasing electricity - confirms the meter resolves to
    // a real customer before any money moves.
    public function verifyCustomer(string $customerId, string $serviceId, ?string $variationId = null): array
    {
        $payload = ['customer_id' => $customerId, 'service_id' => $serviceId];
        if ($variationId) $payload['variation_id'] = $variationId;

        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/verify-customer", $payload));
        return $this->parseResponse($response);
    }

    public function buyElectricity(string $serviceId, string $meterNumber, string $meterType, float $amount, string $reference): array
    {
        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/electricity", [
            'request_id' => $reference,
            'customer_id' => $meterNumber,
            'service_id' => $serviceId,
            'variation_id' => $meterType,
            'amount' => (int) $amount,
        ]));
        return $this->parseResponse($response);
    }

    public function buyBetting(string $serviceId, string $customerId, float $amount, string $reference): array
    {
        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/betting", [
            'request_id' => $reference,
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'amount' => (int) $amount,
        ]));
        return $this->parseResponse($response);
    }

    // Checks the real status of an order - useful when a purchase call times
    // out and you don't know if it actually went through.
    public function requery(string $requestId): array
    {
        $response = $this->authedRequest(fn($http) => $http->post("{$this->baseUrl}/api/v2/requery", ['request_id' => $requestId]));
        return $this->parseResponse($response);
    }
}

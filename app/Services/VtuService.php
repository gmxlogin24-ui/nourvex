<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VtuService
{
    protected string $baseUrl;
    protected string $token;

    // Adjust these codes if your provider's actual docs use different values.
    public const NETWORKS = [
        'mtn' => 1,
        'glo' => 2,
        '9mobile' => 3,
        'airtel' => 4,
    ];

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

    public function getDataPlans(string $network): array
    {
        $code = self::NETWORKS[$network] ?? null;
        if (!$code) return [];

        return Cache::remember("vtu_data_plans_{$network}", now()->addMinutes(30), function () use ($code) {
            $response = $this->client()->get("{$this->baseUrl}/data_plans/", ['network' => $code]);
            if (!$response->successful()) return [];
            $data = $response->json();
            return $data['data'] ?? $data ?? [];
        });
    }

    public function buyAirtime(string $network, string $phone, float $amount, string $reference): array
    {
        $code = self::NETWORKS[$network] ?? null;
        if (!$code) return ['success' => false, 'message' => 'Unknown network.'];

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
        $code = self::NETWORKS[$network] ?? null;
        if (!$code) return ['success' => false, 'message' => 'Unknown network.'];

        $response = $this->client()->post("{$this->baseUrl}/data/", [
            'network' => $code,
            'mobile_number' => $phone,
            'plan' => $planId,
            'Ported_number' => true,
            'request_id' => $reference,
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

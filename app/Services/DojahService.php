<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DojahService
{
    public function configured(): bool
    {
        return filled(config('dojah.app_id')) && filled(config('dojah.secret'));
    }

    // Compares a selfie with the photo on a NIN record.
    public function matchSelfieToNin(string $nin, string $selfieBase64): array
    {
        return $this->call('/api/v1/kyc/nin/verify', ['nin' => $nin, 'selfie_image' => $this->clean($selfieBase64)], 'nin');
    }

    // Compares a selfie with the photo on the user's ID card.
    public function matchSelfieToPhotoId(string $selfieBase64, string $photoIdBase64): array
    {
        return $this->call('/api/v1/kyc/photoid/verify', [
            'selfie_image' => $this->clean($selfieBase64),
            'photoid_image' => $this->clean($photoIdBase64),
        ], 'photoid');
    }

    private function clean(string $b64): string
    {
        // Dojah wants only the base64 body, without the data:image/...;base64, prefix.
        return preg_replace('#^data:image/[a-zA-Z+]+;base64,#', '', trim($b64));
    }

    private function call(string $path, array $body, string $kind): array
    {
        if (!$this->configured()) {
            return ['ok' => false, 'match' => false, 'confidence' => 0,
                    'error' => 'Face check is not set up yet. Your ID will be reviewed manually.'];
        }
        try {
            $res = Http::withHeaders([
                'AppId' => config('dojah.app_id'),
                'Authorization' => config('dojah.secret'),
            ])->timeout(45)->post(rtrim(config('dojah.base_url'), '/') . $path, $body);
        } catch (\Throwable $e) {
            return ['ok' => false, 'match' => false, 'confidence' => 0, 'error' => 'Face check could not be reached. Try again later.'];
        }
        if (!$res->successful()) {
            return ['ok' => false, 'match' => false, 'confidence' => 0, 'error' => 'Face check was rejected. Check the photo and try again.'];
        }
        $data = $res->json();
        $score = (float) (data_get($data, 'entity.selfie_verification.confidence_value')
            ?? data_get($data, 'entity.selfie.confidence_value') ?? 0);
        $threshold = (float) config('dojah.match_threshold', 90);
        return [
            'ok' => true,
            'confidence' => $score,
            'match' => $score >= $threshold,
            'error' => null,
        ];
    }
}

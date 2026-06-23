<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FcmService
{
    private const ACCESS_TOKEN_CACHE_TTL_SECONDS = 3300;

    public function __construct(private readonly SystemSettingService $settings) {}

    public function isEnabled(): bool
    {
        return $this->settings->getBool('fcm', 'enabled', false);
    }

    /**
     * @throws RuntimeException
     */
    public function assertConfigured(): void
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('FCM is disabled.');
        }

        if ($this->legacyServerKey() !== '') {
            return;
        }

        $credentials = $this->credentials();
        $required = ['project_id', 'client_email', 'private_key'];

        foreach ($required as $field) {
            if (! is_string($credentials[$field] ?? null) || trim((string) $credentials[$field]) === '') {
                throw new RuntimeException('FCM credentials are incomplete.');
            }
        }
    }

    /**
     * @param  array<string, string|int|float|bool|null>  $data
     *
     * @throws RuntimeException
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        $token = trim($token);
        if ($token === '') {
            throw new RuntimeException('FCM token is required.');
        }

        $this->assertConfigured();

        $legacyKey = $this->legacyServerKey();
        if ($legacyKey !== '') {
            $this->sendLegacy($legacyKey, $token, $title, $body, $data);

            return;
        }

        $credentials = $this->credentials();
        $projectId = trim((string) $this->settings->get('fcm', 'project_id', $credentials['project_id'] ?? ''));
        if ($projectId === '') {
            throw new RuntimeException('FCM project_id is required.');
        }

        $accessToken = $this->serviceAccountAccessToken($credentials);

        $messageData = [];
        foreach ($data as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $messageData[$key] = match (true) {
                is_bool($value) => $value ? '1' : '0',
                $value === null => '',
                default => (string) $value,
            };
        }

        $response = Http::acceptJson()
            ->withToken($accessToken)
            ->timeout(20)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $messageData,
                ],
            ]);

        if ($response->successful()) {
            return;
        }

        $message = (string) ($response->json('error.message')
            ?? $response->json('error.status')
            ?? $response->json('error')
            ?? 'Failed to send FCM push notification.');

        throw new RuntimeException($message);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    protected function serviceAccountAccessToken(array $credentials): string
    {
        $projectId = (string) ($credentials['project_id'] ?? '');
        $cacheKey = 'fcm.access_token.'.sha1($projectId);

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $privateKey = (string) ($credentials['private_key'] ?? '');
        $privateKey = str_replace('\n', "\n", $privateKey);
        $clientEmail = (string) ($credentials['client_email'] ?? '');
        if ($privateKey === '' || $clientEmail === '') {
            throw new RuntimeException('FCM credentials are invalid.');
        }

        $now = time();
        $header = $this->base64UrlEncode((string) json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode((string) json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsignedJwt = "{$header}.{$claims}";
        $signature = '';
        $signed = openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Unable to sign FCM service account JWT.');
        }

        $jwt = $unsignedJwt.'.'.$this->base64UrlEncode($signature);

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(20)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful()) {
            $message = (string) ($response->json('error_description')
                ?? $response->json('error')
                ?? 'Unable to obtain Google OAuth token for FCM.');
            throw new RuntimeException($message);
        }

        $token = (string) $response->json('access_token', '');
        if ($token === '') {
            throw new RuntimeException('Google OAuth token for FCM is empty.');
        }

        Cache::put($cacheKey, $token, now()->addSeconds(self::ACCESS_TOKEN_CACHE_TTL_SECONDS));

        return $token;
    }

    /**
     * @param  array<string, string|int|float|bool|null>  $data
     */
    protected function sendLegacy(string $serverKey, string $token, string $title, string $body, array $data = []): void
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'key='.$serverKey,
            ])
            ->timeout(20)
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

        if ($response->successful()) {
            return;
        }

        $message = (string) ($response->json('results.0.error')
            ?? $response->json('error')
            ?? 'Failed to send FCM push notification.');

        throw new RuntimeException($message);
    }

    /**
     * @return array<string, mixed>
     */
    protected function credentials(): array
    {
        $raw = trim((string) $this->settings->get('fcm', 'credentials_json', '{}'));
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function legacyServerKey(): string
    {
        return trim((string) $this->settings->get('fcm', 'server_key', ''));
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

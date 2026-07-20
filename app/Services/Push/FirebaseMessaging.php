<?php

declare(strict_types=1);

namespace App\Services\Push;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal FCM HTTP v1 client.
 *
 * Deliberately hand-rolled rather than pulling in the Firebase Admin SDK: the
 * only thing needed is "sign a JWT, swap it for an access token, POST a
 * message", and the shared cPanel host this deploys to is happier with fewer
 * composer dependencies.
 *
 * The legacy `fcm/send` server-key endpoint was decommissioned in 2024, so this
 * uses the OAuth2 / service-account flow that replaced it.
 */
class FirebaseMessaging
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const CACHE_KEY = 'firebase.fcm.access_token';

    /**
     * FCM error codes that mean "this token is dead, stop using it".
     */
    private const DEAD_TOKEN_CODES = ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'];

    public function isConfigured(): bool
    {
        return filled(config('services.firebase.project_id'))
            && is_readable((string) config('services.firebase.credentials'));
    }

    /**
     * Send one message to one device token.
     *
     * @param  array<string, string>  $data  extra key/value payload delivered to the service worker
     * @return array{ok: bool, dead: bool, error: string|null}
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $projectId = (string) config('services.firebase.project_id');

        $notification = array_filter([
            'title' => $title,
            'body' => $body,
            'image' => $imageUrl,
        ]);

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => $notification,
                    // Mirrored into `data` so the service worker can render a
                    // click-through action; `notification` alone is not readable
                    // from JS on all browsers.
                    'data' => array_map(static fn ($v): string => (string) $v, $data),
                    'webpush' => array_filter([
                        'notification' => array_filter([
                            'icon' => $data['icon'] ?? null,
                            'badge' => $data['icon'] ?? null,
                        ]),
                        'fcm_options' => array_filter([
                            'link' => $data['url'] ?? null,
                        ]),
                    ]),
                ],
            ]);

        if ($response->successful()) {
            return ['ok' => true, 'dead' => false, 'error' => null];
        }

        $code = (string) $response->json('error.status', '');
        $message = (string) $response->json('error.message', 'FCM request failed with HTTP '.$response->status());

        return [
            'ok' => false,
            'dead' => in_array($code, self::DEAD_TOKEN_CODES, true),
            'error' => $message,
        ];
    }

    /**
     * A short-lived OAuth access token, cached just under its one-hour life.
     */
    private function accessToken(): string
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(50), function (): string {
            $credentials = $this->credentials();

            $response = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->signedJwt($credentials),
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Could not obtain a Firebase access token: '.$response->body());
            }

            return (string) $response->json('access_token');
        });
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function signedJwt(array $credentials): string
    {
        $now = time();

        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64Url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $signature = '';

        if (! openssl_sign("{$header}.{$claims}", $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign the Firebase JWT — check the service-account private key.');
        }

        return "{$header}.{$claims}.".$this->base64Url($signature);
    }

    /**
     * @return array{client_email: string, private_key: string}
     */
    private function credentials(): array
    {
        $path = (string) config('services.firebase.credentials');

        if (! is_readable($path)) {
            throw new RuntimeException("Firebase service-account file not found or unreadable: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || ! isset($decoded['client_email'], $decoded['private_key'])) {
            throw new RuntimeException("Firebase service-account file at {$path} is not a valid service-account JSON.");
        }

        return $decoded;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

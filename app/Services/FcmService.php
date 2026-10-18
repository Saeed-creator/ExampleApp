<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Minimal Firebase Cloud Messaging (HTTP v1) client.
 *
 * It authenticates with a Firebase service-account JSON file (no extra
 * Composer packages needed) and sends one push message to one device token.
 */
class FcmService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const DEFAULT_TOKEN_URI = 'https://oauth2.googleapis.com/token';

    private const ACCESS_TOKEN_CACHE_KEY = 'fcm.oauth_access_token';

    private ?array $credentials = null;

    /**
     * Whether Firebase credentials are available: either the FIREBASE_CREDENTIALS_JSON
     * variable (raw or base64 JSON) or the service-account file.
     */
    public function isConfigured(): bool
    {
        if ($this->credentialsFromEnv() !== null) {
            return true;
        }

        $path = config('services.fcm.credentials');

        return is_string($path) && $path !== '' && is_file($path);
    }

    /** Decodes FIREBASE_CREDENTIALS_JSON (raw JSON or base64 of the JSON); null when unset or unreadable. */
    private function credentialsFromEnv(): ?array
    {
        $value = config('services.fcm.credentials_json');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            $decoded = json_decode((string) base64_decode($value, true), true);
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Send a push notification to a single device.
     *
     * Returns true when FCM accepted the message. Tokens that FCM reports as
     * no longer valid (app uninstalled, token rotated) are deleted.
     *
     * @param  array<string, scalar>  $data  Extra key/value pairs the Flutter app receives.
     *
     * @throws RuntimeException When the Firebase credentials are missing or invalid.
     */
    public function send(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'android' => [
                'priority' => 'high',
            ],
            'apns' => [
                'payload' => [
                    'aps' => ['sound' => 'default'],
                ],
            ],
        ];

        // FCM requires every data value to be a string, and rejects an empty list.
        if ($data !== []) {
            $message['data'] = array_map(fn ($value) => (string) $value, $data);
        }

        try {
            $response = $this->post(['message' => $message]);

            if ($response->status() === 401) {
                // The cached access token was rejected: fetch a new one, retry once.
                Cache::forget(self::ACCESS_TOKEN_CACHE_KEY);
                $response = $this->post(['message' => $message]);
            }
        } catch (ConnectionException $e) {
            Log::warning('FCM push failed: could not reach Google.', ['error' => $e->getMessage()]);

            return false;
        }

        if ($response->successful()) {
            return true;
        }

        if ($this->tokenIsInvalid($response)) {
            DeviceToken::where('token', $deviceToken)->delete();

            return false;
        }

        Log::warning('FCM push failed.', [
            'status' => $response->status(),
            'error' => $response->json('error.message') ?? $response->body(),
        ]);

        return false;
    }

    private function post(array $payload): Response
    {
        $projectId = $this->credentials()['project_id'];

        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);
    }

    /**
     * FCM tells us a token is dead with a 404 UNREGISTERED, or with a 400
     * that points at the "message.token" field.
     */
    private function tokenIsInvalid(Response $response): bool
    {
        if ($response->json('error.status') === 'NOT_FOUND') {
            return true;
        }

        foreach ($response->json('error.details') ?? [] as $detail) {
            if (($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                return true;
            }

            foreach ($detail['fieldViolations'] ?? [] as $violation) {
                if (($violation['field'] ?? null) === 'message.token') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get a Google OAuth2 access token, cached until shortly before it expires.
     */
    private function accessToken(): string
    {
        $cached = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $credentials = $this->credentials();

        $response = Http::asForm()->post($credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->signedJwt($credentials),
        ]);

        $accessToken = $response->json('access_token');

        if ($response->failed() || ! is_string($accessToken)) {
            throw new RuntimeException(
                'Could not get a Google access token: '
                .($response->json('error_description') ?? 'HTTP '.$response->status())
            );
        }

        $ttl = max(60, (int) $response->json('expires_in', 3600) - 120);

        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $accessToken, $ttl);

        return $accessToken;
    }

    private function signedJwt(array $credentials): string
    {
        $now = time();

        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

        $claims = $this->base64Url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => $credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsigned = $header.'.'.$claims;

        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        if ($privateKey === false || ! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The private_key in the Firebase credentials file is not valid.');
        }

        return $unsigned.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @return array{project_id: string, client_email: string, private_key: string, token_uri?: string}
     */
    private function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Firebase credentials not found. Download a service-account key from the Firebase console, '
                .'then set FIREBASE_CREDENTIALS_JSON (or FIREBASE_CREDENTIALS to the file path).'
            );
        }

        $credentials = $this->credentialsFromEnv()
            ?? json_decode(file_get_contents(config('services.fcm.credentials')), true);

        foreach (['project_id', 'client_email', 'private_key'] as $key) {
            if (! is_array($credentials) || empty($credentials[$key])) {
                throw new RuntimeException("The Firebase credentials file is missing \"{$key}\".");
            }
        }

        return $this->credentials = $credentials;
    }
}

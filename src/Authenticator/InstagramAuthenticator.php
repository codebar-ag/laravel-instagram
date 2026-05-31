<?php

namespace CodebarAg\LaravelInstagram\Authenticator;

use CodebarAg\LaravelInstagram\Exceptions\InstagramException;
use DateTimeImmutable;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use JsonException;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Http\PendingRequest;

class InstagramAuthenticator implements OAuthAuthenticator
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken = null,
        public ?DateTimeImmutable $expiresAt = null,
    ) {}

    /**
     * Apply the authentication to the request.
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $pendingRequest->query()->add('access_token', $this->getAccessToken());
    }

    /**
     * Check if the access token has expired.
     */
    public function hasExpired(): bool
    {
        if (is_null($this->expiresAt)) {
            return false;
        }

        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Check if the access token has not expired.
     */
    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    /**
     * Get the access token
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Get the refresh token
     *
     * @throws InstagramException
     */
    public function getRefreshToken(): ?string
    {
        throw new InstagramException('Instagram does not provide refresh tokens. use getAccessToken() instead.');
    }

    /**
     * Get the expires at DateTime instance
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * Check if the authenticator is refreshable
     */
    public function isRefreshable(): bool
    {
        $created = Carbon::createFromTimestamp($this->getExpiresAt()->getTimestamp())->subDays(60);

        return now()->diffInHours($created) > 24;
    }

    /**
     * Check if the authenticator is not refreshable
     */
    public function isNotRefreshable(): bool
    {
        return ! $this->isRefreshable();
    }

    /**
     * Encode for cache storage (JSON). Replaces PHP serialize, which is unsafe and unsupported with Saloon v4+.
     *
     * @throws JsonException
     */
    public function encodeForCache(): string
    {
        return json_encode([
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
            'expiresAt' => $this->expiresAt?->format(DATE_ATOM),
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Restore from cache. Supports JSON (current) and legacy PHP-serialized payloads for one-time migration.
     *
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public static function decodeFromCache(string $payload): InstagramAuthenticator
    {
        $trimmed = ltrim($payload);

        if ($trimmed !== '' && $trimmed[0] === '{') {
            return self::decodeFromJsonCache($payload);
        }

        try {
            $legacy = unserialize($payload, [
                'allowed_classes' => [
                    static::class,
                    DateTimeImmutable::class,
                ],
            ]);
        } catch (\Throwable $e) {
            if ($e::class === 'UnserializationFailedException') {
                throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.', 0, $e);
            }

            throw $e;
        }

        if (! $legacy instanceof static) {
            throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.');
        }

        return $legacy;
    }

    /**
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    private static function decodeFromJsonCache(string $payload): InstagramAuthenticator
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.');
        }

        if (! isset($data['accessToken']) || ! is_string($data['accessToken']) || $data['accessToken'] === '') {
            throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.');
        }

        $refreshToken = $data['refreshToken'] ?? null;
        if ($refreshToken !== null && ! is_string($refreshToken)) {
            throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.');
        }

        $expiresAt = null;
        if (array_key_exists('expiresAt', $data) && $data['expiresAt'] !== null) {
            if (! is_string($data['expiresAt']) || $data['expiresAt'] === '') {
                throw new InvalidArgumentException('Invalid cached Instagram authenticator payload.');
            }

            try {
                $expiresAt = new DateTimeImmutable($data['expiresAt']);
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid expiresAt in cached Instagram authenticator payload.', 0, $e);
            }
        }

        return new InstagramAuthenticator($data['accessToken'], $refreshToken, $expiresAt);
    }
}

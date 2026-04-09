<?php

namespace CodebarAg\LaravelInstagram\Authenticator;

use DateTimeImmutable;
use Illuminate\Support\Carbon;
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
     * @throws \Exception
     */
    public function getRefreshToken(): ?string
    {
        throw new \Exception('Instagram does not provide refresh tokens. use getAccessToken() instead.');
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
     */
    public static function decodeFromCache(string $payload): static
    {
        $trimmed = ltrim($payload);

        if ($trimmed !== '' && $trimmed[0] === '{') {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            $expiresAt = isset($data['expiresAt']) && is_string($data['expiresAt']) && $data['expiresAt'] !== ''
                ? new DateTimeImmutable($data['expiresAt'])
                : null;

            return new static(
                $data['accessToken'],
                $data['refreshToken'] ?? null,
                $expiresAt,
            );
        }

        $legacy = unserialize($payload, [
            'allowed_classes' => [
                static::class,
                DateTimeImmutable::class,
            ],
        ]);

        if (! $legacy instanceof static) {
            throw new \InvalidArgumentException('Invalid cached Instagram authenticator payload.');
        }

        return $legacy;
    }
}

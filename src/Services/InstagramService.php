<?php

namespace CodebarAg\LaravelInstagram\Services;

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Contracts\InstagramHandlerContract;
use CodebarAg\LaravelInstagram\Data\InstagramUser;
use CodebarAg\LaravelInstagram\Exceptions\InstagramAuthenticationException;
use CodebarAg\LaravelInstagram\Exceptions\InstagramResponseException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use JsonException;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;

class InstagramService implements InstagramHandlerContract
{
    /**
     * @throws CacheInvalidArgumentException
     * @throws InstagramAuthenticationException
     */
    public function connector(): InstagramConnector
    {
        $store = Cache::store(config('instagram.cache_store'));

        if (! $store->has('instagram.authenticator')) {
            throw new InstagramAuthenticationException('No authenticator found. Please authenticate first.');
        }

        $serialized = $store->get('instagram.authenticator');

        if (! is_string($serialized) || $serialized === '') {
            $store->forget('instagram.authenticator');

            throw new InstagramAuthenticationException('No authenticator found. Please authenticate first.');
        }

        try {
            $authenticator = InstagramAuthenticator::decodeFromCache($serialized);
        } catch (JsonException|InvalidArgumentException) {
            $store->forget('instagram.authenticator');

            throw new InstagramAuthenticationException('No authenticator found. Please authenticate first.');
        }

        $connector = new InstagramConnector;

        if ($authenticator->hasExpired()) {
            $authenticator = $connector->refreshAccessToken($authenticator);

            assert($authenticator instanceof InstagramAuthenticator);
            $store->put('instagram.authenticator', $authenticator->encodeForCache(), now()->addDays(60));
        }

        $connector->authenticate($authenticator);

        return $connector;
    }

    /**
     * @throws InstagramAuthenticationException
     */
    public function user(): InstagramUser
    {
        $store = Cache::store(config('instagram.cache_store'));

        if (! $store->has('instagram.authenticated')) {
            throw new InstagramAuthenticationException('No authenticated user found. Please authenticate first.');
        }

        $cachedUser = $store->get('instagram.authenticated');

        if (! is_array($cachedUser) || $cachedUser === []) {
            $store->forget('instagram.authenticated');

            throw new InstagramAuthenticationException('No authenticated user found. Please authenticate first.');
        }

        try {
            return InstagramUser::make($cachedUser);
        } catch (InstagramResponseException) {
            $store->forget('instagram.authenticated');

            throw new InstagramAuthenticationException('No authenticated user found. Please authenticate first.');
        }
    }
}

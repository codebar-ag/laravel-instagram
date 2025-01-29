<?php

namespace CodebarAg\LaravelInstagram\Actions;

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Data\InstagramUser;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class InstagramHandler
{
    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public static function connector(): InstagramConnector
    {
        if (! Cache::store(config('instagram.cache_store'))->has('instagram.authenticator')) {
            throw new \Exception('No authenticator found. Please authenticate first.');
        }

        $serialized = Cache::store(config('instagram.cache_store'))->get('instagram.authenticator');

        if (empty($serialized)) {
            Cache::store(config('instagram.cache_store'))->forget('instagram.authenticator');

            throw new \Exception('No authenticator found. Please authenticate first.');
        }

        $authenticator = InstagramAuthenticator::unserialize($serialized);

        $connector = new InstagramConnector;

        if ($authenticator->hasExpired()) {
            $authenticator = $connector->refreshAccessToken($authenticator);

            Cache::store(config('instagram.cache_store'))->put('instagram.authenticator', $authenticator->serialize(), now()->addDays(60));
        }

        $connector->authenticate($authenticator);

        return $connector;
    }

    public static function user(): InstagramUser
    {
        if (! Cache::store(config('instagram.cache_store'))->has('instagram.authenticated')) {
            throw new \Exception('No authenticated user found. Please authenticate first.');
        }

        if (empty(Cache::store(config('instagram.cache_store'))->get('instagram.authenticated'))) {
            Cache::store(config('instagram.cache_store'))->forget('instagram.authenticated');

            throw new \Exception('No authenticated user found. Please authenticate first.');
        }

        $cachedUser = Cache::store(config('instagram.cache_store'))->get('instagram.authenticated');

        return InstagramUser::make($cachedUser);
    }
}

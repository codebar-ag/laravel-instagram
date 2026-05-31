<?php

namespace CodebarAg\LaravelInstagram\Actions;

use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Contracts\InstagramHandlerContract;
use CodebarAg\LaravelInstagram\Data\InstagramUser;
use CodebarAg\LaravelInstagram\Exceptions\InstagramAuthenticationException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Convenience static facade over the container-bound {@see InstagramHandlerContract}.
 *
 * Inject {@see InstagramHandlerContract} directly when you need a mockable dependency.
 */
class InstagramHandler
{
    /**
     * @throws InstagramAuthenticationException
     * @throws InvalidArgumentException
     */
    public static function connector(): InstagramConnector
    {
        return app(InstagramHandlerContract::class)->connector();
    }

    /**
     * @throws InstagramAuthenticationException
     */
    public static function user(): InstagramUser
    {
        return app(InstagramHandlerContract::class)->user();
    }
}

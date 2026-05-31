<?php

namespace CodebarAg\LaravelInstagram\Contracts;

use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Data\InstagramUser;

interface InstagramHandlerContract
{
    /**
     * Resolve an authenticated connector, refreshing the cached token if it has expired.
     */
    public function connector(): InstagramConnector;

    /**
     * Resolve the authenticated Instagram user from the cache.
     */
    public function user(): InstagramUser;
}

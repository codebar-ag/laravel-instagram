<?php

return [

    /*
     * The client_id from registering your app on Instagram
     */
    'client_id' => env('INSTAGRAM_CLIENT_ID', null),

    /*
     * The client secret from registering your app on Instagram,
     * This is not the same as an access token.
     */
    'client_secret' => env('INSTAGRAM_CLIENT_SECRET', null),

    /*
     * The cache_store to use for caching the authenticator and authenticated user.
     * This should not be the same as your default cache store.
     */
    'cache_store' => env('INSTAGRAM_CACHE_STORE', env('CACHE_DRIVER', 'file')),
];

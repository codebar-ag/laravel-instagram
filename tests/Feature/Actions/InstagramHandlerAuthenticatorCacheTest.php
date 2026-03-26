<?php

use CodebarAg\LaravelInstagram\Actions\InstagramHandler;
use Illuminate\Support\Facades\Cache;

test('connector clears corrupt authenticator cache and prompts re-authentication', function () {
    config(['instagram.cache_store' => 'array']);

    Cache::store('array')->put('instagram.authenticator', '{}', now()->addDay());

    expect(fn () => InstagramHandler::connector())
        ->toThrow(Exception::class, 'No authenticator found. Please authenticate first.');

    expect(Cache::store('array')->has('instagram.authenticator'))->toBeFalse();
});

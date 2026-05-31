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

test('connector clears non-string authenticator cache and prompts re-authentication', function () {
    config(['instagram.cache_store' => 'array']);

    Cache::store('array')->put('instagram.authenticator', ['corrupt' => true], now()->addDay());

    expect(fn () => InstagramHandler::connector())
        ->toThrow(Exception::class, 'No authenticator found. Please authenticate first.');

    expect(Cache::store('array')->has('instagram.authenticator'))->toBeFalse();
});

test('user clears non-array authenticated cache and prompts re-authentication', function () {
    config(['instagram.cache_store' => 'array']);

    Cache::store('array')->put('instagram.authenticated', 'corrupt-string', now()->addDay());

    expect(fn () => InstagramHandler::user())
        ->toThrow(Exception::class, 'No authenticated user found. Please authenticate first.');

    expect(Cache::store('array')->has('instagram.authenticated'))->toBeFalse();
});

test('user clears authenticated cache missing required fields and prompts re-authentication', function () {
    config(['instagram.cache_store' => 'array']);

    Cache::store('array')->put('instagram.authenticated', ['id' => '123'], now()->addDay());

    expect(fn () => InstagramHandler::user())
        ->toThrow(Exception::class, 'No authenticated user found. Please authenticate first.');

    expect(Cache::store('array')->has('instagram.authenticated'))->toBeFalse();
});

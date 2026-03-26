<?php

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;

test('encodeForCache and decodeFromCache round-trip access token only', function () {
    $original = new InstagramAuthenticator('access-token', null, null);
    $decoded = InstagramAuthenticator::decodeFromCache($original->encodeForCache());

    expect($decoded->accessToken)->toBe('access-token')
        ->and($decoded->refreshToken)->toBeNull()
        ->and($decoded->expiresAt)->toBeNull();
});

test('encodeForCache and decodeFromCache round-trip with refresh token and expiresAt', function () {
    $expires = new DateTimeImmutable('2030-06-15T12:00:00+00:00');
    $original = new InstagramAuthenticator('long-lived', 'refresh', $expires);
    $decoded = InstagramAuthenticator::decodeFromCache($original->encodeForCache());

    expect($decoded->accessToken)->toBe('long-lived')
        ->and($decoded->refreshToken)->toBe('refresh')
        ->and($decoded->expiresAt)->not->toBeNull()
        ->and($decoded->expiresAt->format(DATE_ATOM))->toBe($expires->format(DATE_ATOM));
});

test('decodeFromCache reads legacy PHP serialized payload', function () {
    $legacy = new InstagramAuthenticator('legacy-token', null, new DateTimeImmutable('2020-01-01T00:00:00+00:00'));
    $blob = serialize($legacy);

    $decoded = InstagramAuthenticator::decodeFromCache($blob);

    expect($decoded->accessToken)->toBe('legacy-token')
        ->and($decoded->expiresAt)->not->toBeNull()
        ->and($decoded->expiresAt->format('Y-m-d'))->toBe('2020-01-01');
});

test('deprecated serialize and unserialize delegate to JSON cache API', function () {
    $original = new InstagramAuthenticator('tok', null, null);

    $encoded = $original->serialize();
    expect($encoded)->toBe($original->encodeForCache());

    $decoded = InstagramAuthenticator::unserialize($encoded);
    expect($decoded->accessToken)->toBe('tok');
});

test('decodeFromCache throws JsonException for invalid JSON', function () {
    expect(fn () => InstagramAuthenticator::decodeFromCache('{'))
        ->toThrow(JsonException::class);
});

test('decodeFromCache throws when accessToken is missing', function () {
    $payload = json_encode(['refreshToken' => null], JSON_THROW_ON_ERROR);

    expect(fn () => InstagramAuthenticator::decodeFromCache($payload))
        ->toThrow(InvalidArgumentException::class);
});

test('decodeFromCache throws when refreshToken has wrong type', function () {
    $payload = json_encode(['accessToken' => 'x', 'refreshToken' => 1], JSON_THROW_ON_ERROR);

    expect(fn () => InstagramAuthenticator::decodeFromCache($payload))
        ->toThrow(InvalidArgumentException::class);
});

test('decodeFromCache throws when expiresAt is not parseable', function () {
    $payload = json_encode(['accessToken' => 'x', 'expiresAt' => 'not-a-datetime'], JSON_THROW_ON_ERROR);

    expect(fn () => InstagramAuthenticator::decodeFromCache($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid expiresAt in cached Instagram authenticator payload.');
});

test('decodeFromCache throws when legacy payload is not an InstagramAuthenticator', function () {
    expect(fn () => InstagramAuthenticator::decodeFromCache(serialize(new stdClass)))
        ->toThrow(InvalidArgumentException::class, 'Invalid cached Instagram authenticator payload.');
});

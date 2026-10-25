<?php

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('token older than 24 hours is refreshable', function () {
    Carbon::setTestNow('2026-01-10 12:00:00');

    // Issued ten days ago: expires 60 days after issue.
    $authenticator = new InstagramAuthenticator('token', null, new DateTimeImmutable('2026-02-28 12:00:00'));

    expect($authenticator->isRefreshable())->toBeTrue()
        ->and($authenticator->isNotRefreshable())->toBeFalse();
});

test('token younger than 24 hours is not refreshable', function () {
    Carbon::setTestNow('2026-01-10 12:00:00');

    // Issued two hours ago.
    $authenticator = new InstagramAuthenticator('token', null, new DateTimeImmutable('2026-03-11 10:00:00'));

    expect($authenticator->isRefreshable())->toBeFalse()
        ->and($authenticator->isNotRefreshable())->toBeTrue();
});

test('token without expiry is not refreshable', function () {
    $authenticator = new InstagramAuthenticator('token');

    expect($authenticator->isRefreshable())->toBeFalse()
        ->and($authenticator->isNotRefreshable())->toBeTrue();
});

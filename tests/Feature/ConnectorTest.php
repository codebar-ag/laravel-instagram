<?php

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetAccessTokenRequest;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetShortLivedAccessTokenRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('can getAuthorizationUrl', function () {
    $connector = new InstagramConnector;
    $authorizationUrl = $connector->getAuthorizationUrl();

    expect(urldecode($authorizationUrl))
        ->toBeString()
        ->toContain('https://www.instagram.com/oauth/authorize')
        ->toContain('client_id='.config('instagram.client_id'))
        ->toContain('redirect_uri='.route('instagram.callback'))
        ->toContain('response_type=code')
        ->toContain('scope=instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments,instagram_business_content_publish');
})->group('authorization');

test(/**
 * @throws \Saloon\Exceptions\OAuthConfigValidationException
 * @throws \Saloon\Exceptions\InvalidStateException
 * @throws DateMalformedIntervalStringException
 */ 'can getAccessToken', function () {
    MockClient::global([
        GetShortLivedAccessTokenRequest::class => MockResponse::make(
            body: [
                'access_token' => 'some_short_access_token',
                'user_id' => 12345678901234567,
                'permissions' => [
                    'instagram_business_basic',
                    'instagram_business_manage_messages',
                    'instagram_business_content_publish',
                    'instagram_business_manage_insights',
                    'instagram_business_manage_comments',
                ],
            ],
            status: 200
        ),
        GetAccessTokenRequest::class => MockResponse::make(
            body: [
                'access_token' => 'some_long_access_token',
                'refresh_token' => 'some_refresh_token',
                'expires_in' => 5184000,
            ],
            status: 200
        ),
    ]);

    $connector = new InstagramConnector;

    $code = 'some_auth_code';

    $shortLivedAuthenticator = $connector->getShortLivedAccessToken(code: $code);

    expect($shortLivedAuthenticator)
        ->toBeInstanceOf(InstagramAuthenticator::class)
        ->accessToken->toBe('some_short_access_token')
        ->refreshToken->toBe(null)
        ->expiresAt->toBe(null);

    $authenticator = $connector->getAccessToken(code: $shortLivedAuthenticator->accessToken);

    $durationInSeconds = 5184000;
    $baseDateTime = new DateTimeImmutable;
    $date = $baseDateTime->add(new DateInterval('PT'.$durationInSeconds.'S'));

    expect($authenticator)
        ->toBeInstanceOf(InstagramAuthenticator::class)
        ->accessToken->toBe('some_long_access_token')
        ->refreshToken->toBe('some_refresh_token')
        ->expiresAt->toBeInstanceOf(DateTimeImmutable::class)
        ->expiresAt->format('Y-m-d H:i:s')->toBe($date->format('Y-m-d H:i:s'));
});

<?php

namespace CodebarAg\LaravelInstagram\Connectors;

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use CodebarAg\LaravelInstagram\Traits\AuthorizationCodeGrant;
use DateTimeImmutable;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;

class InstagramConnector extends Connector
{
    use AuthorizationCodeGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://graph.instagram.com/v22.0/';
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function createOAuthAuthenticator(string $accessToken, ?string $refreshToken = null, ?DateTimeImmutable $expiresAt = null): OAuthAuthenticator
    {
        return new InstagramAuthenticator($accessToken, $refreshToken, $expiresAt);
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $clientId = config('instagram.client_id');
        $clientSecret = config('instagram.client_secret');

        $scopes = [
            'instagram_business_basic',
            'instagram_business_manage_messages',
            'instagram_business_manage_comments',
            'instagram_business_content_publish',
        ];

        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('INSTAGRAM_CLIENT_ID and/or INSTAGRAM_CLIENT_SECRET must be set in the config file');
        }

        return OAuthConfig::make()
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setDefaultScopes([implode(',', $scopes)])
            ->setRedirectUri(route('instagram.callback'))
            ->setAuthorizeEndpoint('https://www.instagram.com/oauth/authorize')
            ->setTokenEndpoint('https://api.instagram.com/oauth/access_token')
            ->setUserEndpoint('/me');
    }
}

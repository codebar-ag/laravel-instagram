<?php

namespace CodebarAg\LaravelInstagram\Connectors;

use CodebarAg\LaravelInstagram\Authenticator\InstagramAuthenticator;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetAccessTokenRequest;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetShortLivedAccessTokenRequest;
use DateTimeImmutable;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Exceptions\InvalidStateException;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;

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

    /**
     * Get the short lived access token.
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param  callable(TRequest): (void)|null  $requestModifier
     *
     * @throws \Saloon\Exceptions\InvalidStateException
     * @throws OAuthConfigValidationException
     */
    public function getShortLivedAccessToken(string $code, ?string $state = null, ?string $expectedState = null, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate();

        if (! empty($state) && ! empty($expectedState) && $state !== $expectedState) {
            throw new InvalidStateException;
        }

        $request = $this->resolveShortLivedAccessTokenRequest($code, $this->oauthConfig());

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response);
    }

    protected function resolveAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new GetAccessTokenRequest($code, $oauthConfig);
    }

    protected function resolveShortLivedAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new GetShortLivedAccessTokenRequest($code, $oauthConfig);
    }
}

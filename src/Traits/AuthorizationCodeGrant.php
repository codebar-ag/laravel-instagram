<?php

declare(strict_types=1);

namespace CodebarAg\LaravelInstagram\Traits;

use CodebarAg\LaravelInstagram\Requests\Authentication\GetRefreshAccessTokenRequest;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetShortLivedAccessTokenRequest;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Exceptions\InvalidStateException;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Helpers\StringHelpers;
use Saloon\Helpers\URLHelper;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\OAuth2\GetUserRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\HasOAuthConfig;

trait AuthorizationCodeGrant
{
    use HasOAuthConfig;
    use \Saloon\Traits\OAuth2\AuthorizationCodeGrant;

    /**
     * The state generated by the getAuthorizationUrl method.
     */
    protected ?string $state = null;

    /**
     * Get the Authorization URL.
     *
     * @param  array<string>  $scopes
     */
    public function getAuthorizationUrl(array $scopes = [], ?string $state = null, string $scopeSeparator = ' ', array $additionalQueryParameters = []): string
    {
        $config = $this->oauthConfig();

        $config->validate();

        $clientId = $config->getClientId();
        $redirectUri = $config->getRedirectUri();
        $defaultScopes = $config->getDefaultScopes();

        $this->state = $state ?? StringHelpers::random(32);

        $queryParameters = array_filter([
            'response_type' => 'code',
            'scope' => implode($scopeSeparator, array_filter(array_merge($defaultScopes, $scopes))),
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $this->state,
            ...$additionalQueryParameters,
        ]);

        $query = http_build_query($queryParameters, '', '&', PHP_QUERY_RFC3986);
        $query = trim($query, '?&');

        $url = URLHelper::join($this->resolveBaseUrl(), $config->getAuthorizeEndpoint());

        $glue = str_contains($url, '?') ? '&' : '?';

        return $url.$glue.$query;
    }

    /**
     * Get the access token.
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param  callable(TRequest): (void)|null  $requestModifier
     *
     * @throws \Saloon\Exceptions\InvalidStateException
     */
    public function getAccessToken(string $code, ?string $state = null, ?string $expectedState = null, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate();

        if (! empty($state) && ! empty($expectedState) && $state !== $expectedState) {
            throw new InvalidStateException;
        }

        $request = $this->resolveAccessTokenRequest($code, $this->oauthConfig());

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

    /**
     * Refresh the access token.
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param  callable(TRequest): (void)|null  $requestModifier
     */
    public function refreshAccessToken(OAuthAuthenticator|string $refreshToken, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate();

        if ($refreshToken instanceof OAuthAuthenticator) {
            if ($refreshToken->isNotRefreshable()) {
                throw new InvalidArgumentException('Not refreshable.');
            }

            $refreshToken = $refreshToken->getAccessToken();
        }

        $request = $this->resolveRefreshTokenRequest($refreshToken);

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response, $refreshToken);
    }

    /**
     * Create the OAuthAuthenticator from a response.
     */
    protected function createOAuthAuthenticatorFromResponse(Response $response, ?string $fallbackRefreshToken = null): OAuthAuthenticator
    {
        $responseData = $response->object();

        $accessToken = $responseData->access_token;

        $expiresAt = null;

        if (isset($responseData->expires_in) && is_numeric($responseData->expires_in)) {
            $expiresAt = (new DateTimeImmutable)->add(
                DateInterval::createFromDateString((int) $responseData->expires_in.' seconds')
            );
        }

        return $this->createOAuthAuthenticator($accessToken, null, $expiresAt);
    }

    /**
     * Create the authenticator.
     */
    protected function createOAuthAuthenticator(string $accessToken, ?string $refreshToken = null, ?DateTimeImmutable $expiresAt = null): OAuthAuthenticator
    {
        return new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);
    }

    /**
     * Get the authenticated user.
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param  callable(TRequest): (void)|null  $requestModifier
     */
    public function getUser(OAuthAuthenticator $oauthAuthenticator, ?callable $requestModifier = null): Response
    {
        $request = $this->resolveUserRequest($this->oauthConfig())->authenticate($oauthAuthenticator);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        return $this->send($request);
    }

    /**
     * Get the state that was generated in the getAuthorizationUrl() method.
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Resolve the user request
     */
    protected function resolveUserRequest(OAuthConfig $oauthConfig): Request
    {
        return new GetUserRequest($oauthConfig);
    }

    protected function resolveAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new \CodebarAg\LaravelInstagram\Requests\Authentication\GetAccessTokenRequest($code, $oauthConfig);
    }

    protected function resolveShortLivedAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new GetShortLivedAccessTokenRequest($code, $oauthConfig);
    }

    protected function resolveRefreshTokenRequest(string $code): Request
    {
        return new GetRefreshAccessTokenRequest($code);
    }
}

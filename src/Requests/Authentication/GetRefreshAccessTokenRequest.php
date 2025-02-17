<?php

declare(strict_types=1);

namespace CodebarAg\LaravelInstagram\Requests\Authentication;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\AcceptsJson;

class GetRefreshAccessTokenRequest extends Request
{
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return 'https://graph.instagram.com/refresh_access_token';
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     */
    public function __construct(protected string $code) {}

    /**
     * Register the default data.
     *
     * @return array{
     *     grant_type: string,
     *     access_token: string,
     *     client_secret: string,
     * }
     */
    public function defaultQuery(): array
    {
        return [
            'grant_type' => 'ig_exchange_token',
            'access_token' => $this->code,
        ];
    }
}

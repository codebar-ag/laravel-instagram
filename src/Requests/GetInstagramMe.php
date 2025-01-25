<?php

declare(strict_types=1);

namespace CodebarAg\LaravelInstagram\Requests;

use CodebarAg\LaravelInstagram\Data\InstagramUser;
use CodebarAg\LaravelInstagram\Responses\CreateInstagramUserFromResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;

class GetInstagramMe extends Request
{
    use AcceptsJson;

    protected Method $method = Method::GET;

    public function __construct() {}

    public function resolveEndpoint(): string
    {
        return 'me';
    }

    public function defaultQuery(): array
    {
        $fields = [
            'id',
            'user_id',
            'username',
            'name',
            'account_type',
            'profile_picture_url',
            'followers_count',
            'follows_count',
            'media_count',
        ];

        return [
            'fields' => implode(',', $fields),
        ];
    }

    public function createDtoFromResponse(Response $response): InstagramUser
    {
        return CreateInstagramUserFromResponse::fromResponse($response);
    }
}

<?php

declare(strict_types=1);

namespace CodebarAg\LaravelInstagram\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
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
}

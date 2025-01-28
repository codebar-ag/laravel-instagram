<?php

namespace CodebarAg\LaravelInstagram\Responses;

use CodebarAg\LaravelInstagram\Data\InstagramUser;
use Saloon\Http\Response;

final class CreateInstagramUserFromResponse
{
    public static function fromResponse(Response $response): InstagramUser
    {
        $data = $response->json();

        ray($data);

        if (! $data) {
            throw new \Exception('No data found in response');
        }

        return InstagramUser::make($data);
    }
}

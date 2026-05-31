<?php

namespace CodebarAg\LaravelInstagram\Responses;

use CodebarAg\LaravelInstagram\Data\InstagramUser;
use CodebarAg\LaravelInstagram\Exceptions\InstagramResponseException;
use Saloon\Http\Response;

final class CreateInstagramUserFromResponse
{
    public static function fromResponse(Response $response): InstagramUser
    {
        $data = $response->json();

        if (! $data) {
            throw new InstagramResponseException('No data found in response');
        }

        return InstagramUser::make($data);
    }
}

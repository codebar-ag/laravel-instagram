<?php

namespace CodebarAg\LaravelInstagram\Responses;

use CodebarAg\LaravelInstagram\Data\InstagramImage;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Saloon\Http\Response;

final class CreateMediaCollectionFromResponse
{
    public static function fromResponse(Response $response): Collection
    {
        $data = Arr::get($response->json(), 'data');

        if (! $data) {
            throw new \Exception('No data found in response');
        }

        return collect($data)->map(fn (array $dataImage) => InstagramImage::make($dataImage));
    }
}

<?php

namespace CodebarAg\LaravelInstagram\Data;

use Carbon\CarbonImmutable;
use CodebarAg\LaravelInstagram\Exceptions\InstagramResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class InstagramImage
{
    /**
     * @throws InstagramResponseException
     */
    public static function make(array $data): self
    {
        foreach (['id', 'media_type', 'media_url', 'permalink', 'timestamp', 'username'] as $field) {
            if (! Arr::has($data, $field)) {
                throw new InstagramResponseException("Missing required field [{$field}] in Instagram media response.");
            }
        }

        return new self(
            id: Arr::get($data, 'id'),
            media_type: Arr::get($data, 'media_type'),
            media_url: Arr::get($data, 'media_url'),
            permalink: Arr::get($data, 'permalink'),
            timestamp: CarbonImmutable::parse(Arr::get($data, 'timestamp')),
            username: Arr::get($data, 'username'),
            caption: Arr::get($data, 'caption'),
            children: Arr::has($data, 'children.data') ? collect(Arr::get($data, 'children.data'))->map(fn (array $child) => InstagramImage::make($child)) : null,
        );
    }

    public function __construct(
        public string $id,
        public string $media_type,
        public string $media_url,
        public string $permalink,
        public CarbonImmutable $timestamp,
        public string $username,
        public ?string $caption,
        public ?Collection $children = null,
    ) {}
}

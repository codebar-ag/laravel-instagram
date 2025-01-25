<?php

namespace CodebarAg\LaravelInstagram\Data;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class InstagramImage
{
    public static function make(array $data): self
    {
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

<?php

namespace CodebarAg\LaravelInstagram\Data;

use Illuminate\Support\Arr;

final class InstagramUser
{
    public static function make(array $data): self
    {
        return new self(
            id: Arr::get($data, 'id'),
            user_id: Arr::get($data, 'user_id'),
            username: Arr::get($data, 'username'),
            name: Arr::get($data, 'name'),
            account_type: Arr::get($data, 'account_type'),
            profile_picture_url: Arr::get($data, 'profile_picture_url'),
            followers_count: Arr::get($data, 'followers_count'),
            follows_count: Arr::get($data, 'follows_count'),
            media_count: Arr::get($data, 'media_count'),
        );
    }

    public function __construct(
        public string $id,
        public string $user_id,
        public string $username,
        public string $name,
        public string $account_type,
        public ?string $profile_picture_url,
        public int $followers_count,
        public int $follows_count,
        public int $media_count,
    ) {}
}

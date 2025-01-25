<?php

use Carbon\CarbonImmutable;
use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Data\InstagramImage;
use CodebarAg\LaravelInstagram\Requests\GetInstagramMe;
use CodebarAg\LaravelInstagram\Requests\GetInstagramMedia;
use Illuminate\Support\Collection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('can get instagram user', function () {
    MockClient::global([
        GetInstagramMe::class => MockResponse::make([
            'id' => '12345678901234567',
            'user_id' => '76543210987654321',
            'username' => 'some_username',
            'name' => 'Some Name',
            'account_type' => 'BUSINESS',
            'profile_picture_url' => 'https://some-profile-picture-url.com',
            'followers_count' => 123,
            'follows_count' => 321,
            'media_count' => 100,
        ]),
        GetInstagramMedia::class => MockResponse::make([
            'data' => [
                [
                    'id' => '11223344556677889',
                    'caption' => 'Some caption',
                    'media_type' => 'CAROUSEL_ALBUM',
                    'media_url' => 'https://some-media-url.com',
                    'permalink' => 'https://some-permalink.com',
                    'timestamp' => '2022-10-31T19:07:12+0000',
                    'username' => 'some_username',
                    'children' => [
                        'data' => [
                            [
                                'id' => '98877665544332211',
                                'media_type' => 'IMAGE',
                                'media_url' => 'https://some-media-url-two.com',
                                'permalink' => 'https://some-permalink-two.com',
                                'timestamp' => '2022-10-31T19:07:10+0000',
                                'username' => 'some_username',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $connector = new InstagramConnector;

    $response = $connector->send(new GetInstagramMedia(user_id: '12345678901234567'));

    expect($response->dto())->toBeInstanceOf(Collection::class)
        ->and($response->dto()->first())
        ->toBeInstanceOf(InstagramImage::class)
        ->id->toBe('11223344556677889')
        ->caption->toBe('Some caption')
        ->media_type->toBe('CAROUSEL_ALBUM')
        ->media_url->toBe('https://some-media-url.com')
        ->permalink->toBe('https://some-permalink.com')
        ->timestamp->toBeInstanceOf(CarbonImmutable::class)
        ->timestamp->format('Y-m-d H:i:s')->toBe(CarbonImmutable::parse('2022-10-31T19:07:12+0000')->format('Y-m-d H:i:s'))
        ->username->toBe('some_username')
        ->and($response->dto()->first()->children)->toBeInstanceOf(Collection::class)
        ->and($response->dto()->first()->children->first())
        ->toBeInstanceOf(InstagramImage::class)
        ->id->toBe('98877665544332211')
        ->media_type->toBe('IMAGE')
        ->media_url->toBe('https://some-media-url-two.com')
        ->permalink->toBe('https://some-permalink-two.com')
        ->timestamp->toBeInstanceOf(CarbonImmutable::class)
        ->timestamp->format('Y-m-d H:i:s')->toBe(CarbonImmutable::parse('2022-10-31T19:07:10+0000')->format('Y-m-d H:i:s'))
        ->username->toBe('some_username');

})->group('media');

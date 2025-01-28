<?php

use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Data\InstagramUser;
use CodebarAg\LaravelInstagram\Requests\GetInstagramMe;
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
    ]);

    $connector = new InstagramConnector;

    $response = $connector->send(new GetInstagramMe);

    expect($response->dto())->toBeInstanceOf(InstagramUser::class)
        ->id->toBe('12345678901234567')
        ->user_id->toBe('76543210987654321')
        ->username->toBe('some_username')
        ->name->toBe('Some Name')
        ->account_type->toBe('BUSINESS')
        ->profile_picture_url->toBe('https://some-profile-picture-url.com')
        ->followers_count->toBe(123)
        ->follows_count->toBe(321)
        ->media_count->toBe(100);

})->group('user');

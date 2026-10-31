<?php

use CodebarAg\LaravelInstagram\Requests\Authentication\GetAccessTokenRequest;
use CodebarAg\LaravelInstagram\Requests\Authentication\GetShortLivedAccessTokenRequest;
use CodebarAg\LaravelInstagram\Requests\GetInstagramMe;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    config()->set('instagram.cache_store', 'array');
    Cache::store('array')->flush();
});

afterEach(function () {
    MockClient::destroyGlobal();
});

function fakeInstagramCallback(string $username): void
{
    MockClient::global([
        GetShortLivedAccessTokenRequest::class => MockResponse::make([
            'access_token' => 'short-lived-token',
            'user_id' => 12345678901234567,
        ]),
        GetAccessTokenRequest::class => MockResponse::make([
            'access_token' => 'long-lived-token',
            'expires_in' => 5184000,
        ]),
        GetInstagramMe::class => MockResponse::make([
            'id' => '12345678901234567',
            'user_id' => '76543210987654321',
            'username' => $username,
        ]),
    ]);
}

test('callback stores the authenticator and the authenticated user', function () {
    fakeInstagramCallback('some_username');

    $this->get(route('instagram.callback', ['code' => 'auth-code']))
        ->assertOk()
        ->assertSeeText('Authenticated Instagram account: some_username');

    $store = Cache::store('array');

    expect($store->get('instagram.authenticator'))->toBeString()->toContain('long-lived-token')
        ->and($store->get('instagram.authenticated'))->toMatchArray(['username' => 'some_username']);
});

test('callback escapes the username in the response', function () {
    fakeInstagramCallback('<script>alert(1)</script>');

    $response = $this->get(route('instagram.callback', ['code' => 'auth-code']))->assertOk();

    expect($response->getContent())
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
});

test('callback rejects requests without a code', function () {
    $this->get(route('instagram.callback'))->assertForbidden();
});

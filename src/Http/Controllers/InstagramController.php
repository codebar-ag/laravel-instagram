<?php

namespace CodebarAg\LaravelInstagram\Http\Controllers;

use CodebarAg\LaravelInstagram\Actions\InstagramHandler;
use CodebarAg\LaravelInstagram\Connectors\InstagramConnector;
use CodebarAg\LaravelInstagram\Requests\GetInstagramMe;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Saloon\Exceptions\InvalidStateException;
use Saloon\Exceptions\OAuthConfigValidationException;

class InstagramController
{
    public function auth()
    {
        $connector = new InstagramConnector;
        $authorizationUrl = $connector->getAuthorizationUrl();

        return redirect()->to($authorizationUrl);
    }

    /**
     * @throws OAuthConfigValidationException
     * @throws InvalidStateException
     * @throws \JsonException
     */
    public function callback(Request $request)
    {
        if (! $request->has('code')) {
            abort(403, 'Invalid request');
        }

        $connector = new InstagramConnector;
        $shortLivedAuthenticator = $connector->getShortLivedAccessToken(code: $request->get('code'));
        $authenticator = $connector->getAccessToken(code: $shortLivedAuthenticator->accessToken); // @phpstan-ignore-line
        $serialized = $authenticator->serialize(); // @phpstan-ignore-line

        Cache::store(config('instagram.cache_store'))->put('instagram.authenticator', $serialized, now()->addDays(60));

        $connector = InstagramHandler::connector();
        $request = new GetInstagramMe;

        $response = $connector->send($request);

        if (! $response->successful()) {
            return response('Failed to authenticate Instagram account', 500);
        }

        $json = $response->json();

        Cache::store(config('instagram.cache_store'))->put('instagram.authenticated', $json, now()->addDays(60));

        return response('Authenticated Instagram account: '.Arr::get($json, 'username'), 200);
    }
}

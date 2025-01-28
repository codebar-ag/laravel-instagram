<img src="https://banners.beyondco.de/Laravel%20Instagram.png?theme=light&packageManager=composer+require&packageName=codebar-ag%2Flaravel-instagram&pattern=circuitBoard&style=style_1&description=An+opinionated+way+to+integrate+Instagram+with+Laravel&md=1&showWatermark=0&fontSize=175px&images=document-report">


[![Latest Version on Packagist](https://img.shields.io/packagist/v/codebar-ag/laravel-instagram.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-instagram)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/codebar-ag/laravel-instagram/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/codebar-ag/laravel-instagram/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/codebar-ag/laravel-instagram/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/codebar-ag/laravel-instagram/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/codebar-ag/laravel-instagram.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-instagram)

This package was developed to give you a quick start to communicate with the
Instagram Api. It is used to query the most common endpoints.

This package is only designed to login with a single user account to display instagram data on your website. its not currently designed to be used as a multi-user application.

## Navigation
<!-- TOC -->
  * [Navigation](#navigation)
  * [🛠 Requirements](#-requirements)
  * [Installation](#installation)
  * [Usage](#usage)
    * [Authentication](#authentication)
    * [Getting the connector](#getting-the-connector)
    * [Getting the user](#getting-the-user)
    * [Getting the user media](#getting-the-user-media)
    * [Getting the user media without nested children images](#getting-the-user-media-without-nested-children-images)
  * [DTO Showcase](#dto-showcase)
    * [InstagramUser](#instagramuser)
    * [InstagramImage](#instagramimage)
  * [Testing](#testing)
  * [Changelog](#changelog)
  * [Contributing](#contributing)
  * [Security Vulnerabilities](#security-vulnerabilities)
  * [Credits](#credits)
  * [License](#license)
<!-- TOC -->

## 🛠 Requirements

| Version | PHP Version | Laravel Version |
|---------|-------------|-----------------|
| > v11.0 | ^8.3        | ^11.*           |

## Installation

You can install the package via composer:

```bash
composer require codebar-ag/laravel-instagram
```

Then:

```bash
php artisan instagram:install
```


Or:

You can publish the config file with:

```bash
php artisan vendor:publish --tag="instagram-config"
```

This is the contents of the published config file:

```php
<?php

return [

    /*
     * The client_id from registering your app on Instagram
     */
    'client_id' => env('INSTAGRAM_CLIENT_ID', null),

    /*
     * The client secret from registering your app on Instagram,
     * This is not the same as an access token.
     */
    'client_secret' => env('INSTAGRAM_CLIENT_SECRET', null),
];

```
You should finally add the following to your .env file:

```env
INSTAGRAM_CLIENT_ID=your-client-id
INSTAGRAM_CLIENT_SECRET=your-client-secret
```

## Overriding the default routes

If you want to override the default routes, you can do so by creating a `instagram.php` file in your routes directory and adding the following code:

```php
<?php

use CodebarAg\LaravelInstagram\Http\Controllers\InstagramController;
use Illuminate\Support\Facades\Route;

Route::prefix('instagram')->name('instagram.')->group(function () {
    Route::get('/auth', [InstagramController::class, 'auth'])->name('auth');

    Route::get('/callback', [InstagramController::class, 'callback'])->name('callback');
});
```

Then you should register the routes in your `bootstrap\app.php`:

```php
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        //        api: __DIR__ . '/../routes/api.php',
        then: function () {
            Route::middleware('web')->group(base_path('routes/instagram.php'));
        },
    )
```

or in your `RouteServiceProvider`:

```php
$this->routes(function () {
    Route::middleware('web')->group(base_path('routes/web.php'));
    Route::middleware('web')->group(base_path('routes/instagram.php'));
});
```

You can get your client id and client secret by registering your app on the [Instagram Developer Portal](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login)

When configuring your app on the Instagram Developer Portal, you will need to set the redirect uri to: `http://your-app-url.com/instagram/callback`

You should also set the Deauthorize callback URL to: `http://your-app-url.com/instagram/deauthorize`

You should also set the Deletion callback URL to: `http://your-app-url.com/instagram/delete`

The links above need to be publicly accessible. You can use tools like [Expose](https://expose.dev/) or [ngrok](https://ngrok.com/) to expose your local development environment to the internet.
When using the tools above, ensure you set your `APP_URL` in your `.env` file to the url provided by the tool.

## Usage

### Authentication

To authenticate with the instagram api, you need to redirect the user to the following named route `instagram.login` or use the path `/instagram/login`.

This will redirect the user to the Instagram login page, where they will be asked to authorize your app to access their account.

After the user has authorized your app, they will be redirected back to your app, where you can then use the `instagram` facade to interact with the Instagram API.

### Getting the connector

```php
use CodebarAg\LaravelInstagram\Actions\InstagramHandler;

$connector = InstagramHandler::connector(); // returns an instance of \CodebarAg\LaravelInstagram\Connectors\InstagramConnector
```

### Getting the user

```php
use CodebarAg\LaravelInstagram\Requests\GetInstagramMe;

$response = $connector->send(new GetInstagramMe);

$user = $response->dto(); // returns an instance of \CodebarAg\LaravelInstagram\DTO\InstagramUser
```

### Getting the user media

```php
use CodebarAg\LaravelInstagram\Requests\GetInstagramMedia;

$response = $connector->send(new GetInstagramMedia);

$media = $response->dto(); // returns a collection of \CodebarAg\LaravelInstagram\DTO\InstagramImage
```

### Getting the user media without nested children images

```php
use CodebarAg\LaravelInstagram\Requests\GetInstagramMedia;

$response = $connector->send(new GetInstagramMedia(withChildren: false));

$media = $response->dto(); // returns a collection of \CodebarAg\LaravelInstagram\DTO\InstagramImage
```

## DTO Showcase

### InstagramUser

```php
CodebarAg\LaravelInstagram\Data\InstagramUser {
    id: '987654321'                                             // string
    user_id: '123456789'                                        // string               
    username: 'john_doe'                                        // string
    name: 'John Doe'                                            // string
    account_type: 'BUSINESS'                                    // string
    profile_picture_url: https://instagram-link.com             // string
    followers_count: 200                                        // int
    follows_count: 100                                          // int
    media_count: 1                                              // int
}
```

### InstagramImage

```php
CodebarAg\LaravelInstagram\Data\InstagramImage {
    id: '123456789'                                             // string
    media_type: 'CAROUSEL_ALBUM'|'IMAGE'                        // string
    media_url: 'https://instagram-link.com'                     // string
    permalink: 'https://instagram-link.com'                     // string
    timestamp: '2022-01-01T00:00:00+00:00'                      // CarbonImmutable
    caption: 'This is a caption'                                // null|string
    children: [                                                 // null|Collection
        CodebarAg\LaravelInstagram\Data\InstagramImage {
            id: '123456798'                                     // string
            media_type: 'IMAGE'                                 // string
            media_url: 'https://instagram-link.com'             // string
            permalink: 'https://instagram-link.com'             // string
            timestamp: '2022-01-01T00:00:00+00:00'              // CarbonImmutable
            caption: null                                       // null
            children: null                                      // null
        }
    ]
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rhys Lees](https://github.com/RhysLees)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

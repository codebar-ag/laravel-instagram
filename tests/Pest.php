<?php

use CodebarAg\LaravelInstagram\Tests\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;

Config::preventStrayRequests();

uses(TestCase::class)
    ->beforeEach(fn () => MockClient::destroyGlobal())
    ->in(__DIR__);

<?php

use CodebarAg\LaravelInstagram\Tests\TestCase;
use Illuminate\Support\Sleep;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Laravel\Facades\Saloon;

Config::preventStrayRequests();

uses(TestCase::class)
    ->beforeEach(fn () => MockClient::destroyGlobal())
    ->in(__DIR__);

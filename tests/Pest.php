<?php

use CodebarAg\LaravelInstagram\Tests\TestCase;
use Saloon\Config;

Config::preventStrayRequests();

uses(TestCase::class)
    ->in(__DIR__);

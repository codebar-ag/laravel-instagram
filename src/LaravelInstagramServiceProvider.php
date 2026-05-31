<?php

namespace CodebarAg\LaravelInstagram;

use CodebarAg\LaravelInstagram\Contracts\InstagramHandlerContract;
use CodebarAg\LaravelInstagram\Services\InstagramService;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelInstagramServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        $this->app->singleton(InstagramHandlerContract::class, InstagramService::class);
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-instagram')
            ->hasConfigFile()
            ->hasRoute('instagram')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('codebar-ag/laravel-instagram');
            });
    }
}

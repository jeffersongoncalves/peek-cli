<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeffersonGoncalves\LaravelZero\SelfUpdate\PharUpdater;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(PharUpdater::class, fn () => new PharUpdater(
            githubRepo: 'jeffersongoncalves/peek-cli',
            assetName: 'peek.phar',
            tempPrefix: 'peek_',
            currentVersion: (string) config('app.version', 'unreleased'),
        ));
    }
}

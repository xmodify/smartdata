<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            if (config('app.url')) {
                URL::forceRootUrl(config('app.url'));
            }

            if (request()->header('X-Forwarded-Proto') === 'https' || str_contains(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        Schema::defaultStringLength(191);
    }
}

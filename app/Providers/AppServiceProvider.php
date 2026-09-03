<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // This forces Laravel to use secure HTTPS links for all CSS and JS in the Codespace
        if (env('APP_ENV') !== 'local' && env('APP_ENV') !== 'testing') {
            URL::forceScheme('https');
        }
    }
}
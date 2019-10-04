<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Repositories\QuoteRepository::class, function ($app) {
            return new \App\Repositories\QuoteRepository(120);
        });
    }
}

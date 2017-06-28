<?php

namespace Iamface\LaravelSquare;

use Illuminate\Support\ServiceProvider;

class LaravelSquareServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('laravelSquare.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make('Iamface\LaravelSquare\LaravelSquareController');
    }
}

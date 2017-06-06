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
        //
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

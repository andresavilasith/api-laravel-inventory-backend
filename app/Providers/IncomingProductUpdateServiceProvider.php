<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class IncomingProductUpdateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path() . '/Helpers/IncomingProductUpdate.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

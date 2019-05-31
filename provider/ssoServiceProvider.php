<?php

namespace provider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;

class ssoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('MiniOrange\Classes\Actions\AuthFacadeController');

        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $app = app();
        $version = floatval($app->version());
        if ($version <= 5.3) {
            if (!$this->app->routesAreCached()) {
                require __DIR__ . '/../src/routes.php';
            }
            $this->loadViewsFrom(__DIR__ . '/../src/', 'mosaml');


        } else {
            $this->loadMigrationsFrom(__DIR__ . '/../src/classes/actions');
            $this->loadRoutesFrom(__DIR__ . '/../src/routes.php');
            $this->loadViewsFrom(__DIR__ . '/../src/', 'mosaml');
        }
    }
}

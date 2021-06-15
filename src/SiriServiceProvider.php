<?php

namespace TromsFylkestrafikk\Siri;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use TromsFylkestrafikk\Siri\Console\CreateSubscription;
use TromsFylkestrafikk\Siri\Console\ListSubscriptions;
use TromsFylkestrafikk\Siri\Console\TerminateSubscription;

class SiriServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();
        $this->registerMigrations();
        $this->registerConsoleCommands();
        $this->registerRoutes();
        $this->registerViews();
    }

    protected function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/siri.php' => config_path('siri.php'),
            ], 'config');
        }
    }

    /**
     * Setup migrations
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Setup Artisan console commands.
     */
    protected function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSubscription::class,
                ListSubscriptions::class,
                TerminateSubscription::class,
            ]);
        }
    }

    /**
     * Register routes for siri consumption and development.
     */
    protected function registerRoutes()
    {
        $enable_dev = config('siri.enable_dev_routes');
        Route::group($this->getRoutesConfig('routes_api'), function () use ($enable_dev) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            if ($enable_dev) {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api-devel.php');
            }
        });
        if ($enable_dev) {
            Route::group($this->getRoutesConfig('routes_web'), function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web-devel.php');
            });
        }
    }

    /**
     * Get route group configuration for given target.
     *
     * @param string $target  'route' or 'route_dev'.  See 'config/siri.php'
     * @return array
     */
    protected function getRoutesConfig($target = 'route')
    {
        return [
            'prefix' => config("siri.{$target}.prefix"),
            'middleware' => config("siri.{$target}.middleware"),
        ];
    }

    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'siri');
    }
}

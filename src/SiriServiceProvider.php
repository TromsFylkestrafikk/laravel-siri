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
        $this->publishAssets();
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
            ], ['siri', 'config', 'siri-config']);
        }
    }

    /**
     * Publish/copy assets to app.
     */
    protected function publishAssets()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../dist' => public_path('siri')], ['siri', 'assets', 'siri-assets']);
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
        $enableDev = config('siri.enable_dev_routes');
        Route::group($this->getRoutesConfig('routes_api'), function () use ($enableDev) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            if ($enableDev) {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api-dev.php');
            }
        });
        if ($enableDev) {
            Route::group($this->getRoutesConfig('routes_web'), function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web-dev.php');
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

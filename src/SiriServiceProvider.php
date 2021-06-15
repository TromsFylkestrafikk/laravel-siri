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

    protected function registerRoutes()
    {
        Route::group($this->getRouteConfig('route'), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
        if (config('siri.route_dev.enabled')) {
            Route::group($this->getRouteConfig('route_dev'), function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/devel.php');
            });
        }
    }

    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'siri');
    }

    protected function getRouteConfig($target = 'route')
    {
        return [
            'prefix' => config("siri.{$target}.prefix"),
            'middleware' => config("siri.{$target}.middleware"),
        ];
    }
}

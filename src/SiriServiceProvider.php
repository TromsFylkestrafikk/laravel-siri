<?php

namespace TromsFylkestrafikk\Siri;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Console\CreateSubscription;
use TromsFylkestrafikk\Siri\Console\ListSubscriptions;
use TromsFylkestrafikk\Siri\Console\TerminateSubscription;

class SiriServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();
        $this->setupMigrations();
        $this->setupConsoleCommands();
        $this->setupRoutes();
        $this->setupViews();
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
    protected function setupMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Setup Artisan console commands.
     */
    protected function setupConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSubscription::class,
                ListSubscriptions::class,
                TerminateSubscription::class,
            ]);
        }
    }

    protected function setupRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function setupViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'siri');
    }
}

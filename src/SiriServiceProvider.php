<?php

namespace TromsFylkestrafikk\Siri;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Console\CreateSubscription;

class SiriServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishConfig();
        $this->setupMigrations();
        $this->setupConsoleCommands();
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
            ]);
        }
    }
}

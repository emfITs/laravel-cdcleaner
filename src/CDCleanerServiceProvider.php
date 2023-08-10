<?php

namespace Emfits\CDCleaner;

use Emfits\CDCleaner\Console\Commands\CleanCommand;
use Illuminate\Support\ServiceProvider;

class CDCleanerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('cdcleaner.php'),
            ], 'config');

            $this->commands(
                [
                    CleanCommand::class,
                ]
            );
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'cdcleaner');

        // Register the main class to use with the facade
        $this->app->singleton('cdcleaner', function () {
            return new CDCleaner;
        });
    }
}

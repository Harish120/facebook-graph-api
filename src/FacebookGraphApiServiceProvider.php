<?php

namespace Harryes\FacebookGraphApi;

use Illuminate\Support\ServiceProvider;
use Harryes\FacebookGraphApi\Services\FacebookGraphApiService;
use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;

class FacebookGraphApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/facebook-graph-api.php', 'facebook-graph-api'
        );

        $this->app->singleton(FacebookGraphApiInterface::class, function ($app) {
            return new FacebookGraphApiService(
                config('facebook-graph-api.app_id'),
                config('facebook-graph-api.app_secret'),
                config('facebook-graph-api.default_graph_version', 'v18.0'),
                config('facebook-graph-api.default_access_token'),
                config('facebook-graph-api.timeout', 30)
            );
        });

        $this->app->alias(FacebookGraphApiInterface::class, 'facebook-graph-api');
        
        // Register the facade
        $this->app->singleton('facebook-graph-api', function ($app) {
            return $app->make(FacebookGraphApiInterface::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/facebook-graph-api.php' => config_path('facebook-graph-api.php'),
            ], 'facebook-graph-api-config');
        }
    }
} 
<?php

namespace Harryes\FacebookGraphApi;

use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Services\FacebookGraphApiService;
use Illuminate\Support\ServiceProvider;

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

        // Register Facebook Login Service
        $this->app->singleton('facebook-login', function ($app) {
            return new \Harryes\FacebookGraphApi\Services\FacebookLoginService(
                $app->make(FacebookGraphApiInterface::class)
            );
        });

        // Register Blade Components
        $this->loadViewComponentsAs('facebook-graph-api', [
            \Harryes\FacebookGraphApi\View\Components\FacebookLoginButton::class,
        ]);
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

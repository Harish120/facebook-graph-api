<?php

namespace Harryes\FacebookGraphApi\Tests;

use Harryes\FacebookGraphApi\FacebookGraphApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FacebookGraphApiServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'FacebookGraph' => \Harryes\FacebookGraphApi\Facades\FacebookGraph::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup Facebook Graph API configuration
        $app['config']->set('facebook-graph-api.app_id', 'test_app_id');
        $app['config']->set('facebook-graph-api.app_secret', 'test_app_secret');
        $app['config']->set('facebook-graph-api.default_graph_version', 'v18.0');
        $app['config']->set('facebook-graph-api.timeout', 30);
        $app['config']->set('facebook-graph-api.logging.enabled', false);
        $app['config']->set('facebook-graph-api.cache.enabled', false);

        // Setup Facebook Login configuration
        $app['config']->set('facebook-graph-api.login.default_scopes', 'email,public_profile');
        $app['config']->set('facebook-graph-api.login.button_options', [
            'data-width' => '300',
            'data-size' => 'large',
            'data-button-type' => 'login_with',
            'data-layout' => 'rounded',
            'data-auto-logout-link' => 'false',
            'data-use-continue-as' => 'false',
        ]);
        $app['config']->set('facebook-graph-api.login.user_fields', [
            'id', 'name', 'email', 'picture', 'gender', 'birthday',
            'locale', 'timezone', 'updated_time', 'verified',
        ]);
    }
}

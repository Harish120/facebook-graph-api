<?php

namespace Harryes\FacebookGraphApi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Harryes\FacebookGraphApi\FacebookGraphApiServiceProvider;

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
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Facebook Graph API configuration
        $app['config']->set('facebook-graph-api.app_id', 'test_app_id');
        $app['config']->set('facebook-graph-api.app_secret', 'test_app_secret');
        $app['config']->set('facebook-graph-api.default_graph_version', 'v18.0');
        $app['config']->set('facebook-graph-api.timeout', 30);
        $app['config']->set('facebook-graph-api.logging.enabled', false);
        $app['config']->set('facebook-graph-api.cache.enabled', false);
    }
} 
<?php

namespace Harryes\FacebookGraphApi\Tests\Feature;

use Harryes\FacebookGraphApi\Facades\FacebookGraph;
use Harryes\FacebookGraphApi\Responses\FacebookResponse;
use Harryes\FacebookGraphApi\Tests\TestCase;

class FacebookGraphApiFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure the package for testing
        $this->app['config']->set('facebook-graph-api.app_id', 'test_app_id');
        $this->app['config']->set('facebook-graph-api.app_secret', 'test_app_secret');
        $this->app['config']->set('facebook-graph-api.default_graph_version', 'v18.0');
    }

    public function test_facade_can_be_resolved(): void
    {
        $this->assertInstanceOf(
            \Harryes\FacebookGraphApi\Services\FacebookGraphApiService::class,
            FacebookGraph::getFacadeRoot()
        );
    }

    public function test_service_provider_registers_correctly(): void
    {
        $this->assertTrue($this->app->bound('facebook-graph-api'));
        $this->assertTrue($this->app->bound(\Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface::class));
    }

    public function test_config_can_be_published(): void
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'facebook-graph-api-config',
            '--force' => true,
        ]);

        $this->assertFileExists(config_path('facebook-graph-api.php'));
    }

    public function test_facade_methods_are_available(): void
    {
        // Test that facade can be called (methods are available via __callStatic)
        $this->assertInstanceOf(
            \Harryes\FacebookGraphApi\Services\FacebookGraphApiService::class,
            FacebookGraph::getFacadeRoot()
        );

        // Test that we can call methods on the facade
        FacebookGraph::setAccessToken('test_token');
        $this->assertEquals('test_token', FacebookGraph::getAccessToken());
    }

    public function test_facade_can_set_and_get_access_token(): void
    {
        FacebookGraph::setAccessToken('test_token');
        $this->assertEquals('test_token', FacebookGraph::getAccessToken());
    }

    public function test_facade_can_set_and_get_graph_version(): void
    {
        FacebookGraph::setGraphVersion('v19.0');
        $this->assertEquals('v19.0', FacebookGraph::getGraphVersion());
    }

    public function test_facade_can_get_app_credentials(): void
    {
        $this->assertEquals('test_app_id', FacebookGraph::getAppId());
        $this->assertEquals('test_app_secret', FacebookGraph::getAppSecret());
    }

    public function test_dependency_injection_works(): void
    {
        $service = $this->app->make(\Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface::class);

        $this->assertInstanceOf(
            \Harryes\FacebookGraphApi\Services\FacebookGraphApiService::class,
            $service
        );
    }

    public function test_helper_class_can_be_instantiated(): void
    {
        $service = $this->app->make(\Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface::class);
        $helper = new \Harryes\FacebookGraphApi\Helpers\FacebookGraphHelper($service);

        $this->assertInstanceOf(
            \Harryes\FacebookGraphApi\Helpers\FacebookGraphHelper::class,
            $helper
        );
    }

    public function test_batch_request_builder_can_be_created(): void
    {
        $batch = FacebookGraph::createBatchRequest();

        $this->assertInstanceOf(
            \Harryes\FacebookGraphApi\Services\FacebookBatchRequestBuilder::class,
            $batch
        );
    }

    public function test_response_class_works_correctly(): void
    {
        $data = ['id' => '123', 'name' => 'Test'];
        $response = new FacebookResponse($data, [], 200);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123', $response->get('id'));
        $this->assertEquals('Test', $response->get('name'));
        $this->assertEquals($data, $response->getData());
    }

    public function test_response_class_handles_errors(): void
    {
        $errorData = [
            'error' => [
                'message' => 'Invalid token',
                'type' => 'OAuthException',
                'code' => 190,
            ],
        ];
        $response = new FacebookResponse($errorData, [], 400);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertEquals('Invalid token', $response->getErrorMessage());
        $this->assertEquals(190, $response->getErrorCode());
    }

    public function test_response_class_handles_pagination(): void
    {
        $data = [
            'data' => [['id' => '1'], ['id' => '2']],
            'paging' => [
                'next' => 'https://graph.facebook.com/v18.0/me/posts?after=abc123',
                'previous' => 'https://graph.facebook.com/v18.0/me/posts?before=xyz789',
            ],
        ];
        $response = new FacebookResponse($data, [], 200);

        $this->assertTrue($response->hasNextPage());
        $this->assertTrue($response->hasPreviousPage());
        $this->assertNotNull($response->getNextPageUrl());
        $this->assertNotNull($response->getPreviousPageUrl());
    }

    public function test_exception_classes_exist(): void
    {
        $this->assertTrue(class_exists(\Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException::class));
    }

    public function test_exception_static_methods_work(): void
    {
        $this->expectException(\Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException::class);

        throw \Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException::invalidAccessToken();
    }
}

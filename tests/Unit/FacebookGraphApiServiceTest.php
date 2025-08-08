<?php

namespace Harryes\FacebookGraphApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;
use Harryes\FacebookGraphApi\Responses\FacebookResponse;
use Harryes\FacebookGraphApi\Services\FacebookGraphApiService;
use Harryes\FacebookGraphApi\Tests\TestCase;

class FacebookGraphApiServiceTest extends TestCase
{
    protected FacebookGraphApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FacebookGraphApiService('test_app_id', 'test_app_secret');
    }

    public function test_it_can_be_instantiated_with_valid_parameters(): void
    {
        $service = new FacebookGraphApiService('app_id', 'app_secret', 'v18.0', 'access_token', 30);

        $this->assertEquals('app_id', $service->getAppId());
        $this->assertEquals('app_secret', $service->getAppSecret());
        $this->assertEquals('v18.0', $service->getGraphVersion());
        $this->assertEquals('access_token', $service->getAccessToken());
    }

    public function test_it_can_set_and_get_access_token(): void
    {
        $this->service->setAccessToken('new_token');
        $this->assertEquals('new_token', $this->service->getAccessToken());
    }

    public function test_it_can_set_and_get_graph_version(): void
    {
        $this->service->setGraphVersion('v19.0');
        $this->assertEquals('v19.0', $this->service->getGraphVersion());
    }

    public function test_it_builds_correct_urls(): void
    {
        $this->service->setGraphVersion('v18.0');
        $this->service->setAccessToken('test_token');

        $response = $this->service->get('/me', ['fields' => 'id,name']);

        $this->assertInstanceOf(FacebookResponse::class, $response);
    }

    public function test_it_handles_successful_get_request(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'id' => '123',
            'name' => 'Test User',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->get('/me', ['fields' => 'id,name']);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123', $response->get('id'));
        $this->assertEquals('Test User', $response->get('name'));
    }

    public function test_it_handles_successful_post_request(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'id' => 'post_123',
            'message' => 'Test post',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->post('/me/feed', ['message' => 'Test post']);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('post_123', $response->get('id'));
        $this->assertEquals('Test post', $response->get('message'));
    }

    public function test_it_handles_facebook_error_response(): void
    {
        $mockResponse = new Response(400, [], (string) json_encode([
            'error' => [
                'message' => 'Invalid access token',
                'type' => 'OAuthException',
                'code' => 190,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('invalid_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $service->get('/me');
    }

    public function test_it_handles_rate_limit_error(): void
    {
        $mockResponse = new Response(429, ['Retry-After' => '60'], (string) json_encode([
            'error' => [
                'message' => 'Rate limit exceeded',
                'type' => 'OAuthException',
                'code' => 4,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $service->get('/me');
    }

    public function test_it_handles_permission_denied_error(): void
    {
        $mockResponse = new Response(403, [], (string) json_encode([
            'error' => [
                'message' => 'Permission denied',
                'type' => 'OAuthException',
                'code' => 200,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $service->get('/me');
    }

    public function test_it_handles_resource_not_found_error(): void
    {
        $mockResponse = new Response(404, [], (string) json_encode([
            'error' => [
                'message' => 'Resource not found',
                'type' => 'OAuthException',
                'code' => 100,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $service->get('/invalid_endpoint');
    }

    public function test_it_handles_server_error(): void
    {
        $mockResponse = new Response(500, [], (string) json_encode([
            'error' => [
                'message' => 'Internal server error',
                'type' => 'OAuthException',
                'code' => 1,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $service->get('/me');
    }

    public function test_it_throws_exception_for_file_not_found_in_upload(): void
    {
        $this->service->setAccessToken('test_token');

        $this->expectException(FacebookGraphApiException::class);
        $this->service->upload('/me/photos', '/non/existent/file.jpg');
    }

    public function test_it_can_get_user_profile(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'id' => '123',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->get('/me', ['fields' => 'id,name,email']);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123', $response->get('id'));
        $this->assertEquals('Test User', $response->get('name'));
        $this->assertEquals('test@example.com', $response->get('email'));
    }

    public function test_it_can_get_page_information(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'id' => 'page_123',
            'name' => 'Test Page',
            'fan_count' => 1000,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->get('/page_123', ['fields' => 'id,name,fan_count']);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('page_123', $response->get('id'));
        $this->assertEquals('Test Page', $response->get('name'));
        $this->assertEquals(1000, $response->get('fan_count'));
    }

    public function test_it_can_create_page_post(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'id' => 'post_123',
            'message' => 'Test page post',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');
        $service->setAccessToken('test_token');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->post('/page_123/feed', ['message' => 'Test page post']);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('post_123', $response->get('id'));
        $this->assertEquals('Test page post', $response->get('message'));
    }

    public function test_it_can_get_long_lived_token(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'access_token' => 'long_lived_token_123',
            'token_type' => 'bearer',
            'expires_in' => 5184000,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->get('/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => 'app_id',
            'client_secret' => 'app_secret',
            'fb_exchange_token' => 'short_lived_token',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('long_lived_token_123', $response->get('access_token'));
        $this->assertEquals('bearer', $response->get('token_type'));
        $this->assertEquals(5184000, $response->get('expires_in'));
    }

    public function test_it_can_debug_token(): void
    {
        $mockResponse = new Response(200, [], (string) json_encode([
            'data' => [
                'app_id' => 'app_id',
                'type' => 'USER',
                'application' => 'Test App',
                'data_access_expires_at' => 1234567890,
                'expires_at' => 1234567890,
                'is_valid' => true,
                'scopes' => ['email', 'public_profile'],
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $service = new FacebookGraphApiService('app_id', 'app_secret');

        // Use reflection to set the mock client
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        $response = $service->get('/debug_token', [
            'input_token' => 'test_token',
            'access_token' => 'app_id|app_secret',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('app_id', $response->get('data.app_id'));
        $this->assertEquals('USER', $response->get('data.type'));
        $this->assertTrue($response->get('data.is_valid'));
    }
}

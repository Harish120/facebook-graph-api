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

    protected array $container = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FacebookGraphApiService(
            'test_app_id',
            'test_app_secret',
            'v18.0',
            'test_access_token',
            30
        );
    }

    /** @test */
    public function it_can_be_instantiated_with_valid_parameters()
    {
        $service = new FacebookGraphApiService(
            'app_id',
            'app_secret',
            'v18.0',
            'access_token',
            30
        );

        $this->assertInstanceOf(FacebookGraphApiService::class, $service);
        $this->assertEquals('app_id', $service->getAppId());
        $this->assertEquals('app_secret', $service->getAppSecret());
        $this->assertEquals('v18.0', $service->getGraphVersion());
        $this->assertEquals('access_token', $service->getAccessToken());
    }

    /** @test */
    public function it_can_set_and_get_access_token()
    {
        $this->service->setAccessToken('new_access_token');

        $this->assertEquals('new_access_token', $this->service->getAccessToken());
    }

    /** @test */
    public function it_can_set_and_get_graph_version()
    {
        $this->service->setGraphVersion('v19.0');

        $this->assertEquals('v19.0', $this->service->getGraphVersion());
    }

    /** @test */
    public function it_builds_correct_urls()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->service, '/me', ['fields' => 'id,name']);

        $expected = 'https://graph.facebook.com/v18.0/me?fields=id%2Cname';
        $this->assertEquals($expected, $url);
    }

    /** @test */
    public function it_handles_successful_get_request()
    {
        $mockResponse = new Response(200, [], json_encode([
            'id' => '123456789',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->get('/me', ['fields' => 'id,name,email']);

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123456789', $response->get('id'));
        $this->assertEquals('John Doe', $response->get('name'));
    }

    /** @test */
    public function it_handles_successful_post_request()
    {
        $mockResponse = new Response(200, [], json_encode([
            'id' => 'post_123456789',
            'success' => true,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->post('/me/feed', ['message' => 'Test post']);

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('post_123456789', $response->get('id'));
    }

    /** @test */
    public function it_handles_facebook_error_response()
    {
        $mockResponse = new Response(400, [], json_encode([
            'error' => [
                'message' => 'Invalid access token',
                'type' => 'OAuthException',
                'code' => 190,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('Invalid access token');

        $this->service->get('/me');
    }

    /** @test */
    public function it_handles_rate_limit_error()
    {
        $mockResponse = new Response(429, ['Retry-After' => '60'], json_encode([
            'error' => [
                'message' => 'Rate limit exceeded',
                'type' => 'OAuthException',
                'code' => 4,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->service->get('/me');
    }

    /** @test */
    public function it_handles_permission_denied_error()
    {
        $mockResponse = new Response(403, [], json_encode([
            'error' => [
                'message' => 'Permission denied',
                'type' => 'OAuthException',
                'code' => 200,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('Permission denied');

        $this->service->get('/me');
    }

    /** @test */
    public function it_handles_resource_not_found_error()
    {
        $mockResponse = new Response(404, [], json_encode([
            'error' => [
                'message' => 'Resource not found',
                'type' => 'OAuthException',
                'code' => 100,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('Resource not found');

        $this->service->get('/invalid-endpoint');
    }

    /** @test */
    public function it_handles_server_error()
    {
        $mockResponse = new Response(500, [], json_encode([
            'error' => [
                'message' => 'Internal server error',
                'type' => 'OAuthException',
                'code' => 1,
            ],
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('Internal server error');

        $this->service->get('/me');
    }

    /** @test */
    public function it_throws_exception_for_file_not_found_in_upload()
    {
        $this->expectException(FacebookGraphApiException::class);
        $this->expectExceptionMessage('File not found: /path/to/nonexistent/file.jpg');

        $this->service->upload('/me/photos', '/path/to/nonexistent/file.jpg');
    }

    /** @test */
    public function it_can_get_user_profile()
    {
        $mockResponse = new Response(200, [], json_encode([
            'id' => '123456789',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->getUserProfile();

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123456789', $response->get('id'));
    }

    /** @test */
    public function it_can_get_page_information()
    {
        $mockResponse = new Response(200, [], json_encode([
            'id' => 'page_123456789',
            'name' => 'Test Page',
            'fan_count' => 1000,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->getPage('page_123456789');

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('page_123456789', $response->get('id'));
        $this->assertEquals('Test Page', $response->get('name'));
    }

    /** @test */
    public function it_can_create_page_post()
    {
        $mockResponse = new Response(200, [], json_encode([
            'id' => 'post_123456789',
            'success' => true,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->createPagePost('page_123456789', ['message' => 'Test post']);

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('post_123456789', $response->get('id'));
    }

    /** @test */
    public function it_can_get_long_lived_token()
    {
        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'long_lived_token_123456789',
            'token_type' => 'bearer',
            'expires_in' => 5184000,
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->getLongLivedToken('short_lived_token');

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('long_lived_token_123456789', $response->get('access_token'));
    }

    /** @test */
    public function it_can_debug_token()
    {
        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                'app_id' => 'test_app_id',
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

        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);

        $response = $this->service->debugToken('test_token');

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('test_app_id', $response->get('data.app_id'));
        $this->assertTrue($response->get('data.is_valid'));
    }
}

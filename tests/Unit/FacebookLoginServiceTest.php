<?php

namespace Harryes\FacebookGraphApi\Tests\Unit;

use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Responses\FacebookResponse;
use Harryes\FacebookGraphApi\Services\FacebookLoginService;
use Harryes\FacebookGraphApi\Tests\TestCase;
use Mockery;

class FacebookLoginServiceTest extends TestCase
{
    private FacebookLoginService $loginService;

    private \Mockery\MockInterface $mockFacebookApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockFacebookApi = Mockery::mock(FacebookGraphApiInterface::class);
        /** @phpstan-ignore-next-line */
        $this->loginService = new FacebookLoginService($this->mockFacebookApi);
    }

    public function test_get_login_config_returns_correct_configuration(): void
    {
        $config = $this->loginService->getLoginConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('app_id', $config);
        $this->assertArrayHasKey('version', $config);
        $this->assertArrayHasKey('cookie', $config);
        $this->assertArrayHasKey('xfbml', $config);
        $this->assertArrayHasKey('status', $config);
        $this->assertTrue($config['cookie']);
        $this->assertTrue($config['xfbml']);
        $this->assertTrue($config['status']);
    }

    public function test_render_login_button_returns_html_string(): void
    {
        $buttonHtml = $this->loginService->renderLoginButton();

        $this->assertIsString($buttonHtml);
        $this->assertStringContainsString('<div class="fb-login-button"', $buttonHtml);
        $this->assertStringContainsString('data-scope="email,public_profile"', $buttonHtml);
    }

    public function test_render_login_button_with_custom_options(): void
    {
        $customOptions = [
            'scope' => 'email,public_profile,pages_manage_posts',
            'data-width' => '400',
            'data-size' => 'medium',
        ];

        $buttonHtml = $this->loginService->renderLoginButton($customOptions);

        $this->assertStringContainsString('data-scope="email,public_profile,pages_manage_posts"', $buttonHtml);
        $this->assertStringContainsString('data-width="400"', $buttonHtml);
        $this->assertStringContainsString('data-size="medium"', $buttonHtml);
    }

    public function test_render_sdk_script_returns_script_string(): void
    {
        $scriptHtml = $this->loginService->renderSdkScript();

        $this->assertIsString($scriptHtml);
        $this->assertStringContainsString('<div id="fb-root"></div>', $scriptHtml);
        $this->assertStringContainsString('<script', $scriptHtml);
        $this->assertStringContainsString('FB.init', $scriptHtml);
    }

    public function test_render_helper_scripts_returns_script_string(): void
    {
        $scriptHtml = $this->loginService->renderHelperScripts();

        $this->assertIsString($scriptHtml);
        $this->assertStringContainsString('<script', $scriptHtml);
        $this->assertStringContainsString('function checkLoginState', $scriptHtml);
        $this->assertStringContainsString('function statusChangeCallback', $scriptHtml);
        $this->assertStringContainsString('function testAPI', $scriptHtml);
    }

    public function test_render_complete_blade_implementation_includes_all_parts(): void
    {
        $completeImplementation = $this->loginService->renderCompleteBladeImplementation();

        $this->assertStringContainsString('<div id="fb-root"></div>', $completeImplementation);
        $this->assertStringContainsString('<div class="fb-login-button"', $completeImplementation);
        $this->assertStringContainsString('function checkLoginState', $completeImplementation);
    }

    public function test_get_vue_login_button_props_returns_array(): void
    {
        $props = $this->loginService->getVueLoginButtonProps();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('scope', $props);
        $this->assertArrayHasKey('onlogin', $props);
        $this->assertEquals('email,public_profile', $props['scope']);
    }

    public function test_get_react_login_button_props_returns_array(): void
    {
        $props = $this->loginService->getReactLoginButtonProps();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('scope', $props);
        $this->assertArrayHasKey('onLogin', $props);
        $this->assertEquals('email,public_profile', $props['scope']);
    }

    public function test_validate_access_token_returns_true_for_valid_token(): void
    {
        // Create a real FacebookResponse with the data we need
        $responseData = [
            'data' => [
                'is_valid' => true,
                'app_id' => 'test_app_id',
                'user_id' => '12345',
            ],
        ];
        $mockResponse = new FacebookResponse($responseData);

        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('valid_token')
            ->andReturn($mockResponse);

        $result = $this->loginService->validateAccessToken('valid_token');

        $this->assertTrue($result);
    }

    public function test_validate_access_token_returns_false_for_invalid_token(): void
    {
        // Create a real FacebookResponse with invalid token data
        $responseData = [
            'data' => [
                'is_valid' => false,
                'app_id' => 'test_app_id',
                'user_id' => null,
            ],
        ];
        $mockResponse = new FacebookResponse($responseData);

        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('invalid_token')
            ->andReturn($mockResponse);

        $result = $this->loginService->validateAccessToken('invalid_token');

        $this->assertFalse($result);
    }

    public function test_validate_access_token_returns_false_on_exception(): void
    {
        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('error_token')
            ->andThrow(new \Exception('API Error'));

        $result = $this->loginService->validateAccessToken('error_token');

        $this->assertFalse($result);
    }

    public function test_get_user_profile_returns_user_data(): void
    {
        // Create a real FacebookResponse with user data
        $responseData = [
            'id' => '123',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
        $mockResponse = new FacebookResponse($responseData);

        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andReturn($mockResponse);

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEquals('123', $userProfile['id']);
        $this->assertEquals('John Doe', $userProfile['name']);
    }

    public function test_get_user_profile_returns_empty_array_on_failure(): void
    {
        // Create a real FacebookResponse with error data
        $responseData = [
            'error' => [
                'message' => 'Invalid access token',
                'code' => 190,
            ],
        ];
        $mockResponse = new FacebookResponse($responseData, [], 400);

        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andReturn($mockResponse);

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEmpty($userProfile);
    }

    public function test_get_user_profile_returns_empty_array_on_exception(): void
    {
        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andThrow(new \Exception('API Error'));

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEmpty($userProfile);
    }

    public function test_get_default_scopes_returns_config_value(): void
    {
        $scopes = $this->loginService->getDefaultScopes();

        $this->assertIsString($scopes);
        $this->assertEquals('email,public_profile', $scopes);
    }

    public function test_get_default_button_options_returns_config_value(): void
    {
        $options = $this->loginService->getDefaultButtonOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('data-width', $options);
        $this->assertArrayHasKey('data-size', $options);
        $this->assertEquals('300', $options['data-width']);
        $this->assertEquals('large', $options['data-size']);
    }

    public function test_get_default_user_fields_returns_config_value(): void
    {
        $fields = $this->loginService->getDefaultUserFields();

        $this->assertIsArray($fields);
        $this->assertContains('id', $fields);
        $this->assertContains('name', $fields);
        $this->assertContains('email', $fields);
        $this->assertContains('picture', $fields);
    }

    public function test_render_login_button_uses_config_defaults(): void
    {
        $buttonHtml = $this->loginService->renderLoginButton();

        $this->assertStringContainsString('data-width="300"', $buttonHtml);
        $this->assertStringContainsString('data-size="large"', $buttonHtml);
        $this->assertStringContainsString('data-button-type="login_with"', $buttonHtml);
    }

    public function test_render_login_button_merges_custom_options_with_config(): void
    {
        $customOptions = [
            'scope' => 'email,public_profile,user_birthday',
            'data-width' => '400',
        ];

        $buttonHtml = $this->loginService->renderLoginButton($customOptions);

        // Custom options should override config defaults
        $this->assertStringContainsString('data-width="400"', $buttonHtml);
        $this->assertStringContainsString('data-scope="email,public_profile,user_birthday"', $buttonHtml);

        // Config defaults should still be present for non-overridden options
        $this->assertStringContainsString('data-size="large"', $buttonHtml);
    }

    public function test_get_user_profile_uses_config_user_fields(): void
    {
        // Create a real FacebookResponse with user data
        $responseData = [
            'id' => '123',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/picture.jpg',
        ];
        $mockResponse = new FacebookResponse($responseData);

        /** @phpstan-ignore-next-line */
        $this->mockFacebookApi->shouldReceive('get')
            ->once()
            ->with('/me', ['fields' => 'id,name,email,picture,gender,birthday,locale,timezone,updated_time,verified'], 'test_token')
            ->andReturn($mockResponse);

        $result = $this->loginService->getUserProfile('test_token');

        $this->assertIsArray($result);
        $this->assertEquals($responseData, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

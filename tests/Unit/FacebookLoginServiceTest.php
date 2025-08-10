<?php

namespace Harryes\FacebookGraphApi\Tests\Unit;

use Harryes\FacebookGraphApi\Services\FacebookLoginService;
use Harryes\FacebookGraphApi\Tests\TestCase;
use Mockery;

class FacebookLoginServiceTest extends TestCase
{
    private FacebookLoginService $loginService;

    private $mockFacebookApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockFacebookApi = Mockery::mock('Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface');
        $this->loginService = new FacebookLoginService($this->mockFacebookApi);
    }

    public function test_get_login_config_returns_correct_configuration()
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

    public function test_render_login_button_returns_html_string()
    {
        $buttonHtml = $this->loginService->renderLoginButton();

        $this->assertIsString($buttonHtml);
        $this->assertStringContainsString('<div class="fb-login-button"', $buttonHtml);
        $this->assertStringContainsString('data-scope="email,public_profile"', $buttonHtml);
    }

    public function test_render_login_button_with_custom_options()
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

    public function test_render_sdk_script_returns_script_string()
    {
        $scriptHtml = $this->loginService->renderSdkScript();

        $this->assertIsString($scriptHtml);
        $this->assertStringContainsString('<div id="fb-root"></div>', $scriptHtml);
        $this->assertStringContainsString('<script', $scriptHtml);
        $this->assertStringContainsString('FB.init', $scriptHtml);
    }

    public function test_render_helper_scripts_returns_script_string()
    {
        $scriptHtml = $this->loginService->renderHelperScripts();

        $this->assertIsString($scriptHtml);
        $this->assertStringContainsString('<script', $scriptHtml);
        $this->assertStringContainsString('function checkLoginState', $scriptHtml);
        $this->assertStringContainsString('function statusChangeCallback', $scriptHtml);
        $this->assertStringContainsString('function testAPI', $scriptHtml);
    }

    public function test_render_complete_blade_implementation_includes_all_parts()
    {
        $completeImplementation = $this->loginService->renderCompleteBladeImplementation();

        $this->assertStringContainsString('<div id="fb-root"></div>', $completeImplementation);
        $this->assertStringContainsString('<div class="fb-login-button"', $completeImplementation);
        $this->assertStringContainsString('function checkLoginState', $completeImplementation);
    }

    public function test_get_vue_login_button_props_returns_array()
    {
        $props = $this->loginService->getVueLoginButtonProps();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('scope', $props);
        $this->assertArrayHasKey('onlogin', $props);
        $this->assertEquals('email,public_profile', $props['scope']);
    }

    public function test_get_react_login_button_props_returns_array()
    {
        $props = $this->loginService->getReactLoginButtonProps();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('scope', $props);
        $this->assertArrayHasKey('onLogin', $props);
        $this->assertEquals('email,public_profile', $props['scope']);
    }

    public function test_validate_access_token_returns_true_for_valid_token()
    {
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('get')
            ->with('data.is_valid')
            ->andReturn(true);

        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('valid_token')
            ->andReturn($mockResponse);

        $result = $this->loginService->validateAccessToken('valid_token');

        $this->assertTrue($result);
    }

    public function test_validate_access_token_returns_false_for_invalid_token()
    {
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('get')
            ->with('data.is_valid')
            ->andReturn(false);

        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('invalid_token')
            ->andReturn($mockResponse);

        $result = $this->loginService->validateAccessToken('invalid_token');

        $this->assertFalse($result);
    }

    public function test_validate_access_token_returns_false_on_exception()
    {
        $this->mockFacebookApi->shouldReceive('debugToken')
            ->with('error_token')
            ->andThrow(new \Exception('API Error'));

        $result = $this->loginService->validateAccessToken('error_token');

        $this->assertFalse($result);
    }

    public function test_get_user_profile_returns_user_data()
    {
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('isSuccessful')
            ->andReturn(true);
        $mockResponse->shouldReceive('getData')
            ->andReturn(['id' => '123', 'name' => 'John Doe']);

        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andReturn($mockResponse);

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEquals('123', $userProfile['id']);
        $this->assertEquals('John Doe', $userProfile['name']);
    }

    public function test_get_user_profile_returns_empty_array_on_failure()
    {
        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('isSuccessful')
            ->andReturn(false);

        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andReturn($mockResponse);

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEmpty($userProfile);
    }

    public function test_get_user_profile_returns_empty_array_on_exception()
    {
        $this->mockFacebookApi->shouldReceive('get')
            ->with('/me', Mockery::type('array'), 'user_token')
            ->andThrow(new \Exception('API Error'));

        $userProfile = $this->loginService->getUserProfile('user_token');

        $this->assertIsArray($userProfile);
        $this->assertEmpty($userProfile);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

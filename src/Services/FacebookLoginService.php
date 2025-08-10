<?php

namespace Harryes\FacebookGraphApi\Services;

use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Illuminate\Support\Facades\Config;

class FacebookLoginService
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    /**
     * Get Facebook Login configuration for JavaScript SDK
     */
    public function getLoginConfig(): array
    {
        return [
            'app_id' => Config::get('facebook-graph-api.app_id'),
            'version' => Config::get('facebook-graph-api.default_graph_version', 'v18.0'),
            'cookie' => true,
            'xfbml' => true,
            'status' => true,
        ];
    }

    /**
     * Generate Facebook Login button HTML for Blade templates
     */
    public function renderLoginButton(array $options = []): string
    {
        $defaultOptions = [
            'scope' => 'email,public_profile',
            'onlogin' => 'checkLoginState',
            'data-width' => '300',
            'data-size' => 'large',
            'data-button-type' => 'login_with',
            'data-layout' => 'rounded',
            'data-auto-logout-link' => 'false',
            'data-use-continue-as' => 'false',
        ];

        $options = array_merge($defaultOptions, $options);

        $attributes = '';
        foreach ($options as $key => $value) {
            if (str_starts_with($key, 'data-')) {
                $attributes .= " {$key}=\"{$value}\"";
            } else {
                $attributes .= " data-{$key}=\"{$value}\"";
            }
        }

        return "<div class=\"fb-login-button\"{$attributes}></div>";
    }

    /**
     * Generate Facebook Login button for Vue.js
     */
    public function getVueLoginButtonProps(array $options = []): array
    {
        $defaultOptions = [
            'scope' => 'email,public_profile',
            'onlogin' => 'checkLoginState',
            'data-width' => '300',
            'data-size' => 'large',
            'data-button-type' => 'login_with',
            'data-layout' => 'rounded',
            'data-auto-logout-link' => 'false',
            'data-use-continue-as' => 'false',
        ];

        return array_merge($defaultOptions, $options);
    }

    /**
     * Generate Facebook Login button for React
     */
    public function getReactLoginButtonProps(array $options = []): array
    {
        $defaultOptions = [
            'scope' => 'email,public_profile',
            'onLogin' => 'checkLoginState',
            'data-width' => '300',
            'data-size' => 'large',
            'data-button-type' => 'login_with',
            'data-layout' => 'rounded',
            'data-auto-logout-link' => 'false',
            'data-use-continue-as' => 'false',
        ];

        return array_merge($defaultOptions, $options);
    }

    /**
     * Generate complete Facebook SDK initialization script
     */
    public function renderSdkScript(): string
    {
        $config = $this->getLoginConfig();

        return "
        <div id=\"fb-root\"></div>
        <script async defer crossorigin=\"anonymous\" 
                src=\"https://connect.facebook.net/en_US/sdk.js\">
        </script>
        <script>
        window.fbAsyncInit = function() {
            FB.init({
                appId: '{$config['app_id']}',
                cookie: {$config['cookie']},
                xfbml: {$config['xfbml']},
                version: '{$config['version']}'
            });
            
            FB.AppEvents.logPageView();
        };

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = \"https://connect.facebook.net/en_US/sdk.js\";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>";
    }

    /**
     * Generate JavaScript helper functions for Facebook Login
     */
    public function renderHelperScripts(): string
    {
        return "
        <script>
        function checkLoginState() {
            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        }

        function statusChangeCallback(response) {
            if (response.status === 'connected') {
                // Logged into your webpage and Facebook.
                console.log('Welcome! Fetching your information....');
                testAPI();
            } else {
                // The person is not logged into your webpage or we are unable to tell.
                console.log('Please log into this webpage.');
            }
        }

        function testAPI() {
            FB.api('/me', function(response) {
                console.log('Good to see you, ' + response.name + '.');
                
                // Send the access token to your server
                FB.getLoginStatus(function(response) {
                    if (response.status === 'connected') {
                        sendTokenToServer(response.authResponse.accessToken);
                    }
                });
            });
        }

        function sendTokenToServer(accessToken) {
            // Send to your Laravel backend
            fetch('/facebook/auth/callback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                },
                body: JSON.stringify({
                    access_token: accessToken
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Token sent to server:', data);
                // Handle response from server
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function logout() {
            FB.logout(function(response) {
                console.log('User logged out');
                // Handle logout
            });
        }
        </script>";
    }

    /**
     * Generate complete Facebook Login implementation for Blade
     */
    public function renderCompleteBladeImplementation(): string
    {
        return $this->renderSdkScript().
               $this->renderHelperScripts().
               $this->renderLoginButton();
    }

    /**
     * Generate Facebook Login implementation for Vue.js
     */
    public function renderVueImplementation(): string
    {
        return $this->renderSdkScript().$this->renderHelperScripts();
    }

    /**
     * Generate Facebook Login implementation for React
     */
    public function renderReactImplementation(): string
    {
        return $this->renderSdkScript().$this->renderHelperScripts();
    }

    /**
     * Validate access token on the server side
     */
    public function validateAccessToken(string $accessToken): bool
    {
        try {
            $response = $this->facebookApi->debugToken($accessToken);

            return $response->get('data.is_valid', false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user profile from access token
     */
    public function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->facebookApi->get('/me', [
                'fields' => 'id,name,email,picture,gender,birthday,locale,timezone,updated_time,verified',
            ], $accessToken);

            if ($response->isSuccessful()) {
                return $response->getData();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

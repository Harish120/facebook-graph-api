<?php

namespace Harryes\FacebookGraphApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getLoginConfig()
 * @method static string renderLoginButton(array $options = [])
 * @method static array getVueLoginButtonProps(array $options = [])
 * @method static array getReactLoginButtonProps(array $options = [])
 * @method static string renderSdkScript()
 * @method static string renderHelperScripts()
 * @method static string renderCompleteBladeImplementation()
 * @method static string renderVueImplementation()
 * @method static string renderReactImplementation()
 * @method static bool validateAccessToken(string $accessToken)
 * @method static array getUserProfile(string $accessToken)
 *
 * @see \Harryes\FacebookGraphApi\Services\FacebookLoginService
 */
class FacebookLogin extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'facebook-login';
    }
}

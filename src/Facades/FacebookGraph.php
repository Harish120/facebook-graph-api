<?php

namespace Harryes\FacebookGraphApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse request(string $method, string $endpoint, array $params = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse get(string $endpoint, array $params = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse post(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse put(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse delete(string $endpoint, ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null)
 * @method static self setAccessToken(string $accessToken)
 * @method static ?string getAccessToken()
 * @method static self setGraphVersion(string $version)
 * @method static string getGraphVersion()
 * @method static string getAppId()
 * @method static string getAppSecret()
 *
 * @see \Harryes\FacebookGraphApi\Services\FacebookGraphApiService
 */
class FacebookGraph extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'facebook-graph-api';
    }
} 
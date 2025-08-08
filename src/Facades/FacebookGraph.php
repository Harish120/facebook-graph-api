<?php

namespace LaravelFacebookGraphApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse get(string $endpoint, array $params = [], ?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse post(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse put(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse delete(string $endpoint, ?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null)
 * @method static self setAccessToken(string $accessToken)
 * @method static ?string getAccessToken()
 * @method static self setGraphVersion(string $version)
 * @method static string getGraphVersion()
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getUserProfile(?string $accessToken = null, array $fields = ['id', 'name', 'email'])
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getUserPosts(?string $accessToken = null, array $params = [])
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getPage(string $pageId, ?string $accessToken = null, array $fields = ['id', 'name', 'fan_count'])
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getPagePosts(string $pageId, ?string $accessToken = null, array $params = [])
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse createPagePost(string $pageId, array $data, ?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getPageInsights(string $pageId, array $metrics, ?string $accessToken = null, array $params = [])
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getUserAccounts(?string $accessToken = null)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse getLongLivedToken(string $shortLivedToken)
 * @method static \LaravelFacebookGraphApi\Responses\FacebookResponse debugToken(string $accessToken)
 *
 * @see \LaravelFacebookGraphApi\Services\FacebookGraphApiService
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
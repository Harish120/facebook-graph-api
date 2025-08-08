<?php

namespace Harryes\FacebookGraphApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse get(string $endpoint, array $params = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse post(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse put(string $endpoint, array $data = [], ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse delete(string $endpoint, ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null)
 * @method static self setAccessToken(string $accessToken)
 * @method static ?string getAccessToken()
 * @method static self setGraphVersion(string $version)
 * @method static string getGraphVersion()
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getUserProfile(?string $accessToken = null, array $fields = ['id', 'name', 'email'])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getUserPosts(?string $accessToken = null, array $params = [])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPage(string $pageId, ?string $accessToken = null, array $fields = ['id', 'name', 'fan_count'])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPagePosts(string $pageId, ?string $accessToken = null, array $params = [])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse createPagePost(string $pageId, array $data, ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPageInsights(string $pageId, array $metrics, ?string $accessToken = null, array $params = [])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getUserAccounts(?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getLongLivedToken(string $shortLivedToken)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse debugToken(string $accessToken)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPostReactions(string $postId, ?string $accessToken = null, array $params = [])
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPostReactionSummary(string $postId, ?string $accessToken = null)
 * @method static \Harryes\FacebookGraphApi\Responses\FacebookResponse getPostReactionsByType(string $postId, string $reactionType, ?string $accessToken = null, array $params = [])
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
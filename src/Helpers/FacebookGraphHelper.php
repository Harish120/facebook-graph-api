<?php

namespace Harryes\FacebookGraphApi\Helpers;

use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;

class FacebookGraphHelper
{
    protected FacebookGraphApiInterface $facebookApi;

    public function __construct(FacebookGraphApiInterface $facebookApi)
    {
        $this->facebookApi = $facebookApi;
    }

    /**
     * Get user's basic profile information
     */
    public function getUserBasicInfo(?string $accessToken = null): array
    {
        $response = $this->facebookApi->get('/me', [
            'fields' => 'id,name,email,picture',
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get user profile: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get user's recent posts with pagination
     */
    public function getUserRecentPosts(int $limit = 10, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->get('/me/posts', [
            'limit' => $limit,
            'fields' => 'id,message,created_time,permalink_url',
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get user posts: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get page's basic information
     */
    public function getPageBasicInfo(string $pageId, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->get("/{$pageId}", [
            'fields' => 'id,name,fan_count,category,picture,description',
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get page info: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Create a simple text post on a page
     */
    public function createSimplePagePost(string $pageId, string $message, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->post("/{$pageId}/feed", [
            'message' => $message,
            'published' => true,
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to create page post: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get page insights for common metrics
     */
    public function getPageCommonInsights(string $pageId, ?string $accessToken = null): array
    {
        $metrics = [
            'page_impressions',
            'page_engaged_users',
            'page_post_engagements',
            'page_fan_adds',
            'page_fan_removes',
        ];

        $response = $this->facebookApi->get("/{$pageId}/insights", [
            'metric' => implode(',', $metrics),
            'period' => 'day',
            'limit' => 30,
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get page insights: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get all user's pages with basic info
     */
    public function getUserPages(?string $accessToken = null): array
    {
        $response = $this->facebookApi->get('/me/accounts', [
            'fields' => 'id,name,category,access_token',
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get user accounts: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Validate and get token information
     */
    public function validateToken(string $accessToken): array
    {
        $response = $this->facebookApi->get('/debug_token', [
            'input_token' => $accessToken,
            'access_token' => $this->facebookApi->getAppId().'|'.$this->facebookApi->getAppSecret(),
        ]);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to validate token: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Convert short-lived token to long-lived token
     */
    public function exchangeToken(string $shortLivedToken): array
    {
        $response = $this->facebookApi->get('/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->facebookApi->getAppId(),
            'client_secret' => $this->facebookApi->getAppSecret(),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to exchange token: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Upload a photo to a page
     */
    public function uploadPagePhoto(string $pageId, string $filePath, string $message = '', ?string $accessToken = null): array
    {
        $data = ['message' => $message];
        $response = $this->facebookApi->upload("/{$pageId}/photos", $filePath, $data, $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to upload photo: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get page posts with engagement metrics
     */
    public function getPagePostsWithEngagement(string $pageId, int $limit = 25, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->get("/{$pageId}/posts", [
            'limit' => $limit,
            'fields' => 'id,message,created_time,type,permalink_url,shares,comments.limit(0).summary(true),likes.limit(0).summary(true)',
        ], $accessToken);

        if (! $response->isSuccessful()) {
            throw new \Exception('Failed to get page posts: '.$response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Check if token has required permissions
     */
    public function checkTokenPermissions(string $accessToken, array $requiredPermissions): bool
    {
        $tokenInfo = $this->validateToken($accessToken);
        $scopes = $tokenInfo['data']['scopes'] ?? [];

        foreach ($requiredPermissions as $permission) {
            if (! in_array($permission, $scopes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get token expiration information
     */
    public function getTokenExpirationInfo(string $accessToken): array
    {
        $tokenInfo = $this->validateToken($accessToken);
        $data = $tokenInfo['data'] ?? [];

        return [
            'is_valid' => $data['is_valid'] ?? false,
            'expires_at' => $data['expires_at'] ?? null,
            'scopes' => $data['scopes'] ?? [],
            'type' => $data['type'] ?? 'unknown',
        ];
    }
}

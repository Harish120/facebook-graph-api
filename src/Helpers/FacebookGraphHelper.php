<?php

namespace LaravelFacebookGraphApi\Helpers;

use LaravelFacebookGraphApi\Contracts\FacebookGraphApiInterface;
use LaravelFacebookGraphApi\Responses\FacebookResponse;

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
        $response = $this->facebookApi->getUserProfile($accessToken, ['id', 'name', 'email', 'picture']);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get user profile: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get user's recent posts with pagination
     */
    public function getUserRecentPosts(int $limit = 10, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->getUserPosts($accessToken, ['limit' => $limit]);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get user posts: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get page's basic information
     */
    public function getPageBasicInfo(string $pageId, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->getPage($pageId, $accessToken, [
            'id', 'name', 'fan_count', 'category', 'picture', 'description'
        ]);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get page info: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Create a simple text post on a page
     */
    public function createSimplePagePost(string $pageId, string $message, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->createPagePost($pageId, ['message' => $message], $accessToken);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to create page post: ' . $response->getErrorMessage());
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
            'page_fan_removes'
        ];

        $response = $this->facebookApi->getPageInsights($pageId, $metrics, $accessToken, [
            'period' => 'day',
            'limit' => 30
        ]);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get page insights: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get all user's pages with basic info
     */
    public function getUserPages(?string $accessToken = null): array
    {
        $response = $this->facebookApi->getUserAccounts($accessToken);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get user accounts: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Validate and get token information
     */
    public function validateToken(string $accessToken): array
    {
        $response = $this->facebookApi->debugToken($accessToken);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to validate token: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Convert short-lived token to long-lived token
     */
    public function exchangeToken(string $shortLivedToken): array
    {
        $response = $this->facebookApi->getLongLivedToken($shortLivedToken);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to exchange token: ' . $response->getErrorMessage());
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
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to upload photo: ' . $response->getErrorMessage());
        }

        return $response->getData();
    }

    /**
     * Get page posts with engagement metrics
     */
    public function getPagePostsWithEngagement(string $pageId, int $limit = 25, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->getPagePosts($pageId, $accessToken, [
            'limit' => $limit,
            'fields' => 'id,message,created_time,type,permalink_url,shares,comments.limit(0).summary(true),likes.limit(0).summary(true)'
        ]);
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Failed to get page posts: ' . $response->getErrorMessage());
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
            if (!in_array($permission, $scopes)) {
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
        $data = $tokenInfo['data'];
        
        return [
            'expires_at' => $data['expires_at'] ?? null,
            'is_expired' => $data['is_expired'] ?? false,
            'is_valid' => $data['is_valid'] ?? false,
            'scopes' => $data['scopes'] ?? [],
        ];
    }
} 